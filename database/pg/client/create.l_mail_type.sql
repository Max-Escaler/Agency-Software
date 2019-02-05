CREATE TABLE tbl_l_mail_type (
	mail_type_code varchar(10) PRIMARY KEY,
	description varchar(70) UNIQUE NOT NULL,
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

INSERT INTO tbl_l_mail_type VALUES ('CHECK','Checks (Identifiable) From Any Other Source',sys_user(),current_timestamp,sys_user(),current_timestamp);
INSERT INTO tbl_l_mail_type VALUES ('CERTIFIED','Certified/Registered Mail,Fed-ex,UPS,etc.',sys_user(),current_timestamp,sys_user(),current_timestamp);
INSERT INTO tbl_l_mail_type VALUES ('DSHS-CHECK','DSHS - Checks',sys_user(),current_timestamp,sys_user(),current_timestamp);
INSERT INTO tbl_l_mail_type VALUES ('DSHS-GEN','DSHS - General Mail',sys_user(),current_timestamp,sys_user(),current_timestamp);
INSERT INTO tbl_l_mail_type VALUES ('DSHS-MED','DSHS - Medical Coupons',sys_user(),current_timestamp,sys_user(),current_timestamp);
INSERT INTO tbl_l_mail_type VALUES ('ELECTION','King County Elections',sys_user(),current_timestamp,sys_user(),current_timestamp);
INSERT INTO tbl_l_mail_type VALUES ('GENERAL','General Mail',sys_user(),current_timestamp,sys_user(),current_timestamp);
INSERT INTO tbl_l_mail_type VALUES ('GOVERNMENT','Government Mail--other',sys_user(),current_timestamp,sys_user(),current_timestamp);
INSERT INTO tbl_l_mail_type VALUES ('HHS-CHECK','SSA/SSI/SSD/Veteran\'s Checks (Fed.Treasury/Dept of Health & Human Svcs',sys_user(),current_timestamp,sys_user(),current_timestamp);
INSERT INTO tbl_l_mail_type VALUES ('HHS-GEN','SSA/SSI/SSD General (Federal-Dept of Health & Human Serv)',sys_user(),current_timestamp,sys_user(),current_timestamp);
INSERT INTO tbl_l_mail_type VALUES ('HHS-ID','Social Security ID Card',sys_user(),current_timestamp,sys_user(),current_timestamp);
INSERT INTO tbl_l_mail_type VALUES ('JURY','Jury Summons',sys_user(),current_timestamp,sys_user(),current_timestamp);
INSERT INTO tbl_l_mail_type VALUES ('LABOR','WA Unemployment/Worker\'s Comp (Employment Sec. Dept/Dept of L&I)',sys_user(),current_timestamp,sys_user(),current_timestamp);
INSERT INTO tbl_l_mail_type VALUES ('LABOR-OOS','Unemployment/Worker\'s Comp From Other States',sys_user(),current_timestamp,sys_user(),current_timestamp);
INSERT INTO tbl_l_mail_type VALUES ('LEGAL','Legal (Police, Jail, Prosecutor, Attorneys',sys_user(),current_timestamp,sys_user(),current_timestamp);
INSERT INTO tbl_l_mail_type VALUES ('LIBRARY','Library',sys_user(),current_timestamp,sys_user(),current_timestamp);
INSERT INTO tbl_l_mail_type VALUES ('MEDICAL','Medical: Doctor/Hospitals/Labs/Insurance/Etc.',sys_user(),current_timestamp,sys_user(),current_timestamp);
INSERT INTO tbl_l_mail_type VALUES ('OVERSIZE','Oversized Mail',sys_user(),current_timestamp,sys_user(),current_timestamp);
INSERT INTO tbl_l_mail_type VALUES ('VA','Veterans Administration',sys_user(),current_timestamp,sys_user(),current_timestamp);
INSERT INTO tbl_l_mail_type VALUES ('WSID','Washington Sate Identification',sys_user(),current_timestamp,sys_user(),current_timestamp);

CREATE VIEW l_mail_type AS (SELECT * FROM tbl_l_mail_type WHERE NOT is_deleted);

