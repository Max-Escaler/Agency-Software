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

	 VALUES ('SAFE_HARBORS_DATA_ENTRY', /*UNIQUE_DB_MOD_NAME */
			'Create table for holding some SH data entry.  Also creates l_yes_no_1year', /* DESCRIPTION */
			'CLIENT', /* Which flavor of AGENCY.  AGENCY_CORE applies to all installations */
			'', /* git SHA ID, if applicable */
			'', /* git tag, if applicable */
			current_timestamp, /* Applied at */
			'', /* comment */
			sys_user(),
			sys_user()
		  );

\i ../client/create.l_yes_no_1year.sql
\i ../client/create.tbl_safe_harbors_data_entry.sql

COMMIT;
