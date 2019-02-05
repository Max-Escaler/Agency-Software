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

	 VALUES ('RESTRUCTURE_INFO_ADDITIONAL_TYPE', /*UNIQUE_DB_MOD_NAME */
			'Adds numeric primary key to tbl_l_info_additional_type.', /* DESCRIPTION */
			'AGENCY_CORE', /* Which flavor of AGENCY.  AGENCY_CORE applies to all installations */
			'', /* git SHA ID, if applicable */
			'db_mod.34', /* git tag, if applicable */
			current_timestamp, /* Applied at */
			'', /* comment */
			sys_user(),
			sys_user()
		  );



DROP view info_additional;
DROP TABLE tbl_info_additional;
DROP VIEW l_info_additional_type;
DROP TABLE tbl_l_info_additional_type;

\i ../agency_core/create.tbl_info_additional_type.sql
\i ../agency_core/create.tbl_info_additional.sql

COMMIT;

