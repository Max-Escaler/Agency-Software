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

	 VALUES ('MISSING_INFO_ADDITIONAL_TABLES', /*UNIQUE_DB_MOD_NAME */
			'l_info_additional_type and l_value_type were not created previously.', /* DESCRIPTION */
			'AGENCY_CORE', /* Which flavor of AGENCY.  AGENCY_CORE applies to all installations */
			'', /* git SHA ID, if applicable */
			'db_mod.33', /* git tag, if applicable */
			current_timestamp, /* Applied at */
			'', /* comment */
			sys_user(),
			sys_user()
		  );



/* NOTE:  IF you're db_mod breaks on the following lines: */
\i ../agency_core/create.l_value_type.sql
\i ../agency_core/create.l_info_additional_type.sql
/* It's because the tables already exists.  In that case, comment out
 * the lines above, and uncomment the following instead.
 */

/*
ALTER TABLE tbl_l_info_additional_type ADD FOREIGN KEY (value_type_code) REFERENCES tbl_l_value_type (value_type_code);
*/

COMMIT;
