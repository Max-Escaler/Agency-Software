CREATE TABLE tbl_l_agency_facility (
	agency_facility_code VARCHAR(10) NOT NULL PRIMARY KEY,
	description TEXT NOT NULL UNIQUE,
	short_description VARCHAR(15) NOT NULL UNIQUE,
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

CREATE VIEW l_agency_facility AS SELECT * FROM tbl_l_agency_facility WHERE NOT is_deleted;

INSERT INTO tbl_l_agency_facility VALUES ('ADMIN','Administrative Offices','Admin',sys_user(),current_timestamp,sys_user(),current_timestamp);
INSERT INTO tbl_l_agency_facility VALUES ('CLINICAL','Clinical Facility','Clinical',sys_user(),current_timestamp,sys_user(),current_timestamp);
INSERT INTO tbl_l_agency_facility VALUES ('DROPIN','Drop In Space','Dropin',sys_user(),current_timestamp,sys_user(),current_timestamp);
INSERT INTO tbl_l_agency_facility VALUES ('HOUSINGA','Housing Project A','Housing A',sys_user(),current_timestamp,sys_user(),current_timestamp);
INSERT INTO tbl_l_agency_facility VALUES ('HOUSINGB','Housing Project B','Housing B',sys_user(),current_timestamp,sys_user(),current_timestamp);
INSERT INTO tbl_l_agency_facility VALUES ('SHELTER','Shelter Facility','Shelter',sys_user(),current_timestamp,sys_user(),current_timestamp);

