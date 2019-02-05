CREATE OR REPLACE VIEW report AS (
SELECT 
	r.report_id,
	r.report_code,
	r.report_title,
	r.report_category_code,
	r.report_header,
	r.report_footer,
	r.report_comment,
	r.client_page,
	r.suppress_output_codes,
	r.override_sql_security,
	r.rows_per_page,
	r.output_template_codes,
	r.permission_type_codes,
	r.variables,
	r.css_class,
	r.css_id,
    r.block_merge_force_count,
	r.block_merge_specific,
	rb.blocks AS block_count,

	--system fields
	r.added_by,
	r.added_at,
	r.changed_by,
	r.changed_at,
	r.is_deleted,
	r.deleted_at,
	r.deleted_by,
	r.deleted_comment,
	r.sys_log,
	u.generated_by AS last_generated_by,
	u.generated_at AS last_generated_at
    FROM tbl_report r
        LEFT JOIN (SELECT DISTINCT ON (report_code) report_id,report_code,generated_at,generated_by FROM report_usage ORDER BY report_code,generated_at DESC) u USING (report_code)
		LEFT JOIN (SELECT report_code,count(*) AS blocks FROM report_block GROUP BY 1) rb USING (report_code)
    WHERE NOT is_deleted
);

