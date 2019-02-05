CREATE TABLE      tbl_disability_confidential (
	disability_confidential_id		SERIAL PRIMARY KEY NOT NULL,
	client_id			     		INTEGER NOT NULL,
	aids_diag_date				DATE,
	aids_disabled_date			DATE,
	hiv_pos_date				DATE,
	comment					TEXT,
	--sys fields
	added_at			TIMESTAMP(0) NOT NULL DEFAULT current_timestamp,
	added_by			INTEGER NOT NULL REFERENCES tbl_staff (staff_id),
	changed_at			TIMESTAMP(0) NOT NULL DEFAULT current_timestamp,
	changed_by			INTEGER NOT NULL REFERENCES tbl_staff (staff_id),
	is_deleted			BOOLEAN NOT NULL DEFAULT FALSE,
	deleted_at			TIMESTAMP(0),
	deleted_by			INTEGER REFERENCES tbl_staff (staff_id),
	deleted_comment		TEXT,
	sys_log			TEXT
);

CREATE OR REPLACE VIEW disability_confidential AS
SELECT * FROM tbl_disability_confidential WHERE NOT is_deleted;
