CREATE VIEW activity AS (
	SELECT
			client_id,
			bed_id	AS	ref_id,
			'bed'	AS	activity_type,
			bed_date::timestamp	AS	activity_time	
	FROM
			bed
UNION
	SELECT
			client_id,
			ref_id	AS	ref_id,
			ref_table		AS	activity_type,
			added_at 		AS	activity_time	
	FROM
			client_ref
UNION
	SELECT
			client_id,
			entry_id	AS	ref_id,
			'entry'	AS	activity_type,
			entered_at	AS	activity_time	
	FROM
			entry
UNION
	SELECT
			client_id,
			bar_id	AS	ref_id,
			'bar'	AS	activity_type,
			bar_date::timestamp	AS	activity_time	
	FROM
			bar
UNION
	SELECT
			client_id,
			residence_own_id	AS	ref_id,
			'movedin :)'	AS	activity_type,
			residence_date::timestamp	AS	activity_time	
	FROM
			residence_own
UNION
	SELECT
			client_id,
			residence_own_id	AS	ref_id,
			'movedout :('	AS	activity_type,
			residence_date_end::timestamp	AS	activity_time	
	FROM
			residence_own
	WHERE
			residence_date_end IS NOT NULL
);


