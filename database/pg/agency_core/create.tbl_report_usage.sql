CREATE TABLE tbl_report_usage
	(
	report_usage_id		SERIAL PRIMARY KEY,
	/* see DB-mod for adding report_id field */
	report_id		INTEGER REFERENCES tbl_report ( report_id ),
	report_code		VARCHAR(40) REFERENCES tbl_report ( report_code ),
	report_name		VARCHAR(100) NOT NULL,
	output_format		VARCHAR(100) NOT NULL,
	generated_by		INTEGER NOT NULL REFERENCES tbl_staff (staff_id),
	generated_at		TIMESTAMP(0) NOT NULL,
	generated_from		VARCHAR NOT NULL,
	--system fields
	added_by		INTEGER NOT NULL REFERENCES tbl_staff (staff_id),
	added_at		TIMESTAMP(0) NOT NULL DEFAULT CURRENT_TIMESTAMP,
	changed_by		INTEGER NOT NULL  REFERENCES tbl_staff (staff_id),
	changed_at		TIMESTAMP(0) NOT NULL DEFAULT CURRENT_TIMESTAMP,
	is_deleted		BOOLEAN NOT NULL DEFAULT FALSE,
	deleted_at		TIMESTAMP(0) 
				CHECK ((NOT is_deleted AND deleted_at IS NULL) OR (is_deleted AND deleted_at IS NOT NULL)),
	deleted_by		INTEGER REFERENCES tbl_staff (staff_id)
				CHECK ((NOT is_deleted AND deleted_by IS NULL) OR (is_deleted AND deleted_by IS NOT NULL)),
	deleted_comment		TEXT,
	sys_log			TEXT
	);


CREATE VIEW report_usage AS 
	SELECT	*
	FROM	tbl_report_usage
	WHERE	NOT is_deleted;


CREATE INDEX index_tbl_report_usage_generated_at
	ON tbl_report_usage (generated_by);
CREATE INDEX index_tbl_report_usage_generated_by
	ON tbl_report_usage (generated_at);
