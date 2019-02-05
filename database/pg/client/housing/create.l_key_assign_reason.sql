CREATE TABLE tbl_l_key_assign_reason (
	key_assign_reason_code VARCHAR(10) PRIMARY KEY,
	description VARCHAR(30) NOT NULL UNIQUE,
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

INSERT INTO tbl_l_key_assign_reason VALUES('MOVEIN','Move-in',sys_user(),current_timestamp,sys_user(),current_timestamp);
INSERT INTO tbl_l_key_assign_reason VALUES('TRANSFER','Unit Transfer',sys_user(),current_timestamp,sys_user(),current_timestamp);
INSERT INTO tbl_l_key_assign_reason VALUES('LOST','Keys lost',sys_user(),current_timestamp,sys_user(),current_timestamp);
INSERT INTO tbl_l_key_assign_reason VALUES('STOLEN','Keys stolen',sys_user(),current_timestamp,sys_user(),current_timestamp);
INSERT INTO tbl_l_key_assign_reason VALUES('DESTROYED','Keys destroyed',sys_user(),current_timestamp,sys_user(),current_timestamp);
INSERT INTO tbl_l_key_assign_reason VALUES('OTHER','Other reason',sys_user(),current_timestamp,sys_user(),current_timestamp);

CREATE VIEW l_key_assign_reason AS (SELECT * FROM tbl_l_key_assign_reason WHERE NOT is_deleted);

