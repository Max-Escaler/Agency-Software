BEGIN;

/*
 * db_mod.TEMPLATE
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

	 VALUES ('ADD_ID_TO_HOUSING_UNIT', /*UNIQUE_DB_MOD_NAME */
			'Adds a housing_id SERIAL field to housing unit.', /* DESCRIPTION */
			'CLIENT', /* Which flavor of AGENCY.  AGENCY_CORE applies to all installations */
			'', /* git SHA ID, if applicable */
			'db_mod.11', /* git tag, if applicable */
			current_timestamp, /* Applied at */
			'', /* comment */
			sys_user(),
			sys_user()
		  );

DROP VIEW housing_history;
DROP VIEW housing_unit_current;
DROP VIEW housing_unit;
ALTER TABLE tbl_residence_own DROP CONSTRAINT tbl_residence_own_housing_unit_code_fkey;
ALTER TABLE tbl_income DROP CONSTRAINT tbl_income_housing_unit_code_fkey;
ALTER TABLE tbl_housing_unit_subsidy DROP CONSTRAINT tbl_housing_unit_subsidy_housing_unit_code_fkey;
ALTER TABLE tbl_housing_unit DROP CONSTRAINT tbl_housing_unit_pkey;
ALTER TABLE tbl_housing_unit ADD COLUMN housing_unit_id SERIAL PRIMARY KEY;
ALTER TABLE tbl_housing_unit ADD CONSTRAINT tbl_l_housing_unit_housing_unit_code_unique UNIQUE (housing_unit_code);
AlTER TABLE tbl_residence_own ADD FOREIGN KEY (housing_unit_code) REFERENCES tbl_housing_unit (housing_unit_code);
AlTER TABLE tbl_income ADD FOREIGN KEY (housing_unit_code) REFERENCES tbl_housing_unit (housing_unit_code);
AlTER TABLE tbl_residence_own ADD FOREIGN KEY (housing_unit_code) REFERENCES tbl_housing_unit (housing_unit_code);
CREATE VIEW housing_unit AS (SELECT * FROM tbl_housing_unit WHERE NOT is_deleted);
\i ../client/housing/create.view.housing_unit_current.sql
\i ../client/housing/create.view.housing_history.sql

COMMIT;
