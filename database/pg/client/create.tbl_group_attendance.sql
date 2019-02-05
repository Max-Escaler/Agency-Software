CREATE TABLE      tbl_group_attendance (
	group_attendance_id   SERIAL PRIMARY KEY,
	group_attendance_at  TIMESTAMP NOT NULL ,
	client_id     INTEGER  REFERENCES tbl_client (client_id),
	group_type_code VARCHAR(10) NOT NULL REFERENCES tbl_l_group_type ( group_type_code),
	source     CHAR(1)     ,
	comment		TEXT	,
	added_by     INTEGER NOT NULL REFERENCES tbl_staff (staff_id),
	added_at     TIMESTAMP(0)     NOT NULL DEFAULT CURRENT_TIMESTAMP,
	changed_by     INTEGER NOT NULL  REFERENCES tbl_staff (staff_id),
	changed_at     TIMESTAMP(0)     NOT NULL DEFAULT CURRENT_TIMESTAMP,
	is_deleted    BOOLEAN NOT NULL DEFAULT FALSE,
	deleted_at   TIMESTAMP(0),
	deleted_by   INTEGER REFERENCES tbl_staff (staff_id),
	deleted_comment TEXT,
	sys_log TEXT
);

CREATE VIEW group_attendance AS SELECT * FROM tbl_group_attendance WHERE NOT is_deleted;
GRANT SELECT ON group_attendance TO agency_safe;
