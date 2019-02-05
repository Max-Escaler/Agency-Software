--putting trigger and function here until this goes live

CREATE OR REPLACE FUNCTION calendar_appointment_date_sanity() RETURNS TRIGGER AS $$
DECLARE
	ext INTEGER;
	o_ext TIMESTAMP;
BEGIN
	-- overlapping events must be calculated separately
	IF NEW.allow_overlap THEN
		SELECT INTO o_ext event_end FROM calendar_appointment_current ca
			WHERE ca.calendar_id=NEW.calendar_id AND ca.calendar_appointment_id <> COALESCE(NEW.calendar_appointment_id,-1)
				AND ca.event_start=NEW.event_start LIMIT 1;
		IF o_ext <> NEW.event_end THEN
			RAISE EXCEPTION 'Overlapping event has different end time than existing event (event_end=%)', o_ext;
		END IF;

		-- now we check for overlapping events
		SELECT INTO ext calendar_appointment_id FROM calendar_appointment_current ca --this will ignore deleted and cancelled records
			WHERE ca.calendar_id=NEW.calendar_id AND ca.calendar_appointment_id <> COALESCE(NEW.calendar_appointment_id,-1)
				AND ca.event_start<>NEW.event_start AND ca.event_end <> NEW.event_end
				AND (  (ca.event_start <= NEW.event_start AND ca.event_end > NEW.event_start)
					OR
					 (ca.event_start < NEW.event_end AND ca.event_end > NEW.event_end)
					OR
					 (ca.event_start >= NEW.event_start AND ca.event_end <= NEW.event_end)
					) LIMIT 1; --it only takes 1
		IF ext IS NOT NULL THEN
			RAISE EXCEPTION 'Record overlaps existing event (calendar_appointment_id=%)', ext;
		END IF;

	ELSE
		--determine if new record overlaps existing
		SELECT INTO ext calendar_appointment_id FROM calendar_appointment_current ca --this will ignore deleted and cancelled records
			WHERE ca.calendar_id=NEW.calendar_id AND ca.calendar_appointment_id <> COALESCE(NEW.calendar_appointment_id,-1)
				AND (  (ca.event_start <= NEW.event_start AND ca.event_end > NEW.event_start)
					OR
					 (ca.event_start < NEW.event_end AND ca.event_end > NEW.event_end)
					OR
					 (ca.event_start >= NEW.event_start AND ca.event_end <= NEW.event_end)
					) LIMIT 1; --it only takes 1
		IF ext IS NOT NULL THEN
			RAISE EXCEPTION 'Record overlaps existing event (calendar_appointment_id=%)', ext;
		END IF;
	END IF;
	RETURN NEW;
END;$$ LANGUAGE plpgsql;

CREATE TRIGGER tbl_calendar_appointment_insert_update
    BEFORE INSERT OR UPDATE ON tbl_calendar_appointment FOR EACH ROW
    EXECUTE PROCEDURE calendar_appointment_date_sanity();

CREATE OR REPLACE FUNCTION make_repeating_calendar_events ( integer, timestamp, time, date, interval,varchar,integer,integer,boolean) RETURNS VOID AS $$
DECLARE
	cal_id ALIAS FOR $1;
	start ALIAS FOR $2;
	length ALIAS FOR $3;
	enddate ALIAS FOR $4;
	recurrence ALIAS FOR $5;
	descript	ALIAS FOR $6;
	donor	ALIAS FOR $7;
	staff		ALIAS FOR $8;
	overlap	ALIAS FOR $9;
	
	cur TIMESTAMP;
	aoverlap BOOLEAN;
BEGIN
	cur := start;
	aoverlap := COALESCE(overlap,false);
	WHILE (cur < enddate) LOOP
		IF (SELECT calendar_appointment_id FROM calendar_appointment_current WHERE
			calendar_id=cal_id AND ( event_start < cur+length AND event_end > cur)
			LIMIT 1) IS NULL THEN
			INSERT INTO tbl_calendar_appointment (
				calendar_id,
				event_start,
				event_end,
				description,
				donor_id,
				added_by,	
				changed_by,
				allow_overlap,
				sys_log
			) VALUES (
				cal_id,
				cur,
				cur+length,
				descript,
				donor,
				staff,
				staff,
				aoverlap,
				'Event auto-created using make_repeating_calendar_events()'
			);
			RAISE NOTICE 'Created event starting at % of length %',cur,length;
		ELSE
			RAISE NOTICE 'Not inserting record - existing event already in place';
		END IF;
		cur := cur + recurrence;
	END LOOP;
	RETURN;
END;$$ LANGUAGE plpgsql;

CREATE OR REPLACE FUNCTION force_calendar_pto ( cal_id integer, pto_date date, varchar, int4) RETURNS BOOLEAN AS $$
DECLARE
	/*
	 * A function to forcibly schedule PTO for the given date. The function
	 * will cancel any non-donor appointments for that date, then schedule
	 * a block, getting the start and end dates from the calendar record.
	 */

	e_st         timestamp;
	e_end        timestamp;
	day_of_week  int;
	descript     varchar;
	staff        int4;
	sys_l        text;
	c_app        record;
	can_sched    boolean;
	error_notice text;
BEGIN
	/*
	 * date must be >= current date
	 */
	IF (pto_date < current_date) THEN
		RAISE EXCEPTION 'Cannot schedule PTO in the past (%)',pto_date;
		RETURN FALSE;
	END IF;

	descript := COALESCE($3,'PTO');
	staff := COALESCE($4,sys_user());

	/*
	 * get start and end times for day of week from calendar record
	 */
	day_of_week := to_char(pto_date,'D')::integer - 1; -- 1=Sunday, but in table, 0=Sunday

	SELECT INTO e_st 
	pto_date||' '||
		(CASE day_of_week
			WHEN 0 THEN day_0_start
			WHEN 1 THEN day_1_start
			WHEN 2 THEN day_2_start
			WHEN 3 THEN day_3_start
			WHEN 4 THEN day_4_start
			WHEN 5 THEN day_5_start
			WHEN 6 THEN day_6_start
		END)
	FROM calendar WHERE calendar_id = cal_id;

	SELECT INTO e_end
	pto_date||' '||
		(CASE day_of_week
			WHEN 0 THEN day_0_end
			WHEN 1 THEN day_1_end
			WHEN 2 THEN day_2_end
			WHEN 3 THEN day_3_end
			WHEN 4 THEN day_4_end
			WHEN 5 THEN day_5_end
			WHEN 6 THEN day_6_end
		END)
	FROM calendar WHERE calendar_id = cal_id;

	/*
	 * if start time = end time, whole day is blocked
	 */
	IF e_end = e_st THEN
		e_st  := pto_date||' 00:00:00';
		e_end := pto_date||' 23:59:00';
	END IF;

	/*
	 * Check for donor appointments
	 */
	can_sched := TRUE;

	FOR c_app IN SELECT * FROM calendar_appointment_current WHERE calendar_id = cal_id AND event_start::date = pto_date LOOP
		IF c_app.donor_id IS NULL THEN
		ELSE
			error_notice := COALESCE(error_notice||E'\n',E'Cannot cancel donor appointments for these donors:\n')||c_app.donor_id;
			can_sched := FALSE;
		END IF;
	END LOOP;

	IF can_sched THEN
		/*
		 * Cancel the appointments
		 */
		FOR c_app IN SELECT * FROM calendar_appointment_current WHERE calendar_id = cal_id AND event_start::date = pto_date LOOP
			IF c_app.donor_id IS NULL THEN
				RAISE NOTICE 'Cancelling calendar_appointment_id %',c_app.calendar_appointment_id;
				UPDATE tbl_calendar_appointment SET calendar_appointment_resolution_code='CANCELLED',changed_by=staff
					WHERE calendar_appointment_id = c_app.calendar_appointment_id;
				sys_l := COALESCE(sys_l||' ','')||'cancelled calendar_appointment '||c_app.calendar_appointment_id;
			ELSE
				RAISE EXCEPTION 'Fatal Error: cannot cancel donor appointments';
			END IF;
		END LOOP;

		/*
		 * Schedule new event
		 */
		RAISE NOTICE 'Scheduling PTO for %',pto_date;
		INSERT INTO tbl_calendar_appointment (calendar_id,event_start,event_end,description,added_by,changed_by,sys_log)
			VALUES (cal_id,e_st,e_end,descript,staff,staff,sys_l);
	ELSE
		RAISE NOTICE '%',error_notice;
	END IF;

	RETURN can_sched;

END;$$ LANGUAGE plpgsql;
