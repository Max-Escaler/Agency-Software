CREATE VIEW alert_consolidated AS
SELECT DISTINCT ON (staff_id,ref_table,ref_id)
	alert_id,
	staff_id,
	ref_table,
	ref_id::bigint,
	alert_subject,
	get_alert_text(staff_id,ref_table,ref_id) AS alert_text,
	has_read,
	read_at,
	added_by,
	added_at,
	changed_by,
	changed_at,
	is_deleted,
	deleted_at,
	deleted_by,
	deleted_comment,
	sys_log   
FROM alert
ORDER BY staff_id,ref_table,ref_id,alert_id DESC;
