CREATE TABLE tbl_l_staff_assign_type (
	staff_assign_type_code VARCHAR(10) PRIMARY KEY,
	description VARCHAR(60) NOT NULL UNIQUE,
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

INSERT INTO tbl_l_staff_assign_type VALUES ('CM','Case Manager',sys_user(),current_timestamp,sys_user(),current_timestamp);
INSERT INTO tbl_l_staff_assign_type VALUES ('CM_OTHER', 'Other Case Manager',sys_user(),current_timestamp,sys_user(),current_timestamp);
INSERT INTO tbl_l_staff_assign_type VALUES ('CM_MH_PP', 'MH Case Manager (Private Pay)',sys_user(),current_timestamp,sys_user(),current_timestamp);
INSERT INTO tbl_l_staff_assign_type VALUES ('CSS', 'Clinical Support Specialist',sys_user(),current_timestamp,sys_user(),current_timestamp);
INSERT INTO tbl_l_staff_assign_type VALUES ('CM_MH_PB', 'MH Case Manager (pro bono)',sys_user(),current_timestamp,sys_user(),current_timestamp);
INSERT INTO tbl_l_staff_assign_type VALUES ('RC', 'Residential Counselor',sys_user(),current_timestamp,sys_user(),current_timestamp);
INSERT INTO tbl_l_staff_assign_type VALUES ('PAYEE', 'Payee',sys_user(),current_timestamp,sys_user(),current_timestamp);
INSERT INTO tbl_l_staff_assign_type VALUES ('MONITOR', 'Just Monitoring...',sys_user(),current_timestamp,sys_user(),current_timestamp);
INSERT INTO tbl_l_staff_assign_type VALUES ('CM_MH', 'MH Case Manager',sys_user(),current_timestamp,sys_user(),current_timestamp);
INSERT INTO tbl_l_staff_assign_type VALUES ('CM_CD', 'CD Case Manager',sys_user(),current_timestamp,sys_user(),current_timestamp);
INSERT INTO tbl_l_staff_assign_type VALUES ('CM_DD', 'DD Case Manager',sys_user(),current_timestamp,sys_user(),current_timestamp);
INSERT INTO tbl_l_staff_assign_type VALUES ('MH_VOC', 'MH Vocational Specialist',sys_user(),current_timestamp,sys_user(),current_timestamp);
INSERT INTO tbl_l_staff_assign_type VALUES ('CM_IR', 'I&R Case Manager',sys_user(),current_timestamp,sys_user(),current_timestamp);
INSERT INTO tbl_l_staff_assign_type VALUES ('SHELTER', 'Shelter Staff',sys_user(),current_timestamp,sys_user(),current_timestamp);
INSERT INTO tbl_l_staff_assign_type VALUES ('UNKNOWN', 'Unknown Assigment',sys_user(),current_timestamp,sys_user(),current_timestamp);
INSERT INTO tbl_l_staff_assign_type VALUES ('CSS_APP', 'Application Screener',sys_user(),current_timestamp,sys_user(),current_timestamp);

CREATE VIEW l_staff_assign_type AS (SELECT * FROM tbl_l_staff_assign_type WHERE NOT is_deleted);

