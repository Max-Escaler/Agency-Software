CREATE VIEW bar_guest AS (
	SELECT * FROM bar WHERE guest_id IS NOT NULL
);

CREATE VIEW bar_guest_current AS (
	SELECT * FROM bar_guest WHERE
	bar_date <= current_date
	AND COALESCE(bar_date_end,current_date) >= current_date
);

