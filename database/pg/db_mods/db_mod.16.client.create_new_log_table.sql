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

	 VALUES ('CREATE_NEW_LOG_TABLE',
			'Creates new log table.  This will backup existing logs, but not create them in the new table.',
			'AGENCY_CORE',
			'',
			'db_mod.16',
			current_timestamp,
			'', 
			sys_user(),
			sys_user()
		  );

/* Backup existing logs, but you will need to restore them yourself */
CREATE TABLE log_back_dbmod_16 AS SELECT * FROM log;

drop view elevated_concern_note;
drop view log;
drop table tbl_log;
\i ../agency_core/create.l_log_type.sql 
\i ../agency_core/create.tbl_log.sql 
\i ../client/create.view.elevated_concern_note.sql 

INSERT INTO tbl_l_log_type (log_type_code,description,added_by,changed_by) VALUES ('A','Log A',sys_user(),sys_user());
INSERT INTO tbl_l_log_type (log_type_code,description,added_by,changed_by) VALUES ('B','Log B',sys_user(),sys_user());
INSERT INTO tbl_l_log_type (log_type_code,description,added_by,changed_by) VALUES ('C','Log C',sys_user(),sys_user());

COMMIT;

