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

	 VALUES ('DB_TABLE_LOG_FUNCTIONS', /*UNIQUE_DB_MOD_NAME */
			'Table log & Primary key functions', /* DESCRIPTION */
			'AGENCY_CORE', /* Which flavor of AGENCY.  AGENCY_CORE applies to all installations */
			'', /* git SHA ID, if applicable */
			'', /* git tag, if applicable */
			current_timestamp, /* Applied at */
			'', /* comment */
			sys_user(),
			sys_user()
		  );

\i ../agency_core/create.view.table_log_enabled_tables.sql
\i ../agency_core/functions/create.functions_agency_core.sql

/*
COMMIT;
*/
