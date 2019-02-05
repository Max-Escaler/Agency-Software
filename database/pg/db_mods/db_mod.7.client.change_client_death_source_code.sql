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

	 VALUES ('CHANGE_CLIENT_DEATH_SOURCE_CODE',
			'Change client_death_source code from DESC to STAFF',
			'CLIENT',
			'',
			'db_mod.7',
			current_timestamp,
			'', 
			sys_user(),
			sys_user()
		  );

INSERT INTO tbl_l_client_death_data_source VALUES ('STAFF','Agency Staff',sys_user(),current_timestamp,sys_user(),current_timestamp);

UPDATE tbl_client_death SET client_death_data_source_code = 'STAFF',changed_by=sys_user(),changed_at=current_timestamp,sys_log=COALESCE(sys_log || E'\n','') || 'Relabeling staff death source (db_mod7)' WHERE client_death_data_source_code = 'DESC';

UPDATE tbl_l_client_death_data_source SET is_deleted=true,deleted_by=sys_user(),deleted_at=current_timestamp,deleted_comment='Retired for more generic Staff option' WHERE client_death_data_source_code = 'DESC';


COMMIT;
