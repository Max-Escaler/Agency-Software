CREATE TABLE tbl_engine_label (
	engine_label_id		SERIAL PRIMARY KEY,
	table_name			VARCHAR NOT NULL,
	field_name			VARCHAR NOT NULL,
	label				VARCHAR NOT NULL,
	label_short			VARCHAR,
	actions				VARCHAR[],
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

CREATE VIEW engine_label AS SELECT * FROM tbl_engine_label WHERE NOT is_deleted;

CREATE INDEX index_tbl_engine_label_is_deleted ON tbl_engine_label ( is_deleted );
CREATE INDEX index_tbl_engine_label_table_name ON tbl_engine_label ( table_name );
CREATE INDEX index_tbl_engine_label_field_name ON tbl_engine_label ( field_name );

