CREATE VIEW work_order AS (
SELECT
  work_order_id,
  title,
  wo.description,
  assigned_to,
  cc_list,
  work_order_status_code,
  priority,
  blocked_by_ids,
  blocker_of_ids,
  agency_project_code,
  work_order_category_code,
  housing_project_code,
  housing_unit_code,
  target_date,
  next_action_date,
  closed_date,
  hours_estimated,
  hours_actual,
  wo.added_by,
  wo.added_at,
  wo.changed_by,
  wo.changed_at,
  wo.is_deleted,
  wo.deleted_by,
  wo.deleted_comment,
  wo.sys_log,
  COALESCE(last_touch_at,wo.changed_at) AS last_touch_at,
  COALESCE(last_touch_by,wo.changed_by) AS last_touch_by,
  lwos.is_open_status AS is_open
FROM tbl_work_order wo
LEFT JOIN (
	SELECT DISTINCT ON (work_order_id)
		work_order_id,
		changed_at AS last_touch_at,
		changed_by AS last_touch_by
	FROM work_order_comment woc 
	ORDER BY work_order_id,changed_at DESC
	) woc USING (work_order_id)
LEFT JOIN l_work_order_status lwos USING (work_order_status_code)
WHERE NOT wo.is_deleted
);

