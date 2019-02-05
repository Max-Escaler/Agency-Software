CREATE TABLE      tbl_duplication (
	duplication_id     SERIAL PRIMARY KEY,
	client_id     INTEGER NOT NULL , --REFERENCES tbl_client(client_id)   ,
	client_id_old     INTEGER   NOT NULL  ,
	approved BOOLEAN,
	approved_at TIMESTAMP(0) ,
	approved_by INTEGER  REFERENCES tbl_staff (staff_id),
	comment TEXT,
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

CREATE VIEW duplication AS SELECT * FROM tbl_duplication;

CREATE INDEX index_tbl_duplication_client_id_old ON tbl_duplication ( client_id_old );
CREATE INDEX index_tbl_duplication_client_id ON tbl_duplication ( client_id );