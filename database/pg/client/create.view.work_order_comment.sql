CREATE VIEW work_order_comment AS (
SELECT
  work_order_comment_id,
  work_order_id,
  comment,
  has_protected_info,
  attachment_description,
  attachment,
  added_by,
  added_at,
  changed_by,
  changed_at,
  is_deleted,
  deleted_at,
  deleted_by,
  deleted_comment,
  sys_log
FROM tbl_work_order_comment
WHERE NOT is_deleted
);
