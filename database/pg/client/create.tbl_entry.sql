CREATE TABLE tbl_entry (
	entry_id			BIGSERIAL PRIMARY KEY,
	entered_at			TIMESTAMP NOT NULL,
	exited_at			TIMESTAMP,
	client_id			INTEGER NOT NULL REFERENCES tbl_client (client_id),
	issue_no			INTEGER,
	source			CHAR(1),
	entry_location_code	VARCHAR(10) NOT NULL REFERENCES tbl_l_entry_location(entry_location_code),
	--system fields
	added_by			INTEGER NOT NULL REFERENCES tbl_staff (staff_id) DEFAULT sys_user(),
	added_at			TIMESTAMP(0)     NOT NULL DEFAULT CURRENT_TIMESTAMP,
	changed_by			INTEGER NOT NULL  REFERENCES tbl_staff (staff_id) DEFAULT sys_user(),
	changed_at			TIMESTAMP(0)     NOT NULL DEFAULT CURRENT_TIMESTAMP,
	is_deleted			BOOLEAN NOT NULL DEFAULT FALSE,
	deleted_at			TIMESTAMP(0) CHECK ((NOT is_deleted AND deleted_at IS NULL) OR (is_deleted AND deleted_at IS NOT NULL)),
	deleted_by			INTEGER REFERENCES tbl_staff(staff_id)
						  CHECK ((NOT is_deleted AND deleted_by IS NULL) OR (is_deleted AND deleted_by IS NOT NULL)),
	deleted_comment		TEXT,
	sys_log			TEXT

);
CREATE VIEW entry AS SELECT * FROM tbl_entry WHERE NOT is_deleted;

CREATE INDEX index_tbl_entry_client_id ON tbl_entry ( client_id ) WHERE NOT is_deleted;
CREATE INDEX index_tbl_entry_is_deleted ON tbl_entry ( is_deleted );
CREATE INDEX index_tbl_entry_entered_at ON tbl_entry ( entered_at ) WHERE NOT is_deleted;
CREATE INDEX index_tbl_entry_client_id_entered_at ON tbl_entry ( client_id,entered_at ) WHERE NOT is_deleted;
CREATE INDEX index_tbl_entry_entry_location_code ON tbl_entry ( entry_location_code ) WHERE NOT is_deleted;
