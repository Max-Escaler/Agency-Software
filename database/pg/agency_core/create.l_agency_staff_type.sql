CREATE TABLE tbl_l_agency_staff_type(
	agency_staff_type_code VARCHAR(10) NOT NULL PRIMARY KEY,
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

CREATE VIEW l_agency_staff_type AS SELECT * FROM tbl_l_agency_staff_type WHERE NOT is_deleted;

INSERT INTO tbl_l_agency_staff_type VALUES ('PAID', 'Paid',sys_user(),current_timestamp,sys_user(),current_timestamp);
INSERT INTO tbl_l_agency_staff_type VALUES ('VOLUNTEER', 'Volunteer',sys_user(),current_timestamp,sys_user(),current_timestamp);
INSERT INTO tbl_l_agency_staff_type VALUES ('INTERN', 'Intern',sys_user(),current_timestamp,sys_user(),current_timestamp);
INSERT INTO tbl_l_agency_staff_type VALUES ('RESIDENT', 'Resident',sys_user(),current_timestamp,sys_user(),current_timestamp);
INSERT INTO tbl_l_agency_staff_type VALUES ('SITED', 'Sited',sys_user(),current_timestamp,sys_user(),current_timestamp);
