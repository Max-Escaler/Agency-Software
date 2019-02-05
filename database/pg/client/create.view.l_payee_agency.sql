CREATE OR REPLACE VIEW l_payee_agency AS
SELECT agency_code AS payee_agency_code,
	description
FROM l_agency WHERE agency_category_code IN  ('ALL','PAYEE');
