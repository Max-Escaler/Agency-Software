CREATE TABLE tbl_report_block (
	report_block_id	SERIAL PRIMARY KEY,
	report_code	VARCHAR(40) NOT NULL REFERENCES tbl_report (report_code),
	is_enabled			BOOLEAN NOT NULL DEFAULT true,
	report_block_sql	TEXT,
	report_block_title	TEXT,
	report_block_header	TEXT,
	report_block_footer	TEXT,
	report_block_comment	TEXT,
	message_if_empty	TEXT,
	message_if_error	TEXT,
	suppress_output_codes	VARCHAR(20)[], -- references l_output_code (output_code)
	suppress_header_row	BOOLEAN DEFAULT FALSE,
	suppress_row_numbers	BOOLEAN DEFAULT FALSE,
	execution_required	BOOLEAN DEFAULT FALSE,
	override_sql_security	BOOLEAN DEFAULT FALSE,
    permission_type_codes VARCHAR(20)[], -- references l_permission (permission_code)
	sort_order_id_manual	FLOAT,
	report_block_type_code	VARCHAR(10) NOT NULL DEFAULT 'TABLE' REFERENCES tbl_l_report_block_type (report_block_type_code), -- table/chart
	css_class		VARCHAR,
	css_id                  VARCHAR,	
	block_merge_name	VARCHAR,

	/* Hook for reference to future shared sql */
	sql_library_id	INTEGER, /* REFERENCES tbl_sql_library (sql_library_id), */

	--system fields
	added_by                        INTEGER NOT NULL REFERENCES tbl_staff (staff_id),
	added_at                        TIMESTAMP(0)     NOT NULL DEFAULT CURRENT_TIMESTAMP,
	changed_by                      INTEGER NOT NULL  REFERENCES tbl_staff (staff_id),
	changed_at                      TIMESTAMP(0)     NOT NULL DEFAULT CURRENT_TIMESTAMP,
	is_deleted                      BOOLEAN NOT NULL DEFAULT FALSE,
	deleted_at                      TIMESTAMP(0) CHECK ((NOT is_deleted AND deleted_at IS NULL) OR (is_deleted AND deleted_at IS NOT NULL)),
	deleted_by                      INTEGER REFERENCES tbl_staff(staff_id)
									CHECK ((NOT is_deleted AND deleted_by IS NULL) OR (is_deleted AND deleted_by IS NOT NULL)),
	deleted_comment         TEXT,
	sys_log                 TEXT

);

