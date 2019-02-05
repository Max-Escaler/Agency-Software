CREATE TABLE tbl_education_level 
	(
	education_level_id		SERIAL PRIMARY KEY,
	client_id			INTEGER NOT NULL REFERENCES tbl_client (client_id),
	education_level_date		DATE NOT NULL,
	grade_level_current_code	VARCHAR(10) NOT NULL REFERENCES tbl_l_grade_level (grade_level_code),
	highest_education_code		VARCHAR(10) NOT NULL REFERENCES tbl_l_highest_education (highest_education_code),
	comment				TEXT,
	--system fields
	added_by			INTEGER NOT NULL REFERENCES tbl_staff (staff_id),
	added_at			TIMESTAMP(0)     NOT NULL DEFAULT CURRENT_TIMESTAMP,
	changed_by			INTEGER NOT NULL  REFERENCES tbl_staff (staff_id),
	changed_at			TIMESTAMP(0)     NOT NULL DEFAULT CURRENT_TIMESTAMP,
	is_deleted			BOOLEAN NOT NULL DEFAULT FALSE,
	deleted_at			TIMESTAMP(0) 
					CHECK ((NOT is_deleted AND deleted_at IS NULL) OR (is_deleted AND deleted_at IS NOT NULL)),
	deleted_by			INTEGER REFERENCES tbl_staff(staff_id)
					CHECK ((NOT is_deleted AND deleted_by IS NULL) OR (is_deleted AND deleted_by IS NOT NULL)),
	deleted_comment			TEXT,
	sys_log				TEXT
	);

--adding view creation
CREATE OR REPLACE VIEW education_level AS SELECT * FROM tbl_education_level WHERE NOT is_deleted;

