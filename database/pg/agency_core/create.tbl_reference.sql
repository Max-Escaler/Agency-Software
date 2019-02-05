CREATE TABLE tbl_reference (
	reference_id			SERIAL PRIMARY KEY,
	from_table				NAME NOT NULL ,--REFERENCES pg_catalog.pg_class(relname),
	from_id_field			NAME NOT NULL,
	from_id					integer NOT NULL,
	to_table				NAME NOT NULL ,--REFERENCES pg_catalog.pg_class(relname),
	to_id_field				NAME NOT NULL,
	to_id					integer NOT NULL,
--system fields
	added_at				TIMESTAMP(0) NOT NULL DEFAULT CURRENT_TIMESTAMP,
	added_by				INTEGER NOT NULL REFERENCES tbl_staff (staff_id),
	changed_at				TIMESTAMP(0) NOT NULL DEFAULT CURRENT_TIMESTAMP,
	changed_by				INTEGER NOT NULL REFERENCES tbl_staff (staff_id),
	is_deleted				BOOLEAN NOT NULL DEFAULT FALSE,
	deleted_at				TIMESTAMP(0),
	deleted_by				INTEGER REFERENCES tbl_staff (staff_id),
	deleted_comment			TEXT,
	sys_log				TEXT
);

CREATE VIEW reference AS SELECT * FROM tbl_reference WHERE NOT is_deleted;

CREATE UNIQUE INDEX unique_index_tbl_reference ON tbl_reference (from_table,from_id_field,from_id,to_table,to_id_field,to_id);
CREATE INDEX index_tbl_reference_to_table_id ON tbl_reference ( to_table, to_id );
CREATE INDEX index_tbl_reference_is_deleted ON tbl_reference ( is_deleted );


