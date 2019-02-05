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

	 VALUES ('LOOSEN_DB_LIST_UNIQUE_CONSTRAINT', /*UNIQUE_DB_MOD_NAME */
			'Make constraint on db_list,description', /* DESCRIPTION */
			'AGENCY_CORE', /* Which flavor of AGENCY.  AGENCY_CORE applies to all installations */
			'', /* git SHA ID, if applicable */
			'db_mod.30', /* git tag, if applicable */
			current_timestamp, /* Applied at */
			'', /* comment */
			sys_user(),
			sys_user()
		  );

ALTER TABLE db_list DROP CONSTRAINT db_list_description_key;
ALTER TABLE db_list ADD CONSTRAINT db_list_db_name_description_unique UNIQUE (db_name,description);

COMMIT;
