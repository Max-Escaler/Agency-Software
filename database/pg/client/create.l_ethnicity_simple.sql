CREATE TABLE tbl_l_ethnicity_simple (
    ethnicity_simple_code   VARCHAR(10) PRIMARY KEY,
    description TEXT NOT NULL UNIQUE,
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

INSERT INTO tbl_l_ethnicity_simple VALUES
	('UNKNOWN', 'Unknown',sys_user(),current_timestamp,sys_user(),current_timestamp);
INSERT INTO tbl_l_ethnicity_simple VALUES
	('ASIAN_PAC', 'Asian/Pacific Islander',sys_user(),current_timestamp,sys_user(),current_timestamp);
INSERT INTO tbl_l_ethnicity_simple VALUES
	('AFRICAN', 'African American/African Descent',sys_user(),current_timestamp,sys_user(),current_timestamp);
INSERT INTO tbl_l_ethnicity_simple VALUES
	('CAUCASIAN', 'Caucasian',sys_user(),current_timestamp,sys_user(),current_timestamp);
INSERT INTO tbl_l_ethnicity_simple VALUES
	('NATIVE_AM', 'Native American/Alaskan',sys_user(),current_timestamp,sys_user(),current_timestamp);
INSERT INTO tbl_l_ethnicity_simple VALUES
	('MULTI', 'Multi-Racial',sys_user(),current_timestamp,sys_user(),current_timestamp);
INSERT INTO tbl_l_ethnicity_simple VALUES
	('OTHER', 'Other',sys_user(),current_timestamp,sys_user(),current_timestamp);
INSERT INTO tbl_l_ethnicity_simple VALUES
	('LATINO', 'Latino',sys_user(),current_timestamp,sys_user(),current_timestamp);

CREATE VIEW l_ethnicity_simple AS (SELECT * FROM tbl_l_ethnicity_simple WHERE NOT is_deleted);
