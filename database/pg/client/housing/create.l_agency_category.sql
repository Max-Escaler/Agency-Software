CREATE TABLE tbl_l_agency_category (
	agency_category_code VARCHAR(10) PRIMARY KEY,
	description VARCHAR(60)	UNIQUE,
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

INSERT INTO tbl_l_agency_category VALUES ('MH-PHP','Mental Health, PHP',sys_user(),current_timestamp,sys_user(),current_timestamp);
INSERT INTO tbl_l_agency_category VALUES ('MH-OTHER','Mental Health, Other funding',sys_user(),current_timestamp,sys_user(),current_timestamp);
INSERT INTO tbl_l_agency_category VALUES ('CD','Chemical Dependency',sys_user(),current_timestamp,sys_user(),current_timestamp);
INSERT INTO tbl_l_agency_category VALUES ('PAYEE','Payee Services',sys_user(),current_timestamp,sys_user(),current_timestamp);
INSERT INTO tbl_l_agency_category VALUES ('HIV','HIV/AIDS',sys_user(),current_timestamp,sys_user(),current_timestamp);
INSERT INTO tbl_l_agency_category VALUES ('Other','Other Services',sys_user(),current_timestamp,sys_user(),current_timestamp);

CREATE VIEW l_agency_category AS (SELECT * FROM tbl_l_agency_category WHERE NOT is_deleted);

