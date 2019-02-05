----------------------------------------NOTE-------------------------------------
--                                                                             --
-- view created with a 20-day +/- window on matching previous and next records --
--                                                                             --
---------------------------------------------------------------------------------
CREATE OR REPLACE VIEW residence_own AS
SELECT r1.residence_own_id,
	r1.client_id,
	r1.housing_project_code,
	r1.housing_unit_code,
	r1.residence_date,
	r1.residence_date_end,
	r1.moved_from_code,
	r1.chronic_homeless_status_code,
	CASE WHEN ((SELECT r2.client_id FROM tbl_residence_own r2 WHERE ((((r1.residence_date >= (r2.residence_date_end - 20)) AND (r1.residence_date <= (r2.residence_date_end + 20))) AND (r1.client_id = r2.client_id)) AND ((r1.housing_project_code)::text = (r2.housing_project_code)::text) AND r1.residence_own_id != r2.residence_own_id) LIMIT 1) IS NOT NULL) THEN 'Unit Transfer'::text ELSE 'Move-in'::text END AS move_in_type,
	r1.lease_on_file,
	r1.moved_to_code,
	(SELECT r2.housing_unit_code FROM tbl_residence_own r2 WHERE (((r1.residence_date_end >= (r2.residence_date - 20)) AND (r1.residence_date_end <= (r2.residence_date + 20))) AND (r1.client_id = r2.client_id) AND r1.residence_own_id != r2.residence_own_id) LIMIT 1) AS moved_to_unit,
	r1.departure_type_code,
	r1.departure_reason_code,
	r1.move_out_was_code,
	r1.returned_homeless,
	r1."comment",
	r1.added_by,
	r1.added_at,
	r1.changed_by,
	r1.changed_at,
	r1.is_deleted,
	r1.deleted_at,
	r1.deleted_by,
	r1.deleted_comment,
	r1.sys_log
FROM tbl_residence_own r1 WHERE (NOT r1.is_deleted);
