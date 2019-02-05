BEGIN;

/*
 * db_mod.TEMPLATE
 */
 
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

	 VALUES ('CREATE_INFO_ADDITIONAL', /*UNIQUE_DB_MOD_NAME */
			'Creates info_additional table', /* DESCRIPTION */
			'AGENCY_CORE', /* Which flavor of AGENCY.  AGENCY_CORE applies to all installations */
			'', /* git SHA ID, if applicable */
			'info_additional', /* git tag, if applicable */
			current_timestamp, /* Applied at */
			'', /* comment */
			sys_user(),
			sys_user()
		  );


\i ../agency_core/create.l_value_type.sql
\i ../agency_core/create.l_info_additional_type.sql
\i ../agency_core/create.tbl_info_additional.sql

COMMIT;
