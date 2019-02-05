DROP VIEW IF EXISTS work_order_comment;
DROP TABLE IF EXISTS tbl_work_order_comment;
DROP VIEW IF EXISTS work_order;
DROP TABLE IF EXISTS tbl_work_order;
DROP VIEW IF EXISTS  l_work_order_category;
DROP TABLE IF EXISTS  tbl_l_work_order_category;
DROP VIEW IF EXISTS  l_work_order_status;
DROP TABLE IF EXISTS  tbl_l_work_order_status;

DROP TABLE IF EXISTS tbl_work_order_comment_log;
DROP TABLE IF EXISTS tbl_work_order_log;

DROP SEQUENCE IF EXISTS tbl_work_order_comment_log_id;
DROP SEQUENCE IF EXISTS tbl_work_order_log_id;

\i create.l_work_order_category.sql
\i create.l_work_order_status.sql
\i create.tbl_work_order.sql
\i create.tbl_work_order_comment.sql

SELECT enable_table_logging('tbl_work_order','');
SELECT enable_table_logging('tbl_work_order_comment','');
