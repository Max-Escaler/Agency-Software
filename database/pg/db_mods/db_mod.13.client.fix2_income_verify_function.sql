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

	 VALUES ('FIX2_INCOME_VERIFY', /*UNIQUE_DB_MOD_NAME */
			'Fixes income_verify() function', /* DESCRIPTION */
			'CLIENT', /* Which flavor of AGENCY.  AGENCY_CORE applies to all installations */
			'', /* git SHA ID, if applicable */
			'db_mod.13', /* git tag, if applicable */
			current_timestamp, /* Applied at */
			'', /* comment */
			sys_user(),
			sys_user()
		  );

DROP TRIGGER IF EXISTS verify_income_record ON tbl_income;
\i ../client/functions/create.functions_income.sql

COMMIT;
