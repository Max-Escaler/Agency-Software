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

	 VALUES ('ADD_UNKNOWN_ACCURACY_OPTION', /*UNIQUE_DB_MOD_NAME */
			'Add unknown to approximate & exact options', /* DESCRIPTION */
			'AGENCY_CORE', /* Which flavor of AGENCY.  AGENCY_CORE applies to all installations */
			'', /* git SHA ID, if applicable */
			'db_mod.31', /* git tag, if applicable */
			current_timestamp, /* Applied at */
			'', /* comment */
			sys_user(),
			sys_user()
		  );

INSERT INTO tbl_l_accuracy
	SELECT 'U','Unknown',sys_user(),current_timestamp,sys_user(),current_timestamp
	WHERE 'U' NOT IN 
		(SELECT accuracy_code FROM tbl_l_accuracy WHERE accuracy_code='U');
COMMIT;
