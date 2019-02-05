CREATE VIEW report_block AS (SELECT 
		report_block_id,
		report_code,
		is_enabled,
		report_block_sql,
		report_block_title,
		report_block_header,
		report_block_footer,
		report_block_comment,
		message_if_empty,
		message_if_error,
		suppress_output_codes,
		suppress_header_row,
		suppress_row_numbers,
		execution_required,
		override_sql_security,
		permission_type_codes,
		sort_order_id_manual,
		report_block_type_code,
		css_class,
		css_id,
		block_merge_name,
		sql_library_id,
		COALESCE(sort_order_id_manual,report_block_id) AS sort_order_id,
		added_by,
		added_at,
		changed_by,
		changed_at,
		is_deleted,
		deleted_at,
		deleted_by,
		deleted_comment,
		sys_log

		FROM tbl_report_block
		WHERE NOT is_deleted
		ORDER BY COALESCE(sort_order_id_manual,report_block_id)
);

