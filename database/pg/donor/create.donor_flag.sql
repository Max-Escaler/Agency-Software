CREATE TABLE tbl_donor_flag (

	donor_flag_id	SERIAL PRIMARY KEY,
	donor_id		INTEGER NOT NULL REFERENCES tbl_donor (donor_id),
	donor_flag_type_code	VARCHAR(10) NOT NULL REFERENCES tbl_l_donor_flag_type (donor_flag_type_code),
	donor_flag_value VARCHAR(80),
	added_by     INTEGER NOT NULL REFERENCES tbl_staff (staff_id),
	added_at     TIMESTAMP(0)     NOT NULL DEFAULT CURRENT_TIMESTAMP,
	changed_by     INTEGER NOT NULL  REFERENCES tbl_staff (staff_id),
	changed_at     TIMESTAMP(0)     NOT NULL DEFAULT CURRENT_TIMESTAMP,
	is_deleted    BOOLEAN NOT NULL DEFAULT FALSE,
	deleted_at   TIMESTAMP(0),
	deleted_by   INTEGER REFERENCES tbl_staff(staff_id),
	deleted_comment TEXT,
	sys_log TEXT

);

CREATE VIEW donor_flag AS SELECT * FROM tbl_donor_flag WHERE NOT is_deleted;
