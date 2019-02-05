CREATE TABLE tbl_immigrant (
	immigrant_id		SERIAL PRIMARY KEY,
	client_id			INTEGER NOT NULL REFERENCES tbl_client (client_id),
	immigrant_status_code	VARCHAR(10) NOT NULL REFERENCES tbl_l_immigrant_status (immigrant_status_code),
	comment			TEXT,
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

CREATE VIEW immigrant AS 
SELECT * FROM tbl_immigrant WHERE NOT is_deleted;
