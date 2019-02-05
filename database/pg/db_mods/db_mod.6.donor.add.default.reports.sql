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

	 VALUES ('ADD_DEFAULT_REPORTS',
			'Add provided/default reports to database',
			'DONOR',
			'',
			'db_mod.6',
			current_timestamp,
			'', 
			sys_user(),
			sys_user()
		  );

	\i ../agency_core/add.report.agency_core.sql
	\i ../donor/add.report.donor.sql

COMMIT;
