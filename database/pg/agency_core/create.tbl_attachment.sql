CREATE TABLE tbl_attachment (
	attachment_id		SERIAL PRIMARY KEY,
	attachment_size		INTEGER NOT NULL,
	filename_original	VARCHAR,
	md5sum				VARCHAR(32) NOT NULL,
	extension			VARCHAR,
	mime_type			VARCHAR,
	--system fields
	added_by                        INTEGER NOT NULL REFERENCES tbl_staff (staff_id),
	added_at                        TIMESTAMP(0)     NOT NULL DEFAULT CURRENT_TIMESTAMP,
	changed_by                      INTEGER NOT NULL  REFERENCES tbl_staff (staff_id),
	changed_at                      TIMESTAMP(0)     NOT NULL DEFAULT CURRENT_TIMESTAMP,
	is_deleted                      BOOLEAN NOT NULL DEFAULT FALSE,
	deleted_at                      TIMESTAMP(0) CHECK ((NOT is_deleted AND deleted_at IS NULL) OR (is_deleted AND deleted_at IS NOT NULL)),
	deleted_by                      INTEGER REFERENCES tbl_staff(staff_id)
		CHECK ((NOT is_deleted AND deleted_by IS NULL) OR (is_deleted AND deleted_by IS NOT NULL)),
	deleted_comment         TEXT,
	sys_log                 TEXT
);

CREATE VIEW attachment AS (SELECT * FROM tbl_attachment WHERE NOT is_deleted);
