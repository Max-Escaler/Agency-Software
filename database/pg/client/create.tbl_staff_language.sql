CREATE TABLE tbl_staff_language (
	staff_language_id			SERIAL PRIMARY KEY,
	staff_id				INTEGER NOT NULL REFERENCES tbl_staff ( staff_id ),
	language_code			VARCHAR(10) NOT NULL REFERENCES tbl_l_language ( language_code ),
	language_code_other		TEXT CHECK ((language_code IN ('81','82','83','85','87','99') AND language_code_other IS NOT NULL)
									OR language_code NOT IN ('81','82','83','85','87','99')),
	language_proficiency_code	VARCHAR(10) NOT NULL REFERENCES tbl_l_language_proficiency ( language_proficiency_code ),
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

CREATE VIEW staff_language AS SELECT * FROM tbl_staff_language WHERE NOT is_deleted;

CREATE INDEX index_tbl_staff_language_staff_id ON tbl_staff_language ( staff_id );
