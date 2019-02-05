CREATE TABLE tbl_l_reoffense_risk (
	reoffense_risk_code     VARCHAR(10)     PRIMARY KEY,
	description     TEXT,
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

INSERT INTO tbl_l_reoffense_risk VALUES ('NA', 'NA',sys_user(),current_timestamp,sys_user(),current_timestamp);
INSERT INTO tbl_l_reoffense_risk VALUES ('LEVEL1', 'Level 1',sys_user(),current_timestamp,sys_user(),current_timestamp);
INSERT INTO tbl_l_reoffense_risk VALUES ('LEVEL2', 'Level 2',sys_user(),current_timestamp,sys_user(),current_timestamp);
INSERT INTO tbl_l_reoffense_risk VALUES ('LEVEL3', 'Level 3',sys_user(),current_timestamp,sys_user(),current_timestamp);
INSERT INTO tbl_l_reoffense_risk VALUES ('OTHER', 'Other',sys_user(),current_timestamp,sys_user(),current_timestamp);
INSERT INTO tbl_l_reoffense_risk VALUES ('UNKNOWN', 'Unknown',sys_user(),current_timestamp,sys_user(),current_timestamp);

CREATE VIEW l_reoffense_risk AS (SELECT * FROM tbl_l_reoffense_risk WHERE NOT is_deleted);

