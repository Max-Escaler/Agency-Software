CREATE TABLE tbl_l_work_order_status
       (
   work_order_status_code	VARCHAR(10) PRIMARY KEY NOT NULL,
    description		VARCHAR(100) NOT NULL UNIQUE,
	is_open_status			BOOLEAN NOT NULL,
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

INSERT INTO tbl_l_work_order_status
       (work_order_status_code, description,is_open_status,added_by,added_at,changed_by,changed_at)
VALUES ('PENDING', 'Pending',true,sys_user(),current_timestamp,sys_user(),current_timestamp);
INSERT INTO tbl_l_work_order_status
       (work_order_status_code, description,is_open_status,added_by,added_at,changed_by,changed_at)
VALUES ('REOPENED', 'Reopened',true,sys_user(),current_timestamp,sys_user(),current_timestamp);
INSERT INTO tbl_l_work_order_status
       (work_order_status_code, description,is_open_status,added_by,added_at,changed_by,changed_at)
VALUES ('PAUSED', 'Paused',true,sys_user(),current_timestamp,sys_user(),current_timestamp);
INSERT INTO tbl_l_work_order_status
       (work_order_status_code, description,is_open_status,added_by,added_at,changed_by,changed_at)
VALUES ('FIXED', 'Fixed',false,sys_user(),current_timestamp,sys_user(),current_timestamp);
INSERT INTO tbl_l_work_order_status
       (work_order_status_code, description,is_open_status,added_by,added_at,changed_by,changed_at)
VALUES ('WONTFIX', 'Won''t fix',false,sys_user(),current_timestamp,sys_user(),current_timestamp);
INSERT INTO tbl_l_work_order_status
       (work_order_status_code, description,is_open_status,added_by,added_at,changed_by,changed_at)
VALUES ('INVALID', 'Invalid',false,sys_user(),current_timestamp,sys_user(),current_timestamp);
INSERT INTO tbl_l_work_order_status
       (work_order_status_code, description,is_open_status,added_by,added_at,changed_by,changed_at)
VALUES ('DUPLICATE', 'Duplicate',false,sys_user(),current_timestamp,sys_user(),current_timestamp);
INSERT INTO tbl_l_work_order_status
       (work_order_status_code, description,is_open_status,added_by,added_at,changed_by,changed_at)
VALUES ('WORKSFORME', 'Works for me',false,sys_user(),current_timestamp,sys_user(),current_timestamp);
INSERT INTO tbl_l_work_order_status
       (work_order_status_code, description,is_open_status,added_by,added_at,changed_by,changed_at)
VALUES ('LATER', 'Revisit Later',false,sys_user(),current_timestamp,sys_user(),current_timestamp);
//VALUES ('', '',false,sys_user(),current_timestamp,sys_user(),current_timestamp);

CREATE VIEW l_work_order_status AS SELECT *,CASE WHEN is_open_status THEN 'Open' ELSE 'Closed' END AS grouping FROM tbl_l_work_order_status WHERE NOT is_deleted;
