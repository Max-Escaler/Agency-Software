CREATE TABLE tbl_l_application_rank (
	application_rank_code VARCHAR(10) PRIMARY KEY,
	description	VARCHAR(50) NOT NULL UNIQUE,
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

CREATE VIEW l_application_rank AS SELECT * FROM tbl_l_application_rank WHERE NOT is_deleted;

INSERT INTO tbl_l_application_rank VALUES('NOT_ELGBL','Not Eligible',sys_user(),current_timestamp,sys_user(),current_timestamp);
INSERT INTO tbl_l_application_rank VALUES('NOT_APPRP','Not Appropriate',sys_user(),current_timestamp,sys_user(),current_timestamp);
INSERT INTO tbl_l_application_rank VALUES('LOW','Low Priority',sys_user(),current_timestamp,sys_user(),current_timestamp);
INSERT INTO tbl_l_application_rank VALUES('MEDIUM','Medium Priority',sys_user(),current_timestamp,sys_user(),current_timestamp);
INSERT INTO tbl_l_application_rank VALUES('HIGH','High Priority',sys_user(),current_timestamp,sys_user(),current_timestamp);

