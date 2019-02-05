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
		'ADD_ATTACHMENT_TO_EXPORT_ID',
		'Adds an attachment field to client_export_id',
		'CLIENT', /* Which flavor of AGENCY.  AGENCY_CORE applies to all installations */
		'', /* git SHA ID, if applicable */
		'db_mod.78', /* git tag, if applicable */
		current_timestamp, /* Applied at */
		'', /* comment */
		sys_user(),
		sys_user()
	 );

ALTER TABLE tbl_client_export_id ADD COLUMN attachment_id       INTEGER REFERENCES tbl_attachment (attachment_id);
ALTER TABLE tbl_client_export_id_log ADD COLUMN attachment_id       INTEGER;
DROP VIEW client_export_id ; 
CREATE VIEW client_export_id AS SELECT * FROM tbl_client_export_id WHERE NOT is_deleted;

COMMIT;

