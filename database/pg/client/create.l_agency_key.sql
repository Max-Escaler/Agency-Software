CREATE TABLE tbl_l_agency_key (
	agency_key_code VARCHAR(10) PRIMARY KEY,
	description	VARCHAR(60) NOT NULL UNIQUE,
	location	VARCHAR(10),
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

INSERT INTO tbl_l_agency_key VALUES ('AB1','Shelter Office','Shelter',sys_user(),current_timestamp,sys_user(),current_timestamp);
INSERT INTO tbl_l_agency_key VALUES ('AB2','Shelter Common','Shelter',sys_user(),current_timestamp,sys_user(),current_timestamp);
INSERT INTO tbl_l_agency_key VALUES ('AC1','Admin Exterior','Admin',sys_user(),current_timestamp,sys_user(),current_timestamp);
INSERT INTO tbl_l_agency_key VALUES ('AC11','Admin Common','Admin',sys_user(),current_timestamp,sys_user(),current_timestamp);
INSERT INTO tbl_l_agency_key VALUES ('AC8','Office (Jane Doe)','Admin',sys_user(),current_timestamp,sys_user(),current_timestamp);
INSERT INTO tbl_l_agency_key VALUES ('AE1','Basement Common','Basement',sys_user(),current_timestamp,sys_user(),current_timestamp);
INSERT INTO tbl_l_agency_key VALUES ('CARD1','Card (Master)','NA',sys_user(),current_timestamp,sys_user(),current_timestamp);
INSERT INTO tbl_l_agency_key VALUES ('CARD2','Card (Shelter)','NA',sys_user(),current_timestamp,sys_user(),current_timestamp);
INSERT INTO tbl_l_agency_key VALUES ('CARD3','Card (Residential)','NA',sys_user(),current_timestamp,sys_user(),current_timestamp);
INSERT INTO tbl_l_agency_key VALUES ('CARD4','Card (Admin)','NA',sys_user(),current_timestamp,sys_user(),current_timestamp);

CREATE VIEW l_agency_key AS (SELECT * FROM tbl_l_agency_key WHERE NOT is_deleted);

