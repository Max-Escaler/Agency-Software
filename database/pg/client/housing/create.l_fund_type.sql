CREATE TABLE tbl_l_fund_type (
	fund_type_code VARCHAR(10) NOT NULL PRIMARY KEY,
	description VARCHAR(45) NOT NULL UNIQUE,
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

INSERT INTO tbl_l_fund_type VALUES ('S8PB','Section 8, Project-based',sys_user(),current_timestamp,sys_user(),current_timestamp); 
INSERT INTO tbl_l_fund_type VALUES ('S8TB','Section 8, Tenant-based',sys_user(),current_timestamp,sys_user(),current_timestamp); 
INSERT INTO tbl_l_fund_type VALUES ('SHP','Supportive Housing Program',sys_user(),current_timestamp,sys_user(),current_timestamp); 
INSERT INTO tbl_l_fund_type VALUES ('SPCTB','Shelter Plus Care, Tenant Based',sys_user(),current_timestamp,sys_user(),current_timestamp); 
INSERT INTO tbl_l_fund_type VALUES ('SPC','Shelter Plus Care',sys_user(),current_timestamp,sys_user(),current_timestamp); 
--others for future???

CREATE VIEW l_fund_type AS (SELECT * FROM tbl_l_fund_type WHERE NOT is_deleted);

