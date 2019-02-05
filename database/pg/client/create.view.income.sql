CREATE OR REPLACE VIEW income AS
SELECT i.income_id,
	i.client_id,
	i.income_date,
	i.income_date_end,
	(i.monthly_income_primary
	+ COALESCE(i.monthly_income_secondary,0)
	+ COALESCE(i.monthly_interest_income,0))*12 AS annual_income,
	i.monthly_income_primary,
	i.income_primary_code,
	i.monthly_income_secondary,
	i.income_secondary_code,
	i.monthly_interest_income,
	i.other_assistance_1_code,
	i.other_assistance_2_code,
	i.is_income_certification,
	i.is_sha_income_certification,
	i.income_certification_type_code,
	i.housing_unit_code,
	i.rent_amount_tenant,
	i.rent_amount_total,	
	i.fund_type_code,
	i.grant_number,
	i.rent_date_effective,
	CASE 
		WHEN i.is_income_certification THEN
			COALESCE((SELECT COALESCE(i2.rent_date_effective,i2.income_date) - 1 
					FROM tbl_income i2 --next income record, same project
					WHERE i.client_id = i2.client_id
						AND NOT i2.is_deleted
						AND i2.is_income_certification
						AND i2.income_date > i.income_date
						AND SUBSTRING(i2.housing_unit_code,1,1) = SUBSTRING(i.housing_unit_code,1,1)
					ORDER BY i2.income_date LIMIT 1),

				(SELECT i2.income_date - 1 
					FROM tbl_income i2 --next income date, not matching current project (bug 9276)
					WHERE i.client_id=i2.client_id
						AND NOT i2.is_deleted
						AND i2.is_income_certification
						AND i2.income_date > i.income_date
						AND SUBSTRING(i2.housing_unit_code,1,1) <> SUBSTRING(i.housing_unit_code,1,1)
					ORDER BY i2.income_date LIMIT 1
				)
			) 
		ELSE
			NULL::date
	END AS rent_date_end,
	i.added_by,
	i.added_at,
	i.changed_by,
	i.changed_at,
	i.is_deleted,
	i.deleted_at,
	i.deleted_by,
	i.deleted_comment,
	i.sys_log
FROM tbl_income i WHERE NOT is_deleted;
