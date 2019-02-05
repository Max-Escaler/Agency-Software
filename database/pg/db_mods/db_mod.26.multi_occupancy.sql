 BEGIN;
ALTER TABLE tbl_housing_unit ADD COLUMN max_occupant INTEGER NOT NULL DEFAULT 1;

/* This will break if table logging not enabled.
	If that happens, comment out the
	"ALTER TABLE tbl_housing_unit_log..." line,
	and run this db_mod again.
  */
ALTER TABLE tbl_housing_unit_log ADD COLUMN max_occupant INTEGER NOT NULL DEFAULT 1;

DROP VIEW housing_history;
DROP VIEW housing_unit_current;
DROP VIEW housing_unit;

CREATE OR REPLACE VIEW housing_unit AS
SELECT * FROM tbl_housing_unit WHERE NOT is_deleted;

CREATE OR REPLACE VIEW housing_unit_current AS
SELECT * FROM housing_unit 
	WHERE housing_unit_date_end > CURRENT_DATE 
		OR housing_unit_date_end IS NULL;

\i ../client/housing/create.view.housing_history.sql
\i ../client/functions/create.multi_occupancy_functions.sql

CREATE TRIGGER check_max_occupant 
	AFTER INSERT OR UPDATE 
	ON tbl_residence_own
    FOR EACH ROW EXECUTE PROCEDURE enforce_max_occupant();

COMMIT;

