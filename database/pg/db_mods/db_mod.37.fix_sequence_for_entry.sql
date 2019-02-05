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

	 VALUES ('FIX_SEQUENCE_FOR_ENTRY', /*UNIQUE_DB_MOD_NAME */
			'Fixes sequence for entry, which otherwise breaks adding new data.', /* DESCRIPTION */
			'CLIENT', /* Which flavor of AGENCY.  AGENCY_CORE applies to all installations */
			'', /* git SHA ID, if applicable */
			'db_mod.37', /* git tag, if applicable */
			current_timestamp, /* Applied at */
			'', /* comment */
			sys_user(),
			sys_user()
		  );

SELECT setval('tbl_entry_entry_id_seq',(SELECT max(entry_id) FROM tbl_entry));

COMMIT;

