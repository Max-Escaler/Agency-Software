CREATE TABLE tbl_l_conditional_release_extension (
	conditional_release_extension_code	VARCHAR(10) PRIMARY KEY,
	description				TEXT NOT NULL UNIQUE,
    --system fields
    added_by                        INTEGER NOT NULL REFERENCES tbl_staff (staff_id),
    added_at                        TIMESTAMP(0)     NOT NULL DEFAULT CURRENT_TIMESTAMP,
    changed_by                      INTEGER NOT NULL  REFERENCES tbl_staff (staff_id),
    changed_at                      TIMESTAMP(0)     NOT NULL DEFAULT CURRENT_TIMESTAMP,
    is_deleted                      BOOLEAN NOT NULL DEFAULT FALSE,
    deleted_at                      TIMESTAMP(0) CHECK ((NOT is_deleted AND deleted_at IS
 NULL) OR (is_deleted AND deleted_at IS NOT NULL)),
    deleted_by                      INTEGER REFERENCES tbl_staff(staff_id)
                                       CHECK ((NOT is_deleted AND deleted_by IS NULL) OR (is_deleted AND deleted_by IS NOT NULL)),
    deleted_comment         TEXT,
    sys_log                 TEXT
);

INSERT INTO tbl_l_conditional_release_extension VALUES
	('APPROVED', 'Extension requested and approved',sys_user(),current_timestamp,sys_user(),current_timestamp),
	('DENIED', 'Extension requested and denied',sys_user(),current_timestamp,sys_user(),current_timestamp),
	('NONE', 'No extension requested',sys_user(),current_timestamp,sys_user(),current_timestamp),
	('UNKNOWN', 'Unknown',sys_user(),current_timestamp,sys_user(),current_timestamp);

CREATE VIEW l_conditional_release_extension AS (SELECT * FROM tbl_l_conditional_release_extension WHERE NOT is_deleted);


