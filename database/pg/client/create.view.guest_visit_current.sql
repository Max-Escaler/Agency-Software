CREATE VIEW guest_visit_current AS
	SELECT
		guest_visit.*,
		name_full AS guest_name
	FROM
		guest_visit
	LEFT JOIN
		guest USING (guest_id)
	WHERE entered_at IS NOT NULL
		AND exited_at IS NULL
;
