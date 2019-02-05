CREATE TABLE      tbl_bed (
	bed_id     		BIGSERIAL PRIMARY KEY,
	client_id		INTEGER NOT NULL REFERENCES tbl_client (client_id), 
	bed_group_code	VARCHAR(10) NOT NULL REFERENCES tbl_l_bed_group ( bed_group_code),
	bed_no		INTEGER, 
	bed_date		DATE NOT NULL,
	volunteer_status_code	VARCHAR(10),
	comments		TEXT,
	re_register		BOOLEAN,
	night_factor		NUMERIC(4,2),
	removed_by		INTEGER REFERENCES tbl_staff (staff_id),
	removed_at		TIMESTAMP(0),
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

CREATE VIEW bed AS SELECT * FROM tbl_bed WHERE NOT is_deleted;

CREATE INDEX index_tbl_bed_bed_date_client_id ON tbl_bed ( bed_date,client_id );
