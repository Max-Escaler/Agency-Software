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
		'HISPANIC_GENERAL_CODE', /*UNIQUE_DB_MOD_NAME */
		'Adds generic Hispanic code.',
		'CLIENT', /* Which flavor of AGENCY.  AGENCY_CORE applies to all installations */
		'', /* git SHA ID, if applicable */
		'db_mod.57', /* git tag, if applicable */
		current_timestamp, /* Applied at */
		'This is intended to be distinct from "hispanic, other"', /* comment */
		sys_user(),
		sys_user()
	 );

INSERT INTO tbl_l_hispanic_origin (hispanic_origin_code,description,added_by,changed_by,sys_log) VALUES ('HISP_GEN','Hispanic, specificity unknown',sys_user(),sys_user(),'db_mod.57');


COMMIT;

