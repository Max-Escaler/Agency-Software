CREATE OR REPLACE VIEW housing_unit_current AS
SELECT * FROM housing_unit WHERE housing_unit_date <= CURRENT_DATE 
	AND (housing_unit_date_end >= CURRENT_DATE OR housing_unit_date_end IS NULL);
