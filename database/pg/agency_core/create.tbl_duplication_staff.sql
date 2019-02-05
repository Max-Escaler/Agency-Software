CREATE TABLE      tbl_duplication_staff (
	duplication_staff_id     SERIAL PRIMARY KEY,
	staff_id			INTEGER NOT NULL REFERENCES tbl_staff(staff_id)   ,
	staff_id_old		INTEGER   NOT NULL REFERENCES tbl_staff(staff_id),
	comment TEXT,
	approved BOOLEAN,
	approved_at TIMESTAMP(0) ,
	approved_by INTEGER  REFERENCES tbl_staff (staff_id),
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

CREATE VIEW duplication_staff AS SELECT * FROM tbl_duplication_staff;


CREATE INDEX index_tbl_duplication_staff_staff_id ON tbl_duplication_staff ( staff_id );
CREATE INDEX index_tbl_duplication_staff_staff_id_old ON tbl_duplication_staff ( staff_id_old );
