CREATE TABLE tbl_l_mail_delivery (
	mail_delivery_code	VARCHAR(10) PRIMARY KEY,	
	description	TEXT NOT NULL UNIQUE,
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

INSERT INTO tbl_l_mail_delivery (mail_delivery_code,description) VALUES ('DELIVERED','Mail was delivered to client',sys_user(),current_timestamp,sys_user(),current_timestamp);
INSERT INTO tbl_l_mail_delivery (mail_delivery_code,description) VALUES ('RETURNED','Mail was returned to PO/sender',sys_user(),current_timestamp,sys_user(),current_timestamp);

CREATE VIEW l_mail_delivery AS (SELECT * FROM tbl_l_mail_delivery WHERE NOT is_deleted);

