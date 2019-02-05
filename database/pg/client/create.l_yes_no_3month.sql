CREATE TABLE tbl_l_yes_no_3month 
	(
	yes_no_3month_code	VARCHAR(10) PRIMARY KEY,
	description		VARCHAR(50) NOT NULL UNIQUE,
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

INSERT INTO tbl_l_yes_no_3month VALUES 
	('YES','Yes, but not in the last 3 months',sys_user(),current_timestamp,sys_user(),current_timestamp),
	('NO','No',sys_user(),current_timestamp,sys_user(),current_timestamp),
	('YES3','Yes, last 3 months',sys_user(),current_timestamp,sys_user(),current_timestamp),
	('DK','Don''t Know',sys_user(),current_timestamp,sys_user(),current_timestamp),
	('REFUSED','Refused',sys_user(),current_timestamp,sys_user(),current_timestamp);

CREATE VIEW l_yes_no_3month AS (SELECT * FROM tbl_l_yes_no_3month WHERE NOT is_deleted);

