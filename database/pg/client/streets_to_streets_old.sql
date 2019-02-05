UPDATE 	tbl_application_housing_other
SET	housing_type_code = 'STREET_OLD',
	changed_by = 730,
	sys_log = 'changed STREETS to STREET_OLD'
WHERE	housing_type_code = 'STREETS';


UPDATE	tbl_residence_other
SET	facility_code = 'STREET_OLD',
	changed_by = 730,
	sys_log = 'changed STREETS to STREET_OLD'
WHERE	facility_code = 'STREETS';


UPDATE	tbl_residence_other
SET	moved_from_code = 'STREET_OLD',
	changed_by = 730,
	sys_log = 'changed STREETS to STREET_OLD'
WHERE	moved_from_code = 'STREETS';


UPDATE	tbl_residence_other
SET	moved_to_code = 'STREET_OLD',
	changed_by = 730,
	sys_log = 'changed STREETS to STREET_OLD'
WHERE	moved_to_code = 'STREETS';


UPDATE	tbl_residence_own
SET	moved_from_code = 'STREET_OLD',
	changed_by = 730,
	sys_log = 'changed STREETS to STREET_OLD'
WHERE	moved_from_code = 'STREETS';


UPDATE	tbl_residence_own
SET	moved_to_code = 'STREET_OLD',
	changed_by = 730,
	sys_log = 'changed STREETS to STREET_OLD'
WHERE	moved_to_code = 'STREETS';


DELETE FROM l_facility
WHERE	facility_code = 'STREETS';