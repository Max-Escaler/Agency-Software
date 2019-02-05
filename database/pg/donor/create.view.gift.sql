CREATE VIEW gift AS
SELECT  
		gift_cash_id::text||'::gift_cash' AS gift_id,
		donor_id,
		gift_cash_date AS gift_date,
		received_date,
		gift_cash_amount AS gift_amount,
		gift_cash_form_code AS gift_form,
		reference_no,
		response_code,
		restriction_code,
		skip_thanks,
		gift_cash_comment AS gift_comment,
		is_anonymous,
		added_by,
		added_at,
		changed_by,
		changed_at,
		sys_log
FROM
		gift_cash
UNION
SELECT
		gift_inkind_id::text||'::gift_inkind' AS gift_id,
		donor_id,
		gift_inkind_date AS gift_date,
		NULL AS received_date,
		value_total AS gift_amount,
		inkind_item_code AS gift_form,
		reference_no,
	        response_code,
        	restriction_code,
	        skip_thanks,
        	gift_inkind_comment AS gift_comment,
	        is_anonymous,
        	added_by,
	        added_at,
        	changed_by,
	        changed_at,
	        sys_log
FROM
		gift_inkind;
