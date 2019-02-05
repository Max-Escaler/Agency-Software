/*
Simple Client Report
*/

/*
INSERT INTO tbl_report (report_title, report_category_code, report_header, report_footer, report_comment, client_page, allow_output_screen, allow_output_spreadsheet, override_sql_security, rows_per_page, output, report_permission, sql, variables, added_by, added_at, changed_by, changed_at, is_deleted, deleted_at, deleted_by, deleted_comment, sys_log) VALUES ('Simple client report', 'GENERAL', 'Simple Client Report
Sorted by $order_label
', NULL, 'This is a a very simple client report', NULL, true, true, false, NULL, NULL, NULL, 'SELECT client_id,dob,ssn FROM client ORDER BY $order', 'PICK order "Sort results by"
name_last,name_first "Client Name"
dob "Date of Birth"
ssn "Social Security Number"
ENDPICK
', 1, current_timestamp, 1, current_timestamp, false, NULL, NULL, NULL, NULL);
*/

INSERT INTO tbl_report (report_code, report_title, report_category_code, report_header, report_footer, report_comment, client_page, suppress_output_codes, override_sql_security, rows_per_page, output_template_codes, permission_type_codes, variables, css_class, css_id, block_merge_force_count, block_merge_specific, added_by, added_at, changed_by, changed_at, is_deleted, deleted_at, deleted_by, deleted_comment, sys_log) VALUES ('SIMPLE_CLIENT_REPORT', 'Simple client report', 'GENERAL', 'Simple Client Report
Sorted by $order_label
', NULL, 'This is a a very simple client report', NULL, '{}', false, NULL, NULL, '{}', 'PICK order "Sort results by"
name_last,name_first "Client Name"
dob "Date of Birth"
ssn "Social Security Number"
ENDPICK
', NULL, NULL, NULL, NULL, sys_user(),current_timestamp,sys_user(),current_timestamp, false, NULL, NULL, NULL, NULL);

INSERT INTO tbl_report_block (report_code, is_enabled, report_block_sql, report_block_title, report_block_header, report_block_footer, report_block_comment, message_if_empty, message_if_error, suppress_output_codes, suppress_header_row, suppress_row_numbers, execution_required, override_sql_security, permission_type_codes, sort_order_id_manual, report_block_type_code, css_class, css_id, block_merge_name, sql_library_id, added_by, added_at, changed_by, changed_at, is_deleted, deleted_at, deleted_by, deleted_comment, sys_log) VALUES ('SIMPLE_CLIENT_REPORT', true, 'SELECT client_id,dob,ssn FROM client ORDER BY $order', NULL, NULL, NULL, NULL, NULL, NULL, NULL, false, false, false, false, NULL, NULL, 'TABLE', NULL, NULL, NULL, NULL, sys_user(), current_timestamp, sys_user(), current_timestamp, false, NULL, NULL, NULL, NULL);

