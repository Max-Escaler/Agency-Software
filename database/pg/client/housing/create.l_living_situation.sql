CREATE VIEW l_living_situation AS
SELECT	facility_code AS living_situation_code,
		description,
		housing_status FROM l_facility
UNION
SELECT	housing_project_code AS living_situation_code,
		description,
		'HOUSED' AS housing_status FROM l_housing_project;

