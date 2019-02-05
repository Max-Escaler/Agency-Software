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

	 VALUES ('ADD_EXPORT_PERMISSIONS',
			'Adds Permission types for (already-existing) generic_oo_export and sql_dump perms.',
			'AGENCY_CORE',
			'',
			'db_mod.25',
			current_timestamp,
			'', 
			sys_user(),
			sys_user()
		  );

INSERT INTO tbl_l_permission_type VALUES ('GENERIC_OO_EXPORT', 'Output Generic OpenOffice files',sys_user(),current_timestamp,sys_user(),current_timestamp);
INSERT INTO tbl_l_permission_type VALUES ('SQL_DUMP', 'Output Data in Raw Formats',sys_user(),current_timestamp,sys_user(),current_timestamp);

COMMIT; 
