CREATE TABLE tbl_sh_additional_data (
	sh_additional_data_id			SERIAL PRIMARY KEY,
	client_id				INTEGER NOT NULL REFERENCES tbl_client ( client_id ) UNIQUE,
	sh_school_status_code			VARCHAR(10) NOT NULL REFERENCES tbl_l_sh_school_status ( sh_school_status_code ),
	highest_education_code			VARCHAR(10) NOT NULL REFERENCES tbl_l_highest_education ( highest_education_code),
	immigrant_status_code			VARCHAR(10) NOT NULL REFERENCES tbl_l_yes_no_client ( yes_no_client_code),
	comments				TEXT,
	--system fields
	added_by				INTEGER NOT NULL REFERENCES tbl_staff (staff_id),
	added_at				TIMESTAMP(0)     NOT NULL DEFAULT CURRENT_TIMESTAMP,
	changed_by				INTEGER NOT NULL  REFERENCES tbl_staff (staff_id),
	changed_at				TIMESTAMP(0)     NOT NULL DEFAULT CURRENT_TIMESTAMP,
	is_deleted				BOOLEAN NOT NULL DEFAULT FALSE,
	deleted_at				TIMESTAMP(0),
	deleted_by				INTEGER REFERENCES tbl_staff(staff_id),
	deleted_comment				TEXT,
	sys_log					TEXT
);

CREATE VIEW sh_additional_data AS SELECT * FROM tbl_sh_additional_data WHERE NOT is_deleted;
