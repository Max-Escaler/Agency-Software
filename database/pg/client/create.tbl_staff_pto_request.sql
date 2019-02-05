CREATE TABLE tbl_staff_pto_request (
	staff_pto_request_id	SERIAL PRIMARY KEY,
	staff_id			INTEGER NOT NULL REFERENCES tbl_staff ( staff_id ),
	pto_start			TIMESTAMP(0) NOT NULL CHECK (pto_start::text ~ '(00|15|30|45|(23:59)):00$'),
	pto_end			TIMESTAMP(0) NOT NULL CHECK (pto_end::text ~ '(00|15|30|45|(23:59)):00$' AND pto_end > pto_start),
	comments			TEXT,

	has_accrued_pto		BOOLEAN,
	immediate_supervisor_recommendation_code	VARCHAR(10) REFERENCES tbl_l_pto_request_status ( pto_request_status_code ),
	immediate_supervisor_recommendation_date	DATE CHECK 
		((immediate_supervisor_recommendation_date IS NULL AND immediate_supervisor_recommendation_code IS NULL)
			OR (immediate_supervisor_recommendation_date IS NOT NULL AND immediate_supervisor_recommendation_code IS NOT NULL)),
	immediate_supervisor_comments	TEXT,

	program_supervisor_recommendation_code	VARCHAR(10) REFERENCES tbl_l_pto_request_status ( pto_request_status_code ),
	program_supervisor_recommendation_date	DATE CHECK 
		((program_supervisor_recommendation_date IS NULL AND program_supervisor_recommendation_code IS NULL)
			OR (program_supervisor_recommendation_date IS NOT NULL AND program_supervisor_recommendation_code IS NOT NULL)),
	program_supervisor_comments TEXT,

	--system fields
	added_by			INTEGER NOT NULL REFERENCES tbl_staff (staff_id),
	added_at			TIMESTAMP(0)     NOT NULL DEFAULT CURRENT_TIMESTAMP,
	changed_by			INTEGER NOT NULL  REFERENCES tbl_staff (staff_id),
	changed_at			TIMESTAMP(0)     NOT NULL DEFAULT CURRENT_TIMESTAMP,
	is_deleted			BOOLEAN NOT NULL DEFAULT FALSE,
	deleted_at			TIMESTAMP(0),
	deleted_by			INTEGER REFERENCES tbl_staff(staff_id),
	deleted_comment		TEXT,
	sys_log			TEXT
);

CREATE VIEW staff_pto_request AS 
SELECT * 
FROM tbl_staff_pto_request WHERE NOT is_deleted;
