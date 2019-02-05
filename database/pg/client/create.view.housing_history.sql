/*
 * You can customize defaults
 */

CREATE OR REPLACE VIEW housing_history AS

SELECT 	residence_own.residence_own_id::text || '::residence_own'::text AS housing_history_id,
	residence_own.client_id,
	residence_own.housing_project_code AS living_situation_code, 
	geography_code,
	geography_detail_code,
    COALESCE(h.city,hp.city) AS city,
	COALESCE(h.state,hp.state_code) AS state_code,
    COALESCE(h.zipcode,hp.zipcode) AS zip_code, 
	residence_own.housing_unit_code, 
	residence_own.residence_date, 
	'E'::varchar AS residence_date_accuracy, 
	residence_own.residence_date_end,
    CASE
    	WHEN residence_own.residence_date_end IS NOT NULL THEN 'E'::character varying
        ELSE NULL::character varying
    END AS residence_date_end_accuracy, 
	residence_own.moved_from_code, 
	residence_own.move_in_type, 
	residence_own.moved_to_code, 
	residence_own.moved_to_unit, 
	residence_own.departure_type_code, 
	residence_own.departure_reason_code, 
	residence_own.move_out_was_code, 
	residence_own."comment", 
	residence_own.lease_on_file, 
	residence_own.added_at, 
	residence_own.added_by, 
	residence_own.changed_at, 
	residence_own.changed_by, 
	residence_own.sys_log
FROM residence_own
LEFT JOIN l_housing_project hp USING (housing_project_code)
LEFT JOIN housing_unit h USING (housing_unit_code)

UNION ALL

SELECT 	residence_other.residence_other_id::text || '::residence_other'::text AS housing_history_id,
	residence_other.client_id, 
	residence_other.facility_code AS living_situation_code, 
	residence_other.geography_code, 
	residence_other.geography_detail_code, 
	residence_other.city,
	residence_other.state_code,
	residence_other.zipcode, NULL::character varying AS housing_unit_code, 
	residence_other.residence_date, 
	residence_other.residence_date_accuracy, 
	residence_other.residence_date_end, 
	residence_other.residence_date_end_accuracy, 
	residence_other.moved_from_code, 
	NULL::character varying AS move_in_type, 
	residence_other.moved_to_code, 
	NULL::character varying AS moved_to_unit, 
	residence_other.departure_type_code, 
	residence_other.departure_reason_code, 
	'UNKNOWN'::character varying AS move_out_was_code, 
	residence_other."comment", 
	NULL::boolean AS lease_on_file, 
	residence_other.added_at, 
	residence_other.added_by, 
	residence_other.changed_at, 
	residence_other.changed_by, 
	residence_other.sys_log
FROM residence_other
ORDER BY 2, 8;

