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

	 VALUES ('ADD_UPDATE_ENGINE_PERMISSION',
			'Adds separate permission for update_engine.',
			'AGENCY_CORE',
			'',
			'db_mod.18',
			current_timestamp,
			'', 
			sys_user(),
			sys_user()
		  );

INSERT INTO tbl_l_permission_type VALUES ('UPDATE_ENGINE', 'Update the Engine Array',sys_user(),current_timestamp,sys_user(),
current_timestamp);

COMMIT; 
