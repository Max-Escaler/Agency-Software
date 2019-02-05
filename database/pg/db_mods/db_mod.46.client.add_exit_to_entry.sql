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
	(db_revision_code,
	db_revision_description,
	agency_flavor_code,
	git_sha,
	git_tag,
	applied_at,
	comment,
	added_by,
	changed_by)

	 VALUES ('ADD_EXIT_TO_ENTRY', /*UNIQUE_DB_MOD_NAME */
			'Adds exited_at field to entry table', /* DESCRIPTION */
			'CLIENT', /* Which flavor of AGENCY.  AGENCY_CORE applies to all installations */
			'', /* git SHA ID, if applicable */
			'db_mod.46', /* git tag, if applicable */
			current_timestamp, /* Applied at */
			'', /* comment */
			sys_user(),
			sys_user()
		  );

ALTER TABLE tbl_entry ADD COLUMN exited_at TIMESTAMP;
ALTER TABLE tbl_entry_log ADD COLUMN exited_at TIMESTAMP;

DROP VIEW elevated_concern_note;

DROP VIEW entry;
CREATE VIEW entry AS (SELECT * FROM tbl_entry WHERE NOT is_deleted);
\i ../client/create.view.elevated_concern_note.sql


COMMIT;
