CREATE OR REPLACE VIEW export_safe_harbors_income AS

SELECT i.income_id,
	i.client_id,
	i.income_date,
	i.income_date_end,
	(i.annual_income/12)::numeric(12,2) AS monthly_income_total,
	i.monthly_income_primary,	
	safe_harbors_value('l_income',i.income_primary_code) AS income_primary_code,
	i.monthly_income_secondary,	
	safe_harbors_value('l_income',i.income_secondary_code) AS income_secondary_code,
	CASE WHEN i.monthly_interest_income > 0 THEN safe_harbors_value('l_income','OTHER') 
	END AS income_interest_code,
	i.monthly_interest_income,
	safe_harbors_value('l_other_assistance',i.other_assistance_1_code) AS other_assistance_1_code,
	safe_harbors_value('l_other_assistance',i.other_assistance_2_code) AS other_assistance_2_code,
	i.changed_at
FROM income i;	
	