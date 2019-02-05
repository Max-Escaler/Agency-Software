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

	 VALUES ('FIX_ALERT_NOTIFY_2_3_4_FIELDS',
			'Make the alert notify 2/3/4 fields actually work.',
			'AGENCY_CORE',
			'',
			'db_mod.40',
			current_timestamp,
			'', 
			sys_user(),
			sys_user()
		  );

\i ../agency_core/functions/create.alert_notify.sql

COMMIT; 
