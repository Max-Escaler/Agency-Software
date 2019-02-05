CREATE TABLE tbl_data_dictionary (
	data_dictionary_id	SERIAL PRIMARY KEY,
	name_table			VARCHAR(80),
	name_field			VARCHAR(80),
	comment_general		TEXT,
	comment_reporting	TEXT,
	comment_data_entry	TEXT,
	comment_other		TEXT,
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

/* FIXME:  Add table or field not NULL, and UNIQUE, here */

CREATE VIEW data_dictionary AS (SELECT * FROM tbl_data_dictionary WHERE NOT is_deleted);

