CREATE TABLE tbl_client_key_assign (
	client_key_assign_id SERIAL PRIMARY KEY,
	client_id		INTEGER NOT NULL REFERENCES tbl_client (client_id),
	agency_key_code	VARCHAR(10) NOT NULL REFERENCES tbl_l_agency_key (agency_key_code),
	key_serial_number VARCHAR(10),
	assigned_by		INTEGER NOT NULL REFERENCES tbl_staff (staff_id),
	client_key_assign_date	DATE NOT NULL,
	client_key_assign_date_end DATE,
	key_disposition_code VARCHAR(10) REFERENCES tbl_l_key_disposition ( key_disposition_code),
	comments		TEXT,
	--system fields
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

CREATE VIEW client_key_assign AS
SELECT * FROM tbl_client_key_assign WHERE NOT is_deleted;
