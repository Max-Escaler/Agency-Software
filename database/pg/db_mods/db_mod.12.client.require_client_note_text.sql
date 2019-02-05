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

	 VALUES ('REQUIRE_CLIENT_NOTE_TEXT', /*UNIQUE_DB_MOD_NAME */
			'Disallow client notes with no text', /* DESCRIPTION */
			'CLIENT', /* Which flavor of AGENCY.  AGENCY_CORE applies to all installations */
			'', /* git SHA ID, if applicable */
			'db_mod.12', /* git tag, if applicable */
			current_timestamp, /* Applied at */
			'', /* comment */
			sys_user(),
			sys_user()
		  );

UPDATE tbl_client_note 
	SET	note='(none)',
		changed_by=sys_user(),
		changed_at=current_timestamp,
		sys_log=COALESCE(sys_log||E'\n','') || 'Changed null text to add NOT NULL constraint.  See db_mod.12'
	WHERE note IS NULL;

ALTER TABLE tbl_client_note ALTER note SET NOT NULL;

COMMIT;
