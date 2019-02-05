BEGIN;

/*
 * db_mod.TEMPLATE
 */
 
/*
NOTE:  I believe it is not possible to know the
git SHA ID before actually making a commit.

It is possible to know a git tag, and include
that in the commit.
*/

INSERT INTO tbl_db_revision_history 
	(
	db_revision_code,
	db_revision_description,
	agency_flavor_code,
	git_sha,
	git_tag,
	applied_at,
	comment,
	added_by,
	changed_by
	)

	 VALUES (
		'REQUIRE_CLIENT_LANGUAGE', /*UNIQUE_DB_MOD_NAME */
		'Requires language code.',
		'CLIENT', /* Which flavor of AGENCY.  AGENCY_CORE applies to all installations */
		'', /* git SHA ID, if applicable */
		'db_mod.56', /* git tag, if applicable */
		current_timestamp, /* Applied at */
		'Updates old values, defaults to unknown. A different default can be set in client config file', /* comment */
		sys_user(),
		sys_user()
	 );

UPDATE tbl_client 
SET language_code=0,
changed_by=2,
changed_at=current_timestamp,
sys_log=COALESCE(sys_log||E'\n','') || 'Updating blank values to unknown. db_mod.56'
WHERE language_code IS NULL;

ALTER TABLE tbl_client ALTER COLUMN language_code SET DEFAULT 0;
ALTER TABLE tbl_client ALTER COLUMN language_code SET NOT NULL;

COMMIT;

