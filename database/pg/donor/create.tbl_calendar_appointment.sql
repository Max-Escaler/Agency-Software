CREATE TABLE tbl_calendar_appointment (
	calendar_appointment_id			SERIAL PRIMARY KEY,
	calendar_id				INTEGER NOT NULL REFERENCES tbl_calendar ( calendar_id ),
	description				VARCHAR(90),
	donor_id				INTEGER REFERENCES tbl_donor ( donor_id ),
	event_start				TIMESTAMP(0) NOT NULL CHECK (event_start::text ~ '(00|15|30|45):00$'),
	event_end				TIMESTAMP(0) NOT NULL CHECK ( (event_end::text ~ '(00|15|30|45|(23:59)):00$') --for the last time-slot of the day
										AND event_end > event_start),
	calendar_appointment_resolution_code	VARCHAR(10) REFERENCES tbl_l_calendar_appointment_resolution ( calendar_appointment_resolution_code ),
	location_code			REFERENCES tbl_l_location (location_code),
	location_text			TEXT,
	comments				TEXT,
	allow_overlap			BOOLEAN NOT NULL DEFAULT FALSE,
--system fields
	added_by				INTEGER NOT NULL REFERENCES tbl_staff (staff_id),
	added_at				TIMESTAMP(0)     NOT NULL DEFAULT CURRENT_TIMESTAMP,
	changed_by				INTEGER NOT NULL  REFERENCES tbl_staff (staff_id),
	changed_at				TIMESTAMP(0)     NOT NULL DEFAULT CURRENT_TIMESTAMP,
	is_deleted				BOOLEAN NOT NULL DEFAULT FALSE,
	deleted_at				TIMESTAMP(0),
	deleted_by				INTEGER REFERENCES tbl_staff(staff_id),
	deleted_comment			TEXT,
	sys_log				TEXT

	CONSTRAINT calendar_appointment_some_good_data CHECK (donor_id IS NOT NULL OR description IS NOT NULL)
);

CREATE INDEX index_tbl_calendar_appointment_calendar_id ON tbl_calendar_appointment ( calendar_id );
CREATE INDEX index_tbl_calendar_appointment_donor_id ON tbl_calendar_appointment ( donor_id );
CREATE INDEX index_tbl_calendar_appointment_event_start ON tbl_calendar_appointment ( event_start );
CREATE INDEX index_tbl_calendar_appointment_location_code ON tbl_calendar_appointment ( location_code );
CREATE INDEX index_tbl_calendar_appointment_calendar_appointment_resolution_code ON tbl_calendar_appointment ( calendar_appointment_resolution_code );
CREATE INDEX index_tbl_calendar_appointment_donor_id_event_start ON tbl_calendar_appointment ( donor_id,event_start );
CREATE INDEX index_tbl_calendar_appointment_event_start_donor_id ON tbl_calendar_appointment ( event_start,donor_id );
