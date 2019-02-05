CREATE TABLE tbl_l_highest_education (
    highest_education_code		VARCHAR(10) PRIMARY KEY,
    description			VARCHAR(30) NOT NULL UNIQUE,
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

INSERT INTO tbl_l_highest_education VALUES
	('NOSCHOOL','No schooling',sys_user(),current_timestamp,sys_user(),current_timestamp),
	('NURSTO4TH','Nursery school to 4th grade',sys_user(),current_timestamp,sys_user(),current_timestamp),
	('5THGRADE','5th grade',sys_user(),current_timestamp,sys_user(),current_timestamp),
	('6THGRADE','6th grade',sys_user(),current_timestamp,sys_user(),current_timestamp),
	('7THGRADE','7th grade',sys_user(),current_timestamp,sys_user(),current_timestamp),
	('8THGRADE','8th grade',sys_user(),current_timestamp,sys_user(),current_timestamp),
	('9THGRADE','9th grade',sys_user(),current_timestamp,sys_user(),current_timestamp),
	('10THGRADE','10th grade',sys_user(),current_timestamp,sys_user(),current_timestamp),
	('11THGRADE','11th grade',sys_user(),current_timestamp,sys_user(),current_timestamp),
	('12THGRADE','12th grade',sys_user(),current_timestamp,sys_user(),current_timestamp),
	('HIGHSCHOOL','High school diploma',sys_user(),current_timestamp,sys_user(),current_timestamp),
	('GED','GED',sys_user(),current_timestamp,sys_user(),current_timestamp),	
	('POSTSECOND','Post-secondary',sys_user(),current_timestamp,sys_user(),current_timestamp),
	('ASSOCIATES','Associates degree',sys_user(),current_timestamp,sys_user(),current_timestamp),
	('BACHELORS','Bachelors degree',sys_user(),current_timestamp,sys_user(),current_timestamp),
	('MASTERS','Masters degree',sys_user(),current_timestamp,sys_user(),current_timestamp),
	('DOCTORATE','Doctorate',sys_user(),current_timestamp,sys_user(),current_timestamp),
	('OTHERGRAD','Other graduate or professional',sys_user(),current_timestamp,sys_user(),current_timestamp);

CREATE VIEW l_highest_education AS (SELECT * FROM tbl_l_highest_education WHERE NOT is_deleted);

