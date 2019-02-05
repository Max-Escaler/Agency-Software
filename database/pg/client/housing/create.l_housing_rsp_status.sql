CREATE TABLE tbl_l_housing_rsp_status (
	housing_rsp_status_code	VARCHAR(10) PRIMARY KEY,
	description			VARCHAR(40) NOT NULL UNIQUE,
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

INSERT INTO tbl_l_housing_rsp_status VALUES ('2','Initial consult done',sys_user(),current_timestamp,sys_user(),current_timestamp);
INSERT INTO tbl_l_housing_rsp_status VALUES ('4','Initial RSP completed',sys_user(),current_timestamp,sys_user(),current_timestamp);
INSERT INTO tbl_l_housing_rsp_status VALUES ('6','Review consult completed',sys_user(),current_timestamp,sys_user(),current_timestamp);
INSERT INTO tbl_l_housing_rsp_status VALUES ('7','Review completed-no changes necessary',sys_user(),current_timestamp,sys_user(),current_timestamp);
INSERT INTO tbl_l_housing_rsp_status VALUES ('8','RSP Revision completed',sys_user(),current_timestamp,sys_user(),current_timestamp);
INSERT INTO tbl_l_housing_rsp_status VALUES ('UNKNOWN','Unknown',sys_user(),current_timestamp,sys_user(),current_timestamp);
INSERT INTO tbl_l_housing_rsp_status VALUES ('OTHER','Other status',sys_user(),current_timestamp,sys_user(),current_timestamp);

CREATE VIEW l_housing_rsp_status AS (SELECT * FROM tbl_l_housing_rsp_status WHERE NOT is_deleted);

