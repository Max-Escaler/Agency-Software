CREATE OR REPLACE VIEW service_heet AS
SELECT 	service_id,
	client_id,
	contact_type_code,
	service_code,
	service_minutes,
	service_by,
	service_date::date,
	progress_note,
	service_progress_note_id,
	service_project_code,
	added_by,
	added_at,
	changed_by,
	changed_at,
	is_deleted,
	deleted_at,
	deleted_by,
	deleted_comment,
	sys_log
 FROM tbl_service WHERE service_project_code = 'HEET' AND NOT is_deleted;

