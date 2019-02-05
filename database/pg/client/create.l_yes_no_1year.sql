CREATE TABLE tbl_l_yes_no_1year (
    yes_no_1year_code     VARCHAR(10) PRIMARY KEY,
    description VARCHAR(50) NOT NULL UNIQUE,
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

CREATE VIEW l_yes_no_1year AS SELECT * FROM tbl_l_yes_no_1year WHERE NOT is_deleted;

INSERT INTO tbl_l_yes_no_1year VALUES ('YES_1YEAR','Yes, in last year',sys_user(),current_timestamp,sys_user(),current_timestamp);
INSERT INTO tbl_l_yes_no_1year VALUES ('YES_NO1YR','Yes, not in last year',sys_user(),current_timestamp,sys_user(),current_timestamp);
INSERT INTO tbl_l_yes_no_1year VALUES ('NO','No',sys_user(),current_timestamp,sys_user(),current_timestamp);
/*
INSERT INTO tbl_l_yes_no_1year VALUES ('UNKNOWN','Unknown',sys_user(),current_timestamp,sys_user(),current_timestamp);
INSERT INTO tbl_l_yes_no_1year VALUES ('NOTASKED','Not Asked',sys_user(),current_timestamp,sys_user(),current_timestamp);
INSERT INTO tbl_l_yes_no_1year VALUES ('REFUSED','Refused',sys_user(),current_timestamp,sys_user(),current_timestamp);
*/

