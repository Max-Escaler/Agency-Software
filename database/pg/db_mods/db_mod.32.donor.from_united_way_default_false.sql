
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

	 VALUES ('FROM_UNITED_WAY_DEFAULT_FALSE', /*UNIQUE_DB_MOD_NAME */
			'from_united_way in tbl_donor, set default false', /* DESCRIPTION */
			'DONOR', /* Which flavor of AGENCY.  AGENCY_CORE applies to all installations */
			'', /* git SHA ID, if applicable */
			'db_mod.32', /* git tag, if applicable */
			current_timestamp, /* Applied at */
			'', /* comment */
			sys_user(),
			sys_user()
		  );

ALTER TABLE tbl_donor ALTER from_united_way SET DEFAULT false;

COMMIT;
