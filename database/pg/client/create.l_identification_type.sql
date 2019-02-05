CREATE TABLE tbl_l_identification_type (
    identification_type_code VARCHAR(10) PRIMARY KEY,
    description VARCHAR(30) NOT NULL UNIQUE,
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

CREATE VIEW l_identification_type AS SELECT * FROM tbl_l_identification_type WHERE NOT is_deleted;

INSERT INTO tbl_l_identification_type  VALUES ('DRIVER', E'Driver\'s License',sys_user(),current_timestamp,sys_user(),current_timestamp);
INSERT INTO tbl_l_identification_type  VALUES ('STATE_ID', 'State Identification',sys_user(),current_timestamp,sys_user(),current_timestamp);
INSERT INTO tbl_l_identification_type  VALUES ('US_PASS', 'United State Passport',sys_user(),current_timestamp,sys_user(),current_timestamp);
INSERT INTO tbl_l_identification_type  VALUES ('OTHER', 'Other Identification Type',sys_user(),current_timestamp,sys_user(),current_timestamp);
-- Do DL need to be separate?

