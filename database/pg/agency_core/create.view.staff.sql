CREATE OR REPLACE VIEW staff AS
SELECT tbl_staff.staff_id, 
	tbl_staff.username,
	COALESCE(tbl_staff.username_unix,tbl_staff.username) AS username_unix,
/*
	((COALESCE(tbl_staff.staff_email,tbl_staff.username_unix,tbl_staff.username))||'@desc.org')::varchar(60) AS staff_email,
*/
	COALESCE(tbl_staff.staff_email,tbl_staff.username_unix,tbl_staff.username||'@yourdomain.org')::varchar(60) AS staff_email,
	tbl_staff.kc_staff_id,
	tbl_staff.name_last,
	tbl_staff.name_first,
	tbl_staff.name_first_legal,
	CASE
		WHEN tbl_staff.name_last IS NULL THEN name_first
		WHEN tbl_staff.name_first IS NULL THEN name_last
		ELSE tbl_staff.name_last || ', ' || name_first
	END  AS name_full,
	tbl_staff.is_active,
	tbl_staff.login_allowed,
	staff_employment_latest.staff_title,
	staff_employment_latest.agency_program_code,
	staff_employment_latest.agency_project_code,
	staff_employment_latest.staff_position_code,
	staff_employment_latest.agency_facility_code,
	staff_employment_latest.staff_shift_code,
	staff_employment_latest.agency_staff_type_code,
	staff_employment_latest.staff_employment_status_code,
	staff_employment_latest.hired_on,
	staff_employment_latest.terminated_on,
	staff_employment_latest.supervised_by,
	tbl_staff.pgp_key_public,
	tbl_staff.gender_code,
	tbl_staff.notes,
	tbl_staff.added_by,
	tbl_staff.added_at,
	tbl_staff.changed_by,
	tbl_staff.changed_at,
	tbl_staff.is_deleted,
	tbl_staff.deleted_at,
	tbl_staff.deleted_by,
	tbl_staff.deleted_comment,
	tbl_staff.sys_log 
FROM tbl_staff 
	LEFT JOIN staff_employment_latest USING (staff_id)
WHERE (NOT tbl_staff.is_deleted);
