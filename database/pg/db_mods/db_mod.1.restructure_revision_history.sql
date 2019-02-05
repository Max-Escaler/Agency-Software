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

/*
 * Since this db_mod revises the db_mod table, 
 * I moved the insert to the end
 */

/* Restructure */
SELECT * INTO TEMP TABLE tmp_db_rev FROM db_revision_history;
DROP TABLE db_revision_history;
\i ../agency_core/create.l_agency_flavor.sql
\i ../agency_core/create.tbl_db_revision_history.sql

/* Restore the data */
INSERT INTO tbl_db_revision_history
	(db_revision_code,
	agency_flavor_code,
	applied_at,
	comment,
	added_by,
	added_at,
	changed_by,
	sys_log)

	SELECT
		db_modification_id AS db_revision_code,
		'AGENCY_CORE' AS agency_flavor_code,
		added_at AS applied_at,
		CASE WHEN cvs_code_version IS NOT NULL AND cvs_code_version <> ''
			THEN 'CVS code version was: ' || cvs_code_version
			ELSE NULL 
		END AS comment,
		added_by,
		added_at,
		1 AS changed_by,
		COALESCE(sys_log || E'\n','') || 'Moved records to new format.  See db_mod.1...' AS sys_log
	FROM tmp_db_rev;

/* Add this change to revision history */

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

	 VALUES ('DB_REV_RESTRUCTURE', /*UNIQUE_DB_MOD_NAME */
			'Make db_rev a table/view, and add additional fields.', /* DESCRIPTION */
			'AGENCY_CORE', /* Which flavor of AGENCY.  AGENCY_CORE applies to all installations */
			'', /* git SHA ID, if applicable */
			'db_mod_1', /* git tag, if applicable */
			current_timestamp, /* applied_at */
			'', /* comment */
			sys_user(),
			sys_user()
		  );

/*
Uncomment the COMMIT when your revision works,
and before checking it in.
*/

COMMIT;
