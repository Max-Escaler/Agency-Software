CREATE TABLE tbl_l_language (
	language_code	VARCHAR(10) PRIMARY KEY,
	description		VARCHAR(80) NOT NULL UNIQUE,
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

INSERT INTO tbl_l_language VALUES ('71','Japanese','01',sys_user(),current_timestamp,sys_user(),current_timestamp);
INSERT INTO tbl_l_language VALUES ('72','Korean','02',sys_user(),current_timestamp,sys_user(),current_timestamp);
INSERT INTO tbl_l_language VALUES ('74','Vietnamese','04',sys_user(),current_timestamp,sys_user(),current_timestamp);
INSERT INTO tbl_l_language VALUES ('05','Laotian','05',sys_user(),current_timestamp,sys_user(),current_timestamp);
INSERT INTO tbl_l_language VALUES ('06','Cambodian','06',sys_user(),current_timestamp,sys_user(),current_timestamp);
INSERT INTO tbl_l_language VALUES ('07','Mandarin','07',sys_user(),current_timestamp,sys_user(),current_timestamp);
INSERT INTO tbl_l_language VALUES ('08','Hmong','08',sys_user(),current_timestamp,sys_user(),current_timestamp);
INSERT INTO tbl_l_language VALUES ('09','Samoan','09',sys_user(),current_timestamp,sys_user(),current_timestamp);
INSERT INTO tbl_l_language VALUES ('10','Ilocano','10',sys_user(),current_timestamp,sys_user(),current_timestamp);
INSERT INTO tbl_l_language VALUES ('11','Tagalog','11',sys_user(),current_timestamp,sys_user(),current_timestamp);
INSERT INTO tbl_l_language VALUES ('12','French','12',sys_user(),current_timestamp,sys_user(),current_timestamp);
INSERT INTO tbl_l_language VALUES ('13','English','13',sys_user(),current_timestamp,sys_user(),current_timestamp);
INSERT INTO tbl_l_language VALUES ('14','German','14',sys_user(),current_timestamp,sys_user(),current_timestamp);
INSERT INTO tbl_l_language VALUES ('16','Cantonese','16',sys_user(),current_timestamp,sys_user(),current_timestamp);
INSERT INTO tbl_l_language VALUES ('17','Hungarian','17',sys_user(),current_timestamp,sys_user(),current_timestamp);
INSERT INTO tbl_l_language VALUES ('18','Russian','18',sys_user(),current_timestamp,sys_user(),current_timestamp);
INSERT INTO tbl_l_language VALUES ('19','Romanian','19',sys_user(),current_timestamp,sys_user(),current_timestamp);
INSERT INTO tbl_l_language VALUES ('20','Polish','20',sys_user(),current_timestamp,sys_user(),current_timestamp);
INSERT INTO tbl_l_language VALUES ('21','Greek','21',sys_user(),current_timestamp,sys_user(),current_timestamp);
INSERT INTO tbl_l_language VALUES ('22','Tigrigna','22',sys_user(),current_timestamp,sys_user(),current_timestamp);
INSERT INTO tbl_l_language VALUES ('23','Amharic','23',sys_user(),current_timestamp,sys_user(),current_timestamp);
INSERT INTO tbl_l_language VALUES ('24','Finnish','24',sys_user(),current_timestamp,sys_user(),current_timestamp);
INSERT INTO tbl_l_language VALUES ('25','Farsi','25',sys_user(),current_timestamp,sys_user(),current_timestamp);
INSERT INTO tbl_l_language VALUES ('26','Czech','26',sys_user(),current_timestamp,sys_user(),current_timestamp);
INSERT INTO tbl_l_language VALUES ('27','Mien','27',sys_user(),current_timestamp,sys_user(),current_timestamp);
INSERT INTO tbl_l_language VALUES ('28','Yakima','28',sys_user(),current_timestamp,sys_user(),current_timestamp);
INSERT INTO tbl_l_language VALUES ('29','Salish','29',sys_user(),current_timestamp,sys_user(),current_timestamp);
INSERT INTO tbl_l_language VALUES ('30','Puyallup','30',sys_user(),current_timestamp,sys_user(),current_timestamp);
INSERT INTO tbl_l_language VALUES ('31','Thai','31',sys_user(),current_timestamp,sys_user(),current_timestamp);
INSERT INTO tbl_l_language VALUES ('60','Italian','60',sys_user(),current_timestamp,sys_user(),current_timestamp);
INSERT INTO tbl_l_language VALUES ('81','Other African','81',sys_user(),current_timestamp,sys_user(),current_timestamp);
INSERT INTO tbl_l_language VALUES ('82','Other Nat.Amer.','82',sys_user(),current_timestamp,sys_user(),current_timestamp);
INSERT INTO tbl_l_language VALUES ('83','Other Filipino','83',sys_user(),current_timestamp,sys_user(),current_timestamp);
INSERT INTO tbl_l_language VALUES ('85','Other Asian','85',sys_user(),current_timestamp,sys_user(),current_timestamp);
INSERT INTO tbl_l_language VALUES ('87','Other Comm.Type','87',sys_user(),current_timestamp,sys_user(),current_timestamp);
INSERT INTO tbl_l_language VALUES ('99','Other Language','99',sys_user(),current_timestamp,sys_user(),current_timestamp);

INSERT INTO tbl_l_language VALUES ('0','Unknown','',sys_user(),current_timestamp,sys_user(),current_timestamp);
INSERT INTO tbl_l_language VALUES ('1','Spanish','',sys_user(),current_timestamp,sys_user(),current_timestamp);
INSERT INTO tbl_l_language VALUES ('2','SouthEast Asian','',sys_user(),current_timestamp,sys_user(),current_timestamp);
INSERT INTO tbl_l_language VALUES ('3','Eastern European','',sys_user(),current_timestamp,sys_user(),current_timestamp);
INSERT INTO tbl_l_language VALUES ('4','ASL/TTY','',sys_user(),current_timestamp,sys_user(),current_timestamp);

CREATE VIEW l_language AS (SELECT * FROM tbl_l_language WHERE NOT is_deleted);

