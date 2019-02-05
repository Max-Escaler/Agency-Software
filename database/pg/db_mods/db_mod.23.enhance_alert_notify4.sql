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

	 VALUES ('ENHANCE_ALERT_NOTIFY4',
			'Fix the facility field in alert notify function.',
			'AGENCY_CORE',
			'',
			'db_mod.23',
			current_timestamp,
			'', 
			sys_user(),
			sys_user()
		  );

\i ../agency_core/functions/create.alert_notify.sql

COMMIT; 
