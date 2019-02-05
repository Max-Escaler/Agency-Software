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

	 VALUES ('real_l_hospital', /*UNIQUE_DB_MOD_NAME */
			'Adds a l_hospital lookup table', /* DESCRIPTION */
			'CLIENT', /* Which flavor of AGENCY.  AGENCY_CORE applies to all installations */
			'', /* git SHA ID, if applicable */
			'db_mod.64', /* git tag, if applicable */
			current_timestamp, /* Applied at */
			'', /* comment */
			sys_user(),
			sys_user()
		  );


-- clear out old view
DROP VIEW l_hospital;
-- this should break if you already have tbl_l_hospital
\i ../client/create.l_hospital.sql

COMMIT;

