BEGIN;

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

	 VALUES ('FIX_RESIDENCE_OWN_PROJECT_CONSTRAINT',
			'The project field in residence_own was still referencing project_agency, instead of housing_project',
			'CLIENT',
			NULL,
			'db_mod_2',
			current_timestamp,
			'',
			sys_user(),
			sys_user()
		  );

ALTER TABLE tbl_residence_own DROP CONSTRAINT tbl_residence_own_housing_project_code_fkey;
ALTER TABLE tbl_residence_own ADD FOREIGN KEY (housing_project_code) REFERENCES tbl_l_housing_project (housing_project_code);

COMMIT;
