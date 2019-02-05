CREATE TABLE tbl_l_gender (
	gender_code	VARCHAR(10) PRIMARY KEY,
	description	TEXT NOT NULL UNIQUE,
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

CREATE VIEW l_gender AS SELECT * FROM tbl_l_gender WHERE NOT is_deleted;

INSERT INTO tbl_l_gender VALUES ('FEMALE', 'Female',sys_user(),current_timestamp,sys_user(),current_timestamp);
INSERT INTO tbl_l_gender VALUES ('MALE', 'Male',sys_user(),current_timestamp,sys_user(),current_timestamp);
INSERT INTO tbl_l_gender VALUES ('TR_M', 'Transgender (Female to Male)',sys_user(),current_timestamp,sys_user(),current_timestamp);
INSERT INTO tbl_l_gender VALUES ('TR_F', 'Transgender (Male to Female)',sys_user(),current_timestamp,sys_user(),current_timestamp);
INSERT INTO tbl_l_gender VALUES ('TR_MF', 'Transgender (F to M), CONSIDER FEMALE',sys_user(),current_timestamp,sys_user(),current_timestamp);
INSERT INTO tbl_l_gender VALUES ('TR_FM', 'Transgender (M to F), CONSIDER MALE',sys_user(),current_timestamp,sys_user(),current_timestamp);
INSERT INTO tbl_l_gender VALUES ('TR_UNKNOWN', 'Transgender (Direction Unknown)',sys_user(),current_timestamp,sys_user(),current_timestamp);
INSERT INTO tbl_l_gender VALUES ('UNKNOWN', 'Unknown',sys_user(),current_timestamp,sys_user(),current_timestamp);

/*
--The old codes:
INSERT INTO tbl_l_gender VALUES ('1', 'Female',sys_user(),current_timestamp,sys_user(),current_timestamp);
INSERT INTO tbl_l_gender VALUES ('2', 'Male',sys_user(),current_timestamp,sys_user(),current_timestamp);
INSERT INTO tbl_l_gender VALUES ('3', 'Transgender (Female to Male)',sys_user(),current_timestamp,sys_user(),current_timestamp);
INSERT INTO tbl_l_gender VALUES ('4', 'Transgender (Male to Female)',sys_user(),current_timestamp,sys_user(),current_timestamp);
INSERT INTO tbl_l_gender VALUES ('6', 'Transgender (F to M), CONSIDER FEMALE',sys_user(),current_timestamp,sys_user(),current_timestamp);
INSERT INTO tbl_l_gender VALUES ('7', 'Transgender (M to F), CONSIDER MALE',sys_user(),current_timestamp,sys_user(),current_timestamp);
INSERT INTO tbl_l_gender VALUES ('5', 'Transgender (Direction Unknown)',sys_user(),current_timestamp,sys_user(),current_timestamp);
INSERT INTO tbl_l_gender VALUES ('8', 'Unknown',sys_user(),current_timestamp,sys_user(),current_timestamp);
*/
