CREATE OR REPLACE VIEW gift_cash AS
SELECT
	g.gift_cash_id,
	g.donor_id,
	g.gift_cash_amount,
	g.received_date,
	g.gift_cash_date,
	g.gift_cash_form_code,
	g.reference_no,
	g.response_code,
	g.restriction_code,
	g.skip_thanks,
	g.gift_cash_comment,
	g.expiration,
	g.authorization_no,
	g.is_anonymous,
	g.contract_code,
/*
	g.mip_export_session_id,
*/
	CASE
		WHEN g.response_code::text >= '600'::text AND g.response_code::text <= '699'::text THEN '6425'::text
		WHEN d.donor_type_code::text = 'INDI'::text AND g.response_code::text >= '800'::text AND g.response_code::text <= '899'::text THEN '6450'::text
		WHEN d.donor_type_code::text = 'INDI'::text OR d.donor_type_code::text = 'GOVE'::text OR d.donor_type_code::text = 'OTHE'::text THEN '6420'::text
		WHEN d.donor_type_code::text = 'CORP'::text THEN '6430'::text
		WHEN d.donor_type_code::text = 'FOUN'::text THEN '6410'::text
		WHEN d.donor_type_code::text IN ('ORGN'::text,'REL'::text) THEN '6440'::text
		WHEN d.donor_type_code::text = 'FFO'::text THEN '6450'::text
		ELSE NULL::text
	END AS account_code,
	CASE
	 	WHEN g.restriction_code = '001' THEN '10'
		WHEN g.contract_code IS NOT NULL THEN '20'
	 	ELSE '30'
	END AS account_117_code,
	g.added_by,
	g.added_at,
	g.changed_by,
	g.changed_at,
	g.is_deleted,
	g.deleted_at,
	g.deleted_by,
	g.deleted_comment,
	g.sys_log
FROM tbl_gift_cash g
 	LEFT JOIN tbl_donor d USING (donor_id)
WHERE NOT g.is_deleted;
