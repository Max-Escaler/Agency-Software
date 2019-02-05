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
	 VALUES ('CLARIFY_KING_COUNTY_OUTSIDE_SEATTLE', /*UNIQUE_DB_MOD_NAME */
			'Update King County label in l_geography to say outside seattle', /* DESCRIPTION */
			'CLIENT', /* Which flavor of AGENCY.  AGENCY_CORE applies to all installations */
			'', /* git SHA ID, if applicable */
			'', /* git tag, if applicable */
			current_timestamp, /* Applied at */
			'', /* comment */
			sys_user(),
			sys_user()
		  );

UPDATE tbl_l_geography
SET description='King County (outside Seattle)',
	changed_by=sys_user(),
	changed_at=current_timestamp,
	sys_log=COALESCE(sys_log||E'\n','') || 'Clarifying King County is for outside Seattle. db_mod.27'
WHERE geography_code='KING'
	AND description='King County'
	AND changed_at=added_at;

COMMIT;
