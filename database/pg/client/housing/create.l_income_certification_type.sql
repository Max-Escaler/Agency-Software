CREATE TABLE tbl_l_income_certification_type (
	income_certification_type_code VARCHAR(10) NOT NULL PRIMARY KEY,
	description VARCHAR(45) NOT NULL,
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

INSERT INTO tbl_l_income_certification_type VALUES ('ANNUAL','Annual',sys_user(),current_timestamp,sys_user(),current_timestamp); 
INSERT INTO tbl_l_income_certification_type VALUES ('INITIAL','Initial',sys_user(),current_timestamp,sys_user(),current_timestamp); 
INSERT INTO tbl_l_income_certification_type VALUES ('INTERIM','Interim',sys_user(),current_timestamp,sys_user(),current_timestamp); 
INSERT INTO tbl_l_income_certification_type VALUES ('MOVE_IN','Move in',sys_user(),current_timestamp,sys_user(),current_timestamp); 
INSERT INTO tbl_l_income_certification_type VALUES ('NO_50058','No 50058',sys_user(),current_timestamp,sys_user(),current_timestamp); 
INSERT INTO tbl_l_income_certification_type VALUES ('OTHER','Other (please specify)',sys_user(),current_timestamp,sys_user(),current_timestamp); 
--others for future???

CREATE VIEW l_income_certification_type AS (SELECT * FROM tbl_l_income_certification_type WHERE NOT is_deleted);

