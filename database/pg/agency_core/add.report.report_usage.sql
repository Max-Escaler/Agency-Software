/*
* This is the SQL to load the Report Usage report.
* This report was exported from AGENCY running at REACH at 1/5/18 2:46 pm by
* Ken Tanzer.
*/


/*
* Inserting Report
*/


INSERT INTO tbl_report (report_code, report_title, report_category_code, report_header, report_footer, report_comment, client_page, suppress_output_codes, override_sql_security, rows_per_page, output_template_codes, permission_type_codes, variables, css_class, css_id, block_merge_force_count, block_merge_specific, added_by, changed_by, is_deleted, deleted_at, deleted_by, deleted_comment, sys_log) VALUES ('REPORT_USAGE', 'Report Usage', COALESCE( (SELECT report_category_code FROM l_report_category WHERE report_category_code='AGENCY'),'GENERAL'), 'From $sdate to $edate

Detail sorted by $order_label', NULL, NULL, NULL, NULL, 'f', NULL, NULL, NULL, 'DATE sdate "Enter start date"
DATE edate "Enter end date"

PICK order "Order detail by"
report Report
staff_name(generated_by) "Staff Person"
ENDPICK', NULL, NULL, NULL, NULL, sys_user(), sys_user(), 'f', NULL, NULL, NULL, NULL);




/*
* Inserting Report Blocks
*/


INSERT INTO tbl_report_block (report_code, is_enabled, report_block_sql, report_block_title, report_block_header, report_block_footer, report_block_comment, message_if_empty, message_if_error, suppress_output_codes, suppress_header_row, suppress_row_numbers, execution_required, override_sql_security, permission_type_codes, report_block_type_code, css_class, css_id, block_merge_name, sql_library_id, added_by, changed_by, is_deleted, deleted_at, deleted_by, deleted_comment, sys_log) VALUES ('REPORT_USAGE', 't', 'SELECT 
report_title AS report,
count(*) AS times_run,
last_generated_at AS latest_run,
last_generated_by AS latest_run_by
FROM report_usage
LEFT JOIN report USING (report_code)
WHERE generated_at::date BETWEEN ''$sdate'' AND ''$edate''
GROUP BY 1,3,4
ORDER BY times_run DESC;
', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'f', 'f', 'f', 'f', NULL, 'TABLE', NULL, NULL, NULL, NULL, sys_user(), sys_user(), 'f', NULL, NULL, NULL, NULL);

INSERT INTO tbl_report_block (report_code, is_enabled, report_block_sql, report_block_title, report_block_header, report_block_footer, report_block_comment, message_if_empty, message_if_error, suppress_output_codes, suppress_header_row, suppress_row_numbers, execution_required, override_sql_security, permission_type_codes, report_block_type_code, css_class, css_id, block_merge_name, sql_library_id, added_by, changed_by, is_deleted, deleted_at, deleted_by, deleted_comment, sys_log) VALUES ('REPORT_USAGE', 't', 'SELECT 
report_title AS report,
generated_by AS staff_id,
count(*) AS times_run,
max(generated_at) AS last_run
FROM report_usage
LEFT JOIN report USING (report_code)
WHERE generated_at::date BETWEEN ''$sdate'' AND ''$edate''
GROUP BY 1,2
ORDER BY $order,times_run DESC;
', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'f', 'f', 'f', 'f', NULL, 'TABLE', NULL, NULL, NULL, NULL, sys_user(), sys_user(), 'f', NULL, NULL, NULL, NULL);

