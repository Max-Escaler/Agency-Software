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
		'FIX_STAFF_POSITION_LENGTH_IN_STAFF_EMPLOYMENT', /*UNIQUE_DB_MOD_NAME */
		'Fix staff position length in staff_employment',
		'AGENCY_CORE', /* Which flavor of AGENCY.  AGENCY_CORE applies to all installations */
		'', /* git SHA ID, if applicable */
		'db_mod.70', /* git tag, if applicable */
		current_timestamp, /* Applied at */
		'', /* comment */
		sys_user(),
		sys_user()
	 );

\cd ../agency_core
DROP VIEW staff;
DROP VIEW staff_employment_latest;
DROP VIEW staff_employment_current;
DROP VIEW staff_employment;
ALTER TABLE tbl_staff_employment ALTER COLUMN staff_position_code TYPE VARCHAR(20);
ALTER TABLE tbl_staff_employment_log ALTER COLUMN staff_position_code TYPE VARCHAR(20);
\i create.view.staff_employment.sql
\i create.view.staff.sql
\cd ../db_mods

COMMIT;

