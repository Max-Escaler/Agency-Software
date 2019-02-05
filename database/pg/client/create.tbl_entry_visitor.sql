CREATE TABLE tbl_entry_visitor (
	entry_visitor_id			BIGSERIAL PRIMARY KEY,
	entered_at			TIMESTAMP NOT NULL,
	--hide client_id, but could be used later to link visitors who become members
	client_id			INTEGER REFERENCES tbl_client (client_id),
	issue_no			INTEGER,
	source			CHAR(1),
	entry_location_code	VARCHAR(10) NOT NULL REFERENCES tbl_l_entry_location(entry_location_code),
	visitor_name		VARCHAR(80) NOT NULL,
	visit_purpose		TEXT,
	visiting_who		INTEGER REFERENCES tbl_staff (staff_id),
	visit_type_code		VARCHAR(10) REFERENCES tbl_l_visit_type (visit_type_code),
	comment				TEXT,
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
CREATE VIEW entry_visitor AS SELECT * FROM tbl_entry_visitor WHERE NOT is_deleted;

--CREATE INDEX index_tbl_entry_visitor_client_id ON tbl_entry_visitor ( client_id ) WHERE NOT is_deleted;

CREATE INDEX index_tbl_entry_visitor_is_deleted ON tbl_entry_visitor ( is_deleted );
CREATE INDEX index_tbl_entry_visitor_entered_at ON tbl_entry_visitor ( entered_at ) WHERE NOT is_deleted;
--CREATE INDEX index_tbl_entry_visitor_client_id_entered_at ON tbl_entry_visitor ( client_id,entered_at ) WHERE NOT is_deleted;
CREATE INDEX index_tbl_entry_visitor_entry_location_code ON tbl_entry_visitor ( entry_location_code ) WHERE NOT is_deleted;
