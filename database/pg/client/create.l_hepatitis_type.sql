CREATE TABLE tbl_l_hepatitis_type 
	(
	hepatitis_type_code	VARCHAR(10)	PRIMARY KEY,
	description		VARCHAR(50)	NOT NULL UNIQUE,
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

INSERT INTO tbl_l_hepatitis_type VALUES 
	('A','A',sys_user(),current_timestamp,sys_user(),current_timestamp),
	('B','B',sys_user(),current_timestamp,sys_user(),current_timestamp),
	('C','C',sys_user(),current_timestamp,sys_user(),current_timestamp),
	('AB','A & B',sys_user(),current_timestamp,sys_user(),current_timestamp),
	('BC','B & C',sys_user(),current_timestamp,sys_user(),current_timestamp),
	('ABC','A,B & C',sys_user(),current_timestamp,sys_user(),current_timestamp),
	('UNKNOWN','Unknown',sys_user(),current_timestamp,sys_user(),current_timestamp);

CREATE VIEW l_hepatitis_type AS (SELECT * FROM tbl_l_hepatitis_type WHERE NOT is_deleted);
