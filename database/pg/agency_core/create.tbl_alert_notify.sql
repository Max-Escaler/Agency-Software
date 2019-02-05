CREATE TABLE tbl_alert_notify (
	alert_notify_id			SERIAL PRIMARY KEY,
	alert_object			NAME NOT NULL,
	alert_notify_action_code	VARCHAR(10) NOT NULL REFERENCES tbl_l_alert_notify_action ( alert_notify_action_code ),
	alert_notify_date			DATE NOT NULL DEFAULT CURRENT_DATE,	
	alert_notify_date_end		DATE,

	/* Whom to alert */
	staff_id				INTEGER REFERENCES tbl_staff ( staff_id ),
	agency_program_code		VARCHAR(10) REFERENCES tbl_l_agency_program (agency_program_code),
	agency_project_code		VARCHAR(10) REFERENCES tbl_l_agency_project (agency_project_code),
	staff_position_code		VARCHAR(10) REFERENCES tbl_l_staff_position (staff_position_code),
	agency_facility_code		VARCHAR(10) REFERENCES tbl_l_agency_facility (agency_facility_code),	
	staff_shift_code		VARCHAR(10) REFERENCES tbl_l_staff_shift (staff_shift_code),	
	/* Match staff info w/ trigger record */
	match_program_field		NAME,
	match_project_field		NAME,
	match_position_field	NAME,
	match_facility_field	NAME,
	match_shift_field		NAME,
	match_supervisor_field	NAME,
	match_supervisees_field	NAME,
	match_assignments_field	NAME,

	/* Advanced conditions to trigger alert */
	alert_notify_field		NAME,
	alert_notify_value		TEXT,
	alert_notify_field2		NAME,
	alert_notify_value2		TEXT,
	alert_notify_field3		NAME,
	alert_notify_value3		TEXT,
	alert_notify_field4		NAME,
	alert_notify_value4		TEXT,

	/* comment/explanation */
	alert_notify_reason		TEXT,
	comment					TEXT,
	--system fields
	added_by			INTEGER NOT NULL REFERENCES tbl_staff (staff_id),
	added_at			TIMESTAMP(0)     NOT NULL DEFAULT CURRENT_TIMESTAMP,
	changed_by			INTEGER NOT NULL  REFERENCES tbl_staff (staff_id),
	changed_at			TIMESTAMP(0)     NOT NULL DEFAULT CURRENT_TIMESTAMP,
	is_deleted			BOOLEAN NOT NULL DEFAULT FALSE,
	deleted_at			TIMESTAMP(0),
	deleted_by			INTEGER REFERENCES tbl_staff(staff_id),
	deleted_comment		TEXT,
	sys_log			TEXT
);

CREATE VIEW alert_notify AS SELECT *,
        CASE WHEN coalesce(staff_id::text,match_program_field,match_project_field,match_position_field,match_facility_field,match_shift_field,match_assignments_field,match_supervisor_field,match_supervisees_field) IS NULL THEN 'BLANKET'
        ELSE TRIM(
            CASE WHEN match_assignments_field IS NOT NULL THEN 'ASSIGN ' ELSE '' END
            || CASE WHEN match_supervisor_field IS NOT NULL THEN 'BOSS ' ELSE '' END
            || CASE WHEN match_supervisees_field IS NOT NULL THEN 'STAFF ' ELSE '' END
            || CASE WHEN COALESCE(match_program_field,agency_program_code) IS NOT NULL THEN 'PROG ' ELSE '' END
            || CASE WHEN COALESCE(match_project_field,agency_project_code) IS NOT NULL THEN 'PROJ ' ELSE '' END
            || CASE WHEN COALESCE(staff_position_code,match_position_field) IS NOT NULL THEN 'POS ' ELSE '' END
            || CASE WHEN COALESCE(agency_facility_code,match_facility_field) IS NOT NULL THEN 'FACIL ' ELSE '' END
            || CASE WHEN COALESCE(staff_shift_code,match_shift_field) IS NOT NULL THEN 'SHIFT ' ELSE '' END
            || CASE WHEN staff_id IS NOT NULL THEN 'ID' ELSE '' END)
        END AS alert_notify_basis
 	FROM tbl_alert_notify
    WHERE NOT is_deleted;


CREATE VIEW alert_notify_current AS SELECT * FROM alert_notify 
WHERE alert_notify_date <= CURRENT_DATE AND (alert_notify_date_end IS NULL OR alert_notify_date_end >= CURRENT_DATE);

CREATE INDEX index_tbl_alert_notify_dates_and_such ON tbl_alert_notify (alert_notify_date,alert_notify_date_end,alert_notify_action_code);
CREATE INDEX index_tbl_alert_notify_action_code ON tbl_alert_notify (alert_notify_action_code);
CREATE INDEX index_tbl_alert_notify_staff_id ON tbl_alert_notify (staff_id);

CREATE TRIGGER alert_notify_insert_update /* verify valid object [and column] names */
	/* FIXME: should this be after? */
	BEFORE INSERT OR UPDATE ON tbl_alert_notify FOR EACH ROW EXECUTE PROCEDURE verify_alert_notify();

