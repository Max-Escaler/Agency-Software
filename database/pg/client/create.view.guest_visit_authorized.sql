CREATE OR REPLACE VIEW guest_visit_authorized AS
	SELECT
		guest_id,
		ga.client_id,
		name_full AS guest_name

	FROM
		guest_authorization_current ga
	LEFT JOIN
		guest USING (guest_id)
	WHERE
		-- Not currently visiting already
		guest_id NOT IN (select guest_id FROM guest_visit_current)
	AND
		-- 2 Concurrent visitors max per client
		(SELECT COUNT(*) FROM guest_visit_current gvs WHERE ga.client_id=gvs.client_id) < 2
	AND
		-- Max 5 unique visitors per day
		(ga.client_id NOT IN (
			SELECT client_id FROM guest_visit WHERE entered_at::date=current_date
				AND guest_id <> ga.guest_id GROUP BY 1 HAVING COUNT(distinct guest_id) >=5)) 

		-- Client is not restricted from visitors
	AND	(ga.client_id NOT IN (SELECT client_id FROM client_guest_ineligible))

		-- Guest is not barred from visiting
	-- FIXME: This checks for barred guests, but not whether barred from specific project
	AND (guest_id NOT IN (SELECT guest_id FROM bar_guest_current))	

;


/*
	AND client_id NOT IN 3+ in last week, etc...
*/
;
