CREATE TABLE      tbl_l_reg_required_length (
	reg_required_length_code     VARCHAR(10)     PRIMARY KEY,
	description     TEXT,
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

INSERT INTO tbl_l_reg_required_length VALUES ('NA', 'NA',sys_user(),current_timestamp,sys_user(),current_timestamp);
INSERT INTO tbl_l_reg_required_length VALUES ('CLASS-A', 'Indefinite Registration Requirement (class A)',sys_user(),current_timestamp,sys_user(),current_timestamp);
INSERT INTO tbl_l_reg_required_length VALUES ('CLASS-B', '15-year Registration Requirement (class B)',sys_user(),current_timestamp,sys_user(),current_timestamp);
INSERT INTO tbl_l_reg_required_length VALUES ('CLASS-C', '10-year Registration Requirement (class C)',sys_user(),current_timestamp,sys_user(),current_timestamp);
INSERT INTO tbl_l_reg_required_length VALUES ('OTHER', 'Other',sys_user(),current_timestamp,sys_user(),current_timestamp);
INSERT INTO tbl_l_reg_required_length VALUES ('Unknown', 'Unknown',sys_user(),current_timestamp,sys_user(),current_timestamp);

CREATE VIEW l_reg_required_length AS (SELECT * FROM tbl_l_reg_required_length WHERE NOT is_deleted);

