CREATE TABLE tbl_l_sh_school_status (
    sh_school_status_code		VARCHAR(10) PRIMARY KEY,
    description			VARCHAR(20) NOT NULL UNIQUE,
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

INSERT INTO tbl_l_sh_school_status VALUES
	('YES','Yes',sys_user(),current_timestamp,sys_user(),current_timestamp),
	('NO','No',sys_user(),current_timestamp,sys_user(),current_timestamp),
	('NAVY','Navy',sys_user(),current_timestamp,sys_user(),current_timestamp),
	('MARINES','Marines',sys_user(),current_timestamp,sys_user(),current_timestamp),
	('OTHER','Other',sys_user(),current_timestamp,sys_user(),current_timestamp);

CREATE VIEW l_sh_school_status AS (SELECT * FROM tbl_l_sh_school_status WHERE NOT is_deleted);

