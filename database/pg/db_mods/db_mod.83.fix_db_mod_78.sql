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
		'FIX_DB_MOD_78', /*UNIQUE_DB_MOD_NAME */
		'Fix db_mod 78.  (for client export id, attachment FK points to wrong table.  db_mod.83',
		'AGENCY_CORE', /* Which flavor of AGENCY.  AGENCY_CORE applies to all installations */
		'', /* git SHA ID, if applicable */
		'db_mod.83', /* git tag, if applicable */
		current_timestamp, /* Applied at */
		'', /* comment */
		sys_user(),
		sys_user()
	 );

ALTER TABLE tbl_client_export_id DROP CONSTRAINT "tbl_client_export_id_attachment_id_fkey";
ALTER TABLE tbl_client_export_id ADD CONSTRAINT "tbl_client_export_id_attachment_id_fkey" FOREIGN KEY (attachment_id) REFERENCES tbl_attachment_link (attachment_link_id);

COMMIT;

