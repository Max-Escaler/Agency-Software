CREATE TABLE tbl_l_hispanic_origin (
    hispanic_origin_code    VARCHAR(10) PRIMARY KEY,
    description VARCHAR(100) NOT NULL UNIQUE,
	king_cty_code	VARCHAR(3),
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

INSERT INTO tbl_l_hispanic_origin VALUES
 ('709','Cuban','709',sys_user(),current_timestamp,sys_user(),current_timestamp);
INSERT INTO tbl_l_hispanic_origin VALUES
 ('722','Mexican-American/Chicano','722',sys_user(),current_timestamp,sys_user(),current_timestamp);
INSERT INTO tbl_l_hispanic_origin VALUES
 ('727','Puerto Rican','727',sys_user(),current_timestamp,sys_user(),current_timestamp);
INSERT INTO tbl_l_hispanic_origin VALUES
 ('781','Other Central American','799',sys_user(),current_timestamp,sys_user(),current_timestamp);
INSERT INTO tbl_l_hispanic_origin VALUES
 ('782','Other South American','799',sys_user(),current_timestamp,sys_user(),current_timestamp);
INSERT INTO tbl_l_hispanic_origin VALUES
 ('799','Other Spanish/Hispanic','799',sys_user(),current_timestamp,sys_user(),current_timestamp);
INSERT INTO tbl_l_hispanic_origin VALUES
 ('998','Not Hispanic*','998',sys_user(),current_timestamp,sys_user(),current_timestamp);
INSERT INTO tbl_l_hispanic_origin VALUES
 ('999','Unkown','999',sys_user(),current_timestamp,sys_user(),current_timestamp);
INSERT INTO tbl_l_hispanic_origin (hispanic_origin_code,description,added_by,changed_by) VALUES 
 ('HISP_GEN','Hispanic, specificity unknown',sys_user(),sys_user());

CREATE VIEW l_hispanic_origin AS (SELECT * FROM tbl_l_hispanic_origin WHERE NOT is_deleted);

