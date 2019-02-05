CREATE OR REPLACE VIEW donor_total AS
SELECT gift_cash.donor_id AS donor_total_id,
	gift_cash.donor_id,
	NULL::integer AS "year",
	COUNT(*) AS gift_count,
	SUM(gift_cash.gift_cash_amount) AS gift_total,
	AVG(gift_cash.gift_cash_amount)::integer AS gift_average,
	MAX(gift_cash.gift_cash_amount) AS gift_max
   FROM gift_cash
  GROUP BY gift_cash.donor_id
UNION
 SELECT (gift_cash.donor_id::text || COALESCE(DATE_PART('year'::text, COALESCE(gift_cash.gift_cash_date,gift_cash.received_date)), 0::double precision)::text)::integer AS donor_total_id,
	gift_cash.donor_id,
	date_part('year'::text, COALESCE(gift_cash.gift_cash_date,gift_cash.received_date)) AS "year",
	COUNT(*) AS gift_count,
	SUM(gift_cash.gift_cash_amount) AS gift_total,
	AVG(gift_cash.gift_cash_amount)::integer AS gift_average,
	MAX(gift_cash.gift_cash_amount) AS gift_max
   FROM gift_cash
  GROUP BY gift_cash.donor_id, date_part('year'::text, COALESCE(gift_cash.gift_cash_date,gift_cash.received_date))
  ORDER BY 3 DESC;

