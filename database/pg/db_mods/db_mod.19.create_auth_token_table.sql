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

	 VALUES ('CREATE_AUTH_TOKEN_TABLE',
			'Create auth_token table for password resets.',
			'AGENCY_CORE',
			'',
			'db_mod.19',
			current_timestamp,
			'', 
			sys_user(),
			sys_user()
		  );

\i ../agency_core/create.tbl_auth_token.sql

COMMIT; 
