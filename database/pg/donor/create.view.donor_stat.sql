CREATE OR REPLACE VIEW donor_stat AS
SELECT		g.donor_id,
		COUNT(g.*) AS count,
        	SUM(g.gift_amount) AS total,
        	AVG(g.gift_amount)::int AS average,
        	MAX(g.gift_amount) AS max,
		MIN(gift_date) AS first_date,
		(SELECT g2.gift_amount FROM gift g2 WHERE g2.donor_id=g.donor_id 
			ORDER BY g2.gift_date ASC LIMIT 1) AS first_amount,
		MAX(g.gift_date) AS last_date,
		(SELECT g2.gift_amount FROM gift g2 WHERE g2.donor_id=g.donor_id
			ORDER BY g2.gift_date DESC LIMIT 1) AS last_amount
FROM    	gift g
GROUP BY 	g.donor_id;
