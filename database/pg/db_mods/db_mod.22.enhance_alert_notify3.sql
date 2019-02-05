BEGIN;

INSERT INTO tbl_db_revision_history 
	(db_revision_code,
	db_revision_description,
	agency_flavor_code,
	git_sha,
	git_tag,
	applied_at,
	comment,
	added_by,
	changed_by)

	 VALUES ('ENHANCE_ALERT_NOTIFY3',
			'Fix the facility field in alert notify.',
			'AGENCY_CORE',
			'',
			'db_mod.22',
			current_timestamp,
			'', 
			sys_user(),
			sys_user()
		  );

DROP VIEW alert_notify_current;
DROP VIEW alert_notify;

ALTER TABLE tbl_alert_notify DROP CONSTRAINT tbl_alert_notify_facility_code_fkey;
ALTER TABLE tbl_alert_notify RENAME facility_code TO agency_facility_code;
ALTER TABLE tbl_alert_notify_log RENAME facility_code TO agency_facility_code;
ALTER TABLE tbl_alert_notify ADD FOREIGN KEY (agency_facility_code) REFERENCES tbl_l_agency_facility (agency_facility_code);

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

\i ../agency_core/functions/create.alert_notify.sql

COMMIT; 
