/*
Simple Staff Report
*/

/*
INSERT INTO tbl_report (report_title, report_category_code, report_header, report_footer, report_comment, client_page, suppress_output_codes, override_sql_security, rows_per_page, output, report_permission, sql, variables, added_by, added_at, changed_by, changed_at, is_deleted, deleted_at, deleted_by, deleted_comment, sys_log) VALUES ('Simple staff report', 'GENERAL', 'Simple staff report', NULL, NULL, NULL, NULL, false, NULL, NULL, NULL, 'SELECT staff_id,staff_program(staff_id),staff_project(staff_id) FROM staff', NULL, 1, current_timestamp, 1, current_timestamp, false, NULL, NULL, NULL, NULL);

INSERT INTO tbl_report (report_title, report_category_code, report_header, report_footer, report_comment, client_page, suppress_output_codes, override_sql_security, rows_per_page, output, report_permission, sql, variables, added_by, changed_by, is_deleted, deleted_at, deleted_by, deleted_comment, sys_log) VALUES ('Permissions Assigned', 'GENERAL', 'AGENCY Permissions Report', NULL, 'This report lists all the permissions that have been assigned, from the permission table', NULL, NULL, false, NULL, NULL, 'admin', 'SELECT 
  permission_date,
  permission_basis,
  permission_type_code,
  CASE WHEN permission_read THEN ''R'' ELSE '' '' END 
  || CASE WHEN permission_write THEN ''W'' ELSE '' '' END 
  || CASE WHEN permission_super THEN ''S'' ELSE '' '' END AS rws, 
  comment,
  agency_program_code,
  agency_project_code,
  staff_position_code,
  staff_id
  FROM permission', NULL, sys_user(), sys_user(), false, NULL, NULL, NULL, NULL);
*/

INSERT INTO tbl_report (report_code, report_title, report_category_code, report_header, report_footer, report_comment, client_page, suppress_output_codes, override_sql_security, rows_per_page, output_template_codes, permission_type_codes, variables, css_class, css_id, block_merge_force_count, block_merge_specific, added_by, added_at, changed_by, changed_at, is_deleted, deleted_at, deleted_by, deleted_comment, sys_log) VALUES ('SIMPLE_STAFF_REPORT', 'Simple staff report', 'GENERAL', 'Simple staff report', NULL, NULL, NULL, '{}', false, NULL, NULL, '{}', NULL, NULL, NULL, NULL, NULL, sys_user(), current_timestamp, sys_user(), current_timestamp, false, NULL, NULL, NULL, NULL);
INSERT INTO tbl_report (report_code, report_title, report_category_code, report_header, report_footer, report_comment, client_page, suppress_output_codes, override_sql_security, rows_per_page, output_template_codes, permission_type_codes, variables, css_class, css_id, block_merge_force_count, block_merge_specific, added_by, added_at, changed_by, changed_at, is_deleted, deleted_at, deleted_by, deleted_comment, sys_log) VALUES ('PERMISSIONS_ASSIGNED', 'Permissions Assigned', 'GENERAL', 'AGENCY Permissions Report', NULL, 'This report lists all the permissions that have been assigned, from the permission table', NULL, '{}', false, NULL, NULL, '{ADMIN}', NULL, NULL, NULL, NULL, NULL, sys_user(), current_timestamp, sys_user(), current_timestamp, false, NULL, NULL, NULL, NULL);



INSERT INTO tbl_report_block (report_code, is_enabled, report_block_sql, report_block_title, report_block_header, report_block_footer, report_block_comment, message_if_empty, message_if_error, suppress_output_codes, suppress_header_row, suppress_row_numbers, execution_required, override_sql_security, permission_type_codes, sort_order_id_manual, report_block_type_code, css_class, css_id, block_merge_name, sql_library_id, added_by, added_at, changed_by, changed_at, is_deleted, deleted_at, deleted_by, deleted_comment, sys_log) VALUES ('SIMPLE_STAFF_REPORT', true, 'SELECT staff_id,staff_program(staff_id),staff_project(staff_id) FROM staff', NULL, NULL, NULL, NULL, NULL, NULL, NULL, false, false, false, false, NULL, NULL, 'TABLE', NULL, NULL, NULL, NULL, sys_user(), current_timestamp, sys_user(), current_timestamp, false, NULL, NULL, NULL, NULL);
INSERT INTO tbl_report_block (report_code, is_enabled, report_block_sql, report_block_title, report_block_header, report_block_footer, report_block_comment, message_if_empty, message_if_error, suppress_output_codes, suppress_header_row, suppress_row_numbers, execution_required, override_sql_security, permission_type_codes, sort_order_id_manual, report_block_type_code, css_class, css_id, block_merge_name, sql_library_id, added_by, added_at, changed_by, changed_at, is_deleted, deleted_at, deleted_by, deleted_comment, sys_log) VALUES ('PERMISSIONS_ASSIGNED', true, 'SELECT 
  permission_date,
  permission_basis,
  permission_type_code,
  CASE WHEN permission_read THEN ''R'' ELSE '' '' END 
  || CASE WHEN permission_write THEN ''W'' ELSE '' '' END 
  || CASE WHEN permission_super THEN ''S'' ELSE '' '' END AS rws, 
  comment,
  agency_program_code,
  agency_project_code,
  staff_position_code,
  staff_id
  FROM permission', NULL, NULL, NULL, NULL, NULL, NULL, NULL, false, false, false, false, NULL, NULL, 'TABLE', NULL, NULL, NULL, NULL, sys_user(), current_timestamp,sys_user(),current_timestamp, false, NULL, NULL, NULL, NULL);

