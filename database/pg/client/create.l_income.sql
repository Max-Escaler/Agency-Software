CREATE TABLE tbl_l_income (
	income_code		VARCHAR(10) PRIMARY KEY,
	description		VARCHAR(30) NOT NULL UNIQUE,
	is_current		BOOLEAN NOT NULL DEFAULT TRUE,
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

INSERT INTO tbl_l_income VALUES ('UNKNOWN', 'Unknown',TRUE,sys_user(),current_timestamp,sys_user(),current_timestamp);
INSERT INTO tbl_l_income VALUES ('GA-S', 'GA-S (pregnant women)',TRUE,sys_user(),current_timestamp,sys_user(),current_timestamp);
INSERT INTO tbl_l_income VALUES ('GA-U', 'GA-U',TRUE,sys_user(),current_timestamp,sys_user(),current_timestamp);
INSERT INTO tbl_l_income VALUES ('GA-X', 'GA-X',TRUE,sys_user(),current_timestamp,sys_user(),current_timestamp);
INSERT INTO tbl_l_income VALUES ('ADATSA', 'ADATSA',FALSE,sys_user(),current_timestamp,sys_user(),current_timestamp);
INSERT INTO tbl_l_income VALUES ('PENSION', 'Pension',TRUE,sys_user(),current_timestamp,sys_user(),current_timestamp);
INSERT INTO tbl_l_income VALUES ('SSI', 'SSI',TRUE,sys_user(),current_timestamp,sys_user(),current_timestamp);
INSERT INTO tbl_l_income VALUES ('SSA', 'SSA',TRUE,sys_user(),current_timestamp,sys_user(),current_timestamp);
INSERT INTO tbl_l_income VALUES ('SSDI', 'SSDI',TRUE,sys_user(),current_timestamp,sys_user(),current_timestamp);
INSERT INTO tbl_l_income VALUES ('SSI&SSDI', 'Both SSI & SSDI',TRUE,sys_user(),current_timestamp,sys_user(),current_timestamp);
INSERT INTO tbl_l_income VALUES ('VETERAN', 'Veterans Benefits',TRUE,sys_user(),current_timestamp,sys_user(),current_timestamp);
INSERT INTO tbl_l_income VALUES ('TRIBAL', 'Tribal Income',TRUE,sys_user(),current_timestamp,sys_user(),current_timestamp);
INSERT INTO tbl_l_income VALUES ('AFDC', 'AFDC/TANF',TRUE,sys_user(),current_timestamp,sys_user(),current_timestamp);
INSERT INTO tbl_l_income VALUES ('TRAINING', 'Job Training Program',TRUE,sys_user(),current_timestamp,sys_user(),current_timestamp);
INSERT INTO tbl_l_income VALUES ('EMPLOYED', 'Employed (F/T or P/T)',TRUE,sys_user(),current_timestamp,sys_user(),current_timestamp);
INSERT INTO tbl_l_income VALUES ('UNEMPLOY', 'Unemployment Compensation',TRUE,sys_user(),current_timestamp,sys_user(),current_timestamp);
INSERT INTO tbl_l_income VALUES ('OTHER', 'Other',TRUE,sys_user(),current_timestamp,sys_user(),current_timestamp);
INSERT INTO tbl_l_income VALUES ('APPLYING', 'Applying for Pub. Assistance',TRUE,sys_user(),current_timestamp,sys_user(),current_timestamp);
INSERT INTO tbl_l_income VALUES ('NONE', 'No Income',TRUE,sys_user(),current_timestamp,sys_user(),current_timestamp);

CREATE VIEW l_income AS (SELECT * FROM tbl_l_income WHERE NOT is_deleted);
CREATE VIEW l_income_add AS SELECT * FROM l_income WHERE is_current;

