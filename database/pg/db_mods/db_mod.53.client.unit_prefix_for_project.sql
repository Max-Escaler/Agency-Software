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

	 VALUES ('UNIT_PREFIX_FOR_PROJECT', /*UNIQUE_DB_MOD_NAME */
			'Specify unit prefix string for housing projects', /* DESCRIPTION */
			'CLIENT', /* Which flavor of AGENCY.  AGENCY_CORE applies to all installations */
			'', /* git SHA ID, if applicable */
			'db_mod.53', /* git tag, if applicable */
			current_timestamp, /* Applied at */
			'', /* comment */
			sys_user(),
			sys_user()
		  );


DROP VIEW l_housing_project ;
ALTER TABLE tbl_l_housing_project ADD COLUMN unit_code_prefix VARCHAR(10);
ALTER TABLE tbl_l_housing_project_log ADD COLUMN unit_code_prefix VARCHAR(10);
CREATE VIEW l_housing_project AS SELECT * FROM tbl_l_housing_project WHERE NOT is_deleted;

UPDATE tbl_l_housing_project
 SET unit_code_prefix = prefix,
 changed_by=sys_user(),
 changed_at=current_timestamp,
 sys_log=COALESCE(sys_log||E'\n','') || 'Adding unit prefix based on unit record query'
 from 
	( SELECT housing_project_code,SUBSTRING(housing_unit_code FROM '([A-Za-z]+)[0-9]+') AS prefix
	  FROM housing_unit group by 1,2  ) foo 
  WHERE tbl_l_housing_project.housing_project_code = foo.housing_project_code;

COMMIT;
