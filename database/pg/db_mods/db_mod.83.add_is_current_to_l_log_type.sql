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
		'ADD_IS_CURRENT_TO_L_LOG_TYPE', /*UNIQUE_DB_MOD_NAME */
		'Adds is_current field to l_log_type',
		'AGENCY_CORE', /* Which flavor of AGENCY.  AGENCY_CORE applies to all installations */
		'', /* git SHA ID, if applicable */
		'db_mod.83', /* git tag, if applicable */
		current_timestamp, /* Applied at */
		'', /* comment */
		sys_user(),
		sys_user()
	 );

ALTER TABLE tbl_l_log_type ADD COLUMN is_current BOOLEAN NOT NULL DEFAULT true;
ALTER TABLE tbl_l_log_type_log ADD COLUMN is_current BOOLEAN;
CREATE OR REPLACE VIEW l_log_type AS SELECT * FROM tbl_l_log_type WHERE NOT is_deleted;

COMMIT;

