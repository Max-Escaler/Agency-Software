CREATE OR REPLACE VIEW housing_history AS
SELECT
	residence_own_id::text ||'::residence_own' AS housing_history_id, --link_engine will parse this into a table and an id, thus handling appropriately
	client_id,
	residence_own.housing_project_code AS living_situation_code,
	COALESCE(UPPER(h.city),'SEATTLE')::varchar AS geography_code,
	CASE
		WHEN residence_own.housing_project_code = 'KSH' THEN 345 --denny regrade?
		WHEN residence_own.housing_project_code = 'SCATTERED' THEN NULL
		ELSE 220	--downtown
	END AS geography_detail_code,	
	CASE
		WHEN residence_own.housing_project_code = 'KSH' THEN '98109'
		WHEN residence_own.housing_project_code = 'SCATTERED' THEN h.zipcode::text
		ELSE '98104'
	END AS zipcode,	
	housing_unit_code,
	residence_date,
	'E'::varchar AS residence_date_accuracy,
	residence_date_end,
	CASE
		WHEN residence_date_end IS NOT NULL THEN 'E'::varchar 
	END AS residence_date_end_accuracy,
	moved_from_code,
	move_in_type,
	moved_to_code,
	moved_to_unit,
	departure_type_code,
	departure_reason_code,
	move_out_was_code,
	comment,
	lease_on_file,
	residence_own.added_at,
	residence_own.added_by,
	residence_own.changed_at,
	residence_own.changed_by,
	residence_own.sys_log
FROM residence_own
	LEFT JOIN housing_unit h USING (housing_unit_code)

UNION ALL

SELECT
	residence_other_id::text ||'::residence_other' AS housing_history_id,
	client_id,
	facility_code AS living_situation_code, --will require a joint lookup table
	geography_code,
	geography_detail_code,
	zipcode,
	NULL::varchar AS housing_unit_code,
	residence_date,
	residence_date_accuracy,
	residence_date_end,
	residence_date_end_accuracy,
	moved_from_code,
	NULL::varchar AS move_in_type,
	moved_to_code,
	NULL::varchar AS moved_to_unit,
	departure_type_code,
	departure_reason_code,
	'UNKNOWN'::varchar AS move_out_was_code,
	comment,
	NULL::boolean AS lease_on_file,
	added_at,
	added_by,
	changed_at,
	changed_by,
	sys_log
FROM residence_other

ORDER BY client_id,residence_date;
