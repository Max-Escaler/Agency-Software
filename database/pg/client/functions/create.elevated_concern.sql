CREATE OR REPLACE FUNCTION elevated_concern_post_log() RETURNS TRIGGER AS $$
	/*
	 * Post a log for inserts and exits
	 */

DECLARE
	log_sub TEXT;
	log_txt TEXT;
	nlogid  INTEGER;
	staff INTEGER[];
	st INTEGER;
	ends INTEGER;
BEGIN
	IF TG_OP='INSERT' THEN
		log_sub := client_name(NEW.client_id)||' has been added to the Elevated Concern List';
		log_txt := log_sub||E'\n\nMore information about the ECL can be found on the Wiki or in log 194519.';
	ELSEIF TG_OP='UPDATE' AND (OLD.elevated_concern_date_end IS NULL AND NEW.elevated_concern_date_end IS NOT NULL) THEN
		log_sub := client_name(NEW.client_id)||' has been removed from the Elevated Concern List';
		log_txt := log_sub||E'\n\nMore information about the ECL can be found on the Wiki or in log 194519.';
	ELSEIF TG_OP='UPDATE' THEN
		RETURN NEW;
	ELSE
		RETURN OLD;
	END IF;

	--insert log
	INSERT INTO tbl_log (
		subject,
		log_text,
		md5_sum,
		was_assault_staff,
		was_assault_client,
		was_police,
		was_medics,
		was_bar,
		was_drugs,
		was_threat_staff,
		in_a,
		in_b,
		in_c,
		added_by,
		added_at,
		changed_by,
		sys_log
	) VALUES (
		log_sub,
		log_txt,
		MD5(log_txt||sys_user()||log_sub||CURRENT_TIMESTAMP(0)||CURRENT_TIMESTAMP(0)),
		FALSE,-- was_assault_staff,
		FALSE,
		FALSE,
		FALSE,
		FALSE,
		FALSE,
		FALSE,-- was_threat_staff,
		TRUE, -- in_a,
		TRUE,
		TRUE,
		sys_user(), -- added_by,
		CURRENT_TIMESTAMP(0), -- added_at,
		sys_user(), -- changed_by,
		'Elevated Concern List auto-creating log'
	) RETURNING log_id INTO nlogid;

	--insert client_ref
	INSERT INTO tbl_client_ref (
		client_id,
		ref_table,
		ref_id,
		added_by,
		changed_by,
		sys_log
	) VALUES (
		NEW.client_id,
		'LOG',
		nlogid,
		sys_user(),
		sys_user(),
		'Elevated Concern List auto-creating log'
	);	

	--send out alerts
	staff := staff_alert_client_assign(NEW.client_id);
	st := 1;
	ends := array_count(staff);
	IF (ends > 0) THEN
		FOR i IN st..ends LOOP
			IF (staff[i] <> NEW.changed_by) THEN
				--insert alert
				INSERT INTO tbl_alert (
					staff_id,
					ref_table,
					ref_id,
					alert_subject,
					added_by,
					changed_by
				) VALUES (
					staff[i],
					'LOG',
					nlogid,
					log_sub,
					sys_user(),
					sys_user());
			END IF;
	  	END LOOP;
	END IF;

	RETURN NEW;

END;$$ LANGUAGE plpgsql;
