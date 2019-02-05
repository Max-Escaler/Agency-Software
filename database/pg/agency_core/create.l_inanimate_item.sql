CREATE TABLE tbl_l_inanimate_item (
	inanimate_item_code VARCHAR(10) PRIMARY KEY,
	description VARCHAR(60) NOT NULL UNIQUE,
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

INSERT INTO tbl_l_inanimate_item VALUES ('VAN_GREEN','The Green Van',sys_user(),current_timestamp,sys_user(),current_timestamp);
INSERT INTO tbl_l_inanimate_item VALUES ('VAN_WHITE','The White Van ',sys_user(),current_timestamp,sys_user(),current_timestamp);
--conference rooms
INSERT INTO tbl_l_inanimate_item VALUES ('CONF_LARGE','The Large Conference Room',sys_user(),current_timestamp,sys_user(),current_timestamp);
INSERT INTO tbl_l_inanimate_item VALUES ('CONF_SMALL','The Small Conference Room',sys_user(),current_timestamp,sys_user(),current_timestamp);

CREATE VIEW l_inanimate_item AS (SELECT * FROM tbl_l_inanimate_item WHERE NOT is_deleted);

