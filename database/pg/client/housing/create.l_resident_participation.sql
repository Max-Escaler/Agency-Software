CREATE TABLE tbl_l_resident_participation (
	resident_participation_code	VARCHAR(10) PRIMARY KEY,
	description				VARCHAR(60) NOT NULL UNIQUE,
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

INSERT INTO tbl_l_resident_participation VALUES ('0','Unable to participate for circumstantial/other reason',sys_user(),current_timestamp,sys_user(),current_timestamp);
INSERT INTO tbl_l_resident_participation VALUES ('2','Participated to best ability',sys_user(),current_timestamp,sys_user(),current_timestamp);
INSERT INTO tbl_l_resident_participation VALUES ('4','Participated, but could contribute more',sys_user(),current_timestamp,sys_user(),current_timestamp);
INSERT INTO tbl_l_resident_participation VALUES ('6','Cooperative, no input',sys_user(),current_timestamp,sys_user(),current_timestamp);
INSERT INTO tbl_l_resident_participation VALUES ('8','Refused to cooperate',sys_user(),current_timestamp,sys_user(),current_timestamp);
INSERT INTO tbl_l_resident_participation VALUES ('10','Unable to participate for clinical reason',sys_user(),current_timestamp,sys_user(),current_timestamp);

CREATE VIEW l_resident_participation AS (SELECT * FROM tbl_l_resident_participation WHERE NOT is_deleted);

