CREATE TABLE tbl_info_additional (
	info_additional_id 		SERIAL PRIMARY KEY,
	info_additional_type_code	VARCHAR(30) NOT NULL REFERENCES tbl_info_additional_type (info_additional_type_code),
	info_additional_value	VARCHAR,
	comment					TEXT,
	--system fields
	added_by				INTEGER NOT NULL REFERENCES tbl_staff (staff_id),
	added_at				TIMESTAMP(0)     NOT NULL DEFAULT CURRENT_TIMESTAMP,
	changed_by				INTEGER NOT NULL REFERENCES tbl_staff (staff_id),
	changed_at				TIMESTAMP(0)     NOT NULL DEFAULT CURRENT_TIMESTAMP,
	is_deleted				BOOLEAN NOT NULL DEFAULT FALSE,
	deleted_at				TIMESTAMP(0),
	deleted_by				INTEGER REFERENCES tbl_staff (staff_id),
	deleted_comment			TEXT,
	sys_log 				TEXT
);

CREATE OR REPLACE VIEW info_additional AS
SELECT * FROM tbl_info_additional WHERE NOT is_deleted;

