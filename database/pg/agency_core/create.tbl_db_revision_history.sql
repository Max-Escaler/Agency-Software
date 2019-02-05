CREATE TABLE tbl_db_revision_history (
	db_revision_history_id	SERIAL PRIMARY KEY,
	db_revision_code		VARCHAR(80) UNIQUE NOT NULL,
	db_revision_description TEXT,
	agency_flavor_code		VARCHAR(20) REFERENCES tbl_l_agency_flavor (agency_flavor_code),
	git_sha					VARCHAR(80),
	git_tag					VARCHAR(80),
	applied_at				TIMESTAMP(0),
	comment					TEXT,
	added_at				TIMESTAMP(0) NOT NULL DEFAULT current_timestamp,
	added_by				INTEGER NOT NULL REFERENCES tbl_staff ( staff_id ),
    changed_by				INTEGER NOT NULL  REFERENCES tbl_staff (staff_id),
    changed_at				TIMESTAMP(0)     NOT NULL DEFAULT CURRENT_TIMESTAMP,
    is_deleted				BOOLEAN NOT NULL DEFAULT FALSE,
    deleted_at				TIMESTAMP(0) CHECK ((NOT is_deleted AND deleted_at IS NULL) OR (is_deleted AND deleted_at IS NOT NULL)),
    deleted_by				INTEGER REFERENCES tbl_staff(staff_id)
							CHECK ((NOT is_deleted AND deleted_by IS NULL) OR (is_deleted AND deleted_by IS NOT NULL)),
    deleted_comment			TEXT,
	sys_log					TEXT
);

CREATE VIEW db_revision_history AS SELECT * FROM tbl_db_revision_history WHERE NOT is_deleted;
