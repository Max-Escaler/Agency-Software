CREATE TABLE tbl_l_disability (
	disability_code	VARCHAR(10) PRIMARY KEY,
	description		VARCHAR(45) NOT NULL UNIQUE,
	king_cty_code	VARCHAR(10),
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

INSERT INTO tbl_l_disability VALUES ('11', 'Medically Compromised', '43',sys_user(),current_timestamp,sys_user(),current_timestamp);
INSERT INTO tbl_l_disability VALUES ('12', 'Deaf', '32',sys_user(),current_timestamp,sys_user(),current_timestamp);
INSERT INTO tbl_l_disability VALUES ('1', 'Mental Illness', '24',sys_user(),current_timestamp,sys_user(),current_timestamp);
INSERT INTO tbl_l_disability VALUES ('2', 'Alcohol Abuse', '44',sys_user(),current_timestamp,sys_user(),current_timestamp);
INSERT INTO tbl_l_disability VALUES ('3', 'Drug Abuse', '44',sys_user(),current_timestamp,sys_user(),current_timestamp);
INSERT INTO tbl_l_disability VALUES ('4', 'Developmental Disab.', '23',sys_user(),current_timestamp,sys_user(),current_timestamp);
INSERT INTO tbl_l_disability VALUES ('44', 'Other Med or Phys Disability/Chronic Illness', '44',sys_user(),current_timestamp,sys_user(),current_timestamp);
INSERT INTO tbl_l_disability VALUES ('45', 'Neurological Disability not listed elsewhere', '45',sys_user(),current_timestamp,sys_user(),current_timestamp);
INSERT INTO tbl_l_disability VALUES ('5', 'Mobility Impairment', '23',sys_user(),current_timestamp,sys_user(),current_timestamp);
INSERT INTO tbl_l_disability VALUES ('6', 'Hearing Impairment', '33',sys_user(),current_timestamp,sys_user(),current_timestamp);
INSERT INTO tbl_l_disability VALUES ('7', 'Vision Impairment', '31',sys_user(),current_timestamp,sys_user(),current_timestamp);
INSERT INTO tbl_l_disability VALUES ('8', 'Speech Impairment', '23',sys_user(),current_timestamp,sys_user(),current_timestamp);
INSERT INTO tbl_l_disability VALUES ('9', 'Other', '80',sys_user(),current_timestamp,sys_user(),current_timestamp);

CREATE VIEW l_disability AS (SELECT * FROM tbl_l_disability WHERE NOT is_deleted);
