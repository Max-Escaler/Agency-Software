CREATE TABLE tbl_l_grade_level (
    grade_level_code		VARCHAR(10) PRIMARY KEY,
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

INSERT INTO tbl_l_grade_level VALUES
	('NOTINSCHOO','Not in school',sys_user(),current_timestamp,sys_user(),current_timestamp);
INSERT INTO tbl_l_grade_level VALUES
	('NURSERY','Nursery school',sys_user(),current_timestamp,sys_user(),current_timestamp);
INSERT INTO tbl_l_grade_level VALUES
	('KINDERGART','Kindergarten',sys_user(),current_timestamp,sys_user(),current_timestamp);
INSERT INTO tbl_l_grade_level VALUES
	('1STGRADE','1st grade',sys_user(),current_timestamp,sys_user(),current_timestamp);
INSERT INTO tbl_l_grade_level VALUES
	('2NDGRADE','2nd grade',sys_user(),current_timestamp,sys_user(),current_timestamp);
INSERT INTO tbl_l_grade_level VALUES
	('3RDGRADE','3rd grade',sys_user(),current_timestamp,sys_user(),current_timestamp);
INSERT INTO tbl_l_grade_level VALUES
	('4THGRADE','4th grade',sys_user(),current_timestamp,sys_user(),current_timestamp);
INSERT INTO tbl_l_grade_level VALUES
	('5THGRADE','5th grade',sys_user(),current_timestamp,sys_user(),current_timestamp);
INSERT INTO tbl_l_grade_level VALUES
	('6THGRADE','6th grade',sys_user(),current_timestamp,sys_user(),current_timestamp);
INSERT INTO tbl_l_grade_level VALUES
	('7THGRADE','7th grade',sys_user(),current_timestamp,sys_user(),current_timestamp);
INSERT INTO tbl_l_grade_level VALUES
	('8THGRADE','8th grade',sys_user(),current_timestamp,sys_user(),current_timestamp);
INSERT INTO tbl_l_grade_level VALUES
	('9THGRADE','9th grade',sys_user(),current_timestamp,sys_user(),current_timestamp);
INSERT INTO tbl_l_grade_level VALUES
	('10THGRADE','10th grade',sys_user(),current_timestamp,sys_user(),current_timestamp);
INSERT INTO tbl_l_grade_level VALUES
	('11THGRADE','11th grade',sys_user(),current_timestamp,sys_user(),current_timestamp);
INSERT INTO tbl_l_grade_level VALUES
	('12THGRADE','12th grade',sys_user(),current_timestamp,sys_user(),current_timestamp);
INSERT INTO tbl_l_grade_level VALUES
	('HIGHSCHOOL','High school diploma',sys_user(),current_timestamp,sys_user(),current_timestamp);
INSERT INTO tbl_l_grade_level VALUES
	('VOCATIONAL','Vocational Training',sys_user(),current_timestamp,sys_user(),current_timestamp);
INSERT INTO tbl_l_grade_level VALUES
	('GED','GED',sys_user(),current_timestamp,sys_user(),current_timestamp);	
INSERT INTO tbl_l_grade_level VALUES
	('FRESHMAN','College Freshman',sys_user(),current_timestamp,sys_user(),current_timestamp);
INSERT INTO tbl_l_grade_level VALUES
	('SOPHOMORE','College Sophomore',sys_user(),current_timestamp,sys_user(),current_timestamp);
INSERT INTO tbl_l_grade_level VALUES
	('JUNIOR','College Junior',sys_user(),current_timestamp,sys_user(),current_timestamp);
INSERT INTO tbl_l_grade_level VALUES
	('SENIOR','College Senior',sys_user(),current_timestamp,sys_user(),current_timestamp);
INSERT INTO tbl_l_grade_level VALUES
	('MASTERS','Masters degree',sys_user(),current_timestamp,sys_user(),current_timestamp);
INSERT INTO tbl_l_grade_level VALUES
	('DOCTORATE','Doctorate',sys_user(),current_timestamp,sys_user(),current_timestamp);
INSERT INTO tbl_l_grade_level VALUES
	('POSTSECOND','Post-secondary',sys_user(),current_timestamp,sys_user(),current_timestamp);
INSERT INTO tbl_l_grade_level VALUES
	('OTHERGRAD','Other graduate or professional',sys_user(),current_timestamp,sys_user(),current_timestamp);

CREATE VIEW l_grade_level AS (SELECT * FROM tbl_l_grade_level WHERE NOT is_deleted);

