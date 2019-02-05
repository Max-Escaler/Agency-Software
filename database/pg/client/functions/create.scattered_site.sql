CREATE SEQUENCE seq_scattered_site_unit;

CREATE OR REPLACE FUNCTION next_scattered_site_unit() RETURNS VARCHAR(5) AS '
DECLARE
	ns INTEGER;
	new_unit VARCHAR(5);
BEGIN
	SELECT INTO ns nextval(''seq_scattered_site_unit'');
	IF ns > 9999 THEN
		RAISE EXCEPTION ''seq_scattered_site_unit has exceeded maximum value of 9999.\nAt a minimum, next_scattered_site_unit() needs to be modified to return more than 5 numeric digits\n'';
	END IF;
	SELECT INTO new_unit ''S''||LPAD(ns,4,''0'');
	RETURN new_unit;
END;' LANGUAGE 'plpgsql';

CREATE OR REPLACE FUNCTION set_scattered_site_unit() RETURNS TRIGGER AS '
DECLARE
	new_unit VARCHAR(5);
BEGIN
	/*
	 * For now, this function will only create new ID if no ID is passed
	 */

	IF NEW.housing_project_code = ''SCATTERED'' AND NEW.housing_unit_code IS NULL THEN
		new_unit := next_scattered_site_unit();
		NEW.housing_unit_code := new_unit;
		RAISE NOTICE ''New Scattered Site Unit % has been created'', new_unit;
	END IF;
	RETURN NEW;
END;' LANGUAGE 'plpgsql';

CREATE TRIGGER set_scattered_site_unit BEFORE INSERT
	ON tbl_housing_unit FOR EACH ROW EXECUTE PROCEDURE set_scattered_site_unit();