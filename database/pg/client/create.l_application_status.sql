CREATE TABLE tbl_l_application_status (
	application_status_code	VARCHAR(10) PRIMARY KEY,
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

INSERT INTO tbl_l_application_status VALUES ('DENIED','Denied',sys_user(),current_timestamp,sys_user(),current_timestamp);
INSERT INTO tbl_l_application_status VALUES ('PENDING','Pending',sys_user(),current_timestamp,sys_user(),current_timestamp);
INSERT INTO tbl_l_application_status VALUES ('ACCEPTED','Accepted',sys_user(),current_timestamp,sys_user(),current_timestamp);
INSERT INTO tbl_l_application_status VALUES ('DEFERRED','Deferred',sys_user(),current_timestamp,sys_user(),current_timestamp);
INSERT INTO tbl_l_application_status VALUES ('MOVED_IN','Moved in',sys_user(),current_timestamp,sys_user(),current_timestamp);
INSERT INTO tbl_l_application_status VALUES ('WITHDREW','Withdrew',sys_user(),current_timestamp,sys_user(),current_timestamp);
INSERT INTO tbl_l_application_status VALUES ('DISAPPEAR','Disappeared',sys_user(),current_timestamp,sys_user(),current_timestamp);
INSERT INTO tbl_l_application_status VALUES ('FOUND','Found Other Housing',sys_user(),current_timestamp,sys_user(),current_timestamp);
INSERT INTO tbl_l_application_status VALUES ('EXPIRED','Expired',sys_user(),current_timestamp,sys_user(),current_timestamp);
INSERT INTO tbl_l_application_status VALUES ('REFERRAL','Referral',sys_user(),current_timestamp,sys_user(),current_timestamp);

CREATE VIEW l_application_status AS (SELECT * FROM tbl_l_application_status WHERE NOT is_deleted);
