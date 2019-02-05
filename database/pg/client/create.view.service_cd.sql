CREATE OR REPLACE VIEW service_cd AS
SELECT 	service_id,
	client_id,
	contact_type_code,
	service_code,
	service_minutes,
	service_by,
	service_date,
	asam_dimension_1,
	asam_dimension_2,
	asam_dimension_3,
	asam_dimension_4,
	asam_dimension_5,
	asam_dimension_6,
	progress_note,
	service_progress_note_id,
	service_project_code,
	REPLACE(REPLACE(TRIM(
		COALESCE ( (CASE WHEN service_code = 'CD_TPR' THEN 'Treatment_Plan_Review' ELSE NULL END)||' ','') ||
		COALESCE ( (CASE WHEN asam_dimension_1 THEN '1' ELSE NULL END)||' ','') ||
		COALESCE ( (CASE WHEN asam_dimension_2 THEN '2' ELSE NULL END)||' ','') ||
		COALESCE ( (CASE WHEN asam_dimension_3 THEN '3' ELSE NULL END)||' ','') ||
		COALESCE ( (CASE WHEN asam_dimension_4 THEN '4' ELSE NULL END)||' ','') ||
		COALESCE ( (CASE WHEN asam_dimension_5 THEN '5' ELSE NULL END)||' ','') ||
		COALESCE ( (CASE WHEN asam_dimension_6 THEN '6' ELSE NULL END)||' ','')
		),' ',','),'_',' ') AS asam_dimension_summary,
	added_by,
	added_at,
	changed_by,
	changed_at,
	is_deleted,
	deleted_at,
	deleted_by,
	deleted_comment,
	sys_log FROM tbl_service WHERE service_project_code = 'CD' AND NOT is_deleted;
