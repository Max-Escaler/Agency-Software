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

	 VALUES ('CHANGE_REPORT_DEFAULTS',
			'Make SQL required, and provide defaults for allow output & Security override options',
			'AGENCY_CORE',
			'',
			'db_mod.5',
			current_timestamp,
			'', 
			sys_user(),
			sys_user()
		  );

ALTER TABLE tbl_report ALTER allow_output_spreadsheet SET DEFAULT true;
ALTER TABLE tbl_report ALTER allow_output_screen SET DEFAULT true;
ALTER TABLE tbl_report ALTER override_sql_security SET DEFAULT false;
ALTER TABLE tbl_report ALTER sql SET NOT NULL;

COMMIT;
