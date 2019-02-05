CREATE TABLE tbl_staff_termination (
	staff_termination_id		SERIAL PRIMARY KEY,
	staff_id				INTEGER NOT NULL REFERENCES tbl_staff ( staff_id ),
	termination_date			DATE NOT NULL,
	email_forwarding			TEXT,
	disposition_of_email		TEXT,
	disposition_of_files		TEXT,
	comments				TEXT,
	staff_termination_status_code	VARCHAR(10) REFERENCES tbl_l_staff_request_status ( staff_request_status_code ),
	--system fields
	added_by			INTEGER NOT NULL REFERENCES tbl_staff (staff_id),
	added_at			TIMESTAMP(0)     NOT NULL DEFAULT CURRENT_TIMESTAMP,
	changed_by			INTEGER NOT NULL  REFERENCES tbl_staff (staff_id),
	changed_at			TIMESTAMP(0)     NOT NULL DEFAULT CURRENT_TIMESTAMP,
	is_deleted			BOOLEAN NOT NULL DEFAULT FALSE,
	deleted_at			TIMESTAMP(0) CHECK ((NOT is_deleted AND deleted_at IS NULL) OR (is_deleted AND deleted_at IS NOT NULL)),
	deleted_by			INTEGER REFERENCES tbl_staff(staff_id)
						  CHECK ((NOT is_deleted AND deleted_by IS NULL) OR (is_deleted AND deleted_by IS NOT NULL)),
	deleted_comment		TEXT,
	sys_log			TEXT
);

CREATE VIEW staff_termination AS SELECT * FROM tbl_staff_termination WHERE NOT is_deleted;
