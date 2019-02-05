CREATE TABLE tbl_report (
	report_id				SERIAL PRIMARY KEY,
	report_code				VARCHAR(40) UNIQUE,
	report_title			TEXT NOT NULL,
	report_category_code	VARCHAR(10) REFERENCES tbl_l_report_category (report_category_code),
	report_header			TEXT,
	report_footer			TEXT,
	report_comment			TEXT,
	client_page				VARCHAR (255),
	suppress_output_codes	VARCHAR(20)[], -- references l_output_code (output_code)
	override_sql_security	BOOLEAN DEFAULT FALSE,
	rows_per_page			INTEGER,
	output_template_codes	TEXT, --FIXME: should be array
	permission_type_codes VARCHAR(20)[], -- references l_permission (permission_code)
	variables				TEXT,
	css_class				VARCHAR,
	css_id					VARCHAR,
	block_merge_force_count	INTEGER,
	block_merge_specific	TEXT, -- not implemented yet
	
	--system fields
	added_by				INTEGER NOT NULL REFERENCES tbl_staff (staff_id),
	added_at				TIMESTAMP(0) NOT NULL DEFAULT CURRENT_TIMESTAMP,
	changed_by				INTEGER NOT NULL REFERENCES tbl_staff (staff_id),
	changed_at				TIMESTAMP(0) NOT NULL DEFAULT CURRENT_TIMESTAMP,
	is_deleted				BOOLEAN NOT NULL DEFAULT FALSE,
	deleted_at				TIMESTAMP(0) 
								CHECK ((NOT is_deleted AND deleted_at IS NULL)
								OR (is_deleted AND deleted_at IS NOT NULL)),
	deleted_by				INTEGER REFERENCES tbl_staff(staff_id)
								CHECK ((NOT is_deleted AND deleted_by IS NULL) 
								OR (is_deleted AND deleted_by IS NOT NULL)),
	deleted_comment         TEXT,
	sys_log                 TEXT
/*	EXCLUDE (report_code with =) WHERE (NOT is_deleted) */

/*
  These should be arrays, or child tables:
	OUTPUT
	VARIABLES
*/
);

