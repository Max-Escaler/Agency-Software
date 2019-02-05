/*
 * This report is used to print payment receipts
 * and depends on the payment_receipt.odt template file
 *
 */

INSERT INTO tbl_report (report_title, report_category_code, report_header, report_footer, report_comment, client_page, allow_output_screen, allow_output_spreadsheet, override_sql_security, rows_per_page, output, report_permission, sql, variables, added_by, added_at, changed_by, changed_at, is_deleted, deleted_at, deleted_by, deleted_comment, sys_log) VALUES ('Payment Receipt', NULL, NULL, NULL, 'This report is used to print a formatted receipt for a payment', NULL, true, true, false, NULL, 'payment_receipt.odt|Printed Receipt', NULL, 'SELECT
 payment.*,
 l_payment_type.description AS payment_type_description,
 l_payment_form.description AS payment_form_description,
 l_housing_project.description AS housing_project_description, CASE WHEN is_void THEN ''This payment has been voided and is no longer effective'' ELSE '''' END AS void_message,
 client_name(client_id)
 FROM payment LEFT JOIN l_payment_type USING (payment_type_code)
 LEFT JOIN l_payment_form USING (payment_form_code)
 LEFT JOIN l_housing_project USING (housing_project_code)
 WHERE payment_id=$pid', 'VALUE pid "Payment ID"', sys_user(), current_timestamp, sys_user(), current_timestamp, false, NULL, NULL, NULL, NULL);

