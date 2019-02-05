/*
Simple Client Report
*/

INSERT INTO tbl_report (report_title, report_category_code, report_header, report_footer, report_comment, client_page, allow_output_screen, allow_output_spreadsheet, override_sql_security, rows_per_page, output, report_permission, sql, variables, added_by, added_at, changed_by, changed_at, is_deleted, deleted_at, deleted_by, deleted_comment, sys_log) VALUES ('Simple client report', 'GENERAL', 'Simple Donor Report
Sorted by $order_label
', NULL, 'This is a a very simple donor report', NULL, true, true, false, NULL, NULL, NULL, 'SELECT donor_id,donor_type_code,is_anonymous FROM donor ORDER BY $order', 'PICK order "Sort results by"
donor_name "Donor Name"
donor_type_code,donor_name "Donor Type"
ENDPICK
', 1, current_timestamp, 1, current_timestamp, false, NULL, NULL, NULL, NULL);

