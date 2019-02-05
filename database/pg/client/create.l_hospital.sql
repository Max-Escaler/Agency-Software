CREATE TABLE tbl_l_hospital (
	hospital_code 		VARCHAR(10) PRIMARY KEY,
	description 		TEXT NOT NULL UNIQUE,
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

INSERT INTO tbl_l_hospital (hospital_code,description,added_by,changed_by) VALUES ('HARBORVIEW','Harborview Medical Center',sys_user(),sys_user());
INSERT INTO tbl_l_hospital (hospital_code,description,added_by,changed_by) VALUES ('SWEDISH','Swedish Medical Center',sys_user(),sys_user());
INSERT INTO tbl_l_hospital (hospital_code,description,added_by,changed_by) VALUES ('UW','UW Medical Center',sys_user(),sys_user());
INSERT INTO tbl_l_hospital (hospital_code,description,added_by,changed_by) VALUES ('VM','Virginia Mason Medical Center',sys_user(),sys_user());
INSERT INTO tbl_l_hospital (hospital_code,description,added_by,changed_by) VALUES ('HIGHLINE','Highline',sys_user(),sys_user());
INSERT INTO tbl_l_hospital (hospital_code,description,added_by,changed_by) VALUES ('NAVOS','Navos',sys_user(),sys_user());
INSERT INTO tbl_l_hospital (hospital_code,description,added_by,changed_by) VALUES ('FAIRFAX','Fairfax',sys_user(),sys_user());

CREATE VIEW l_hospital AS (SELECT * FROM tbl_l_hospital WHERE NOT is_deleted);

