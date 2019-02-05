CREATE TABLE tbl_l_phone_type (
	phone_type_code VARCHAR(10) NOT NULL PRIMARY KEY,
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

INSERT INTO tbl_l_phone_type VALUES ('HOME','Home',sys_user(),current_timestamp,sys_user(),current_timestamp); 
INSERT INTO tbl_l_phone_type VALUES ('WORK','Work',sys_user(),current_timestamp,sys_user(),current_timestamp);
INSERT INTO tbl_l_phone_type VALUES ('FAX','Fax',sys_user(),current_timestamp,sys_user(),current_timestamp);
INSERT INTO tbl_l_phone_type VALUES ('VOICE','Voice Mail',sys_user(),current_timestamp,sys_user(),current_timestamp);  
INSERT INTO tbl_l_phone_type VALUES ('PAGER','Pager',sys_user(),current_timestamp,sys_user(),current_timestamp); 
INSERT INTO tbl_l_phone_type VALUES ('MOBILE','Mobile',sys_user(),current_timestamp,sys_user(),current_timestamp); 
INSERT INTO tbl_l_phone_type VALUES ('UNKNOWN','Unknown',sys_user(),current_timestamp,sys_user(),current_timestamp); 
INSERT INTO tbl_l_phone_type VALUES ('OTHER','Other',sys_user(),current_timestamp,sys_user(),current_timestamp); 
--others for future???

CREATE VIEW l_phone_type AS (SELECT * FROM tbl_l_phone_type WHERE NOT is_deleted);

