CREATE OR REPLACE VIEW balance_project AS (
SELECT 
	client_id,
	housing_project_code,
	CASE WHEN is_subsidy THEN 'SUBSIDY' ELSE 'CLIENT' END as balance_type,
	COALESCE(charge_total,0) - COALESCE(payment_total,0) AS balance
FROM

( SELECT
	client_id,
	housing_project_code,
	is_subsidy,
	COALESCE(sum(amount),0) AS charge_total
FROM
	charge
WHERE NOT is_void
GROUP BY 1,2,3 ) AS charge

FULL OUTER JOIN

 ( SELECT
	client_id,
	housing_project_code,
	is_subsidy,
	COALESCE(sum(amount),0) AS payment_total
FROM
	payment
WHERE NOT is_void
GROUP BY 1,2,3) AS payment
	USING (client_id,housing_project_code,is_subsidy)
);

CREATE OR REPLACE VIEW balance_combined AS (
    SELECT distinct
    client_id,
    housing_project_code,
    (SELECT balance FROM balance_project bp2 WHERE client_id=bp.client_id AND housing_project_code=bp.housing_project_code AND balance_type='CLIENT') AS balance_client,
    (SELECT balance FROM balance_project bp3 WHERE client_id=bp.client_id AND housing_project_code=bp.housing_project_code AND balance_type='SUBSIDY') AS balance_subsidy
FROM balance_project AS bp
);

