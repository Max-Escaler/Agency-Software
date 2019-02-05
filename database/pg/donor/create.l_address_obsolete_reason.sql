CREATE TABLE tbl_l_address_obsolete_reason (
	address_obsolete_reason_code	VARCHAR(10) PRIMARY KEY,
	description				TEXT NOT NULL UNIQUE,
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

INSERT INTO tbl_l_address_obsolete_reason VALUES ('RETURNED','Mail Returned',sys_user(),current_timestamp,sys_user(),current_timestamp);
INSERT INTO tbl_l_address_obsolete_reason VALUES ('MOVED','Moved',sys_user(),current_timestamp,sys_user(),current_timestamp);
INSERT INTO tbl_l_address_obsolete_reason VALUES ('NO_ORG','No longer with organization',sys_user(),current_timestamp,sys_user(),current_timestamp);
INSERT INTO tbl_l_address_obsolete_reason VALUES ('UNKNOWN','Unknown',sys_user(),current_timestamp,sys_user(),current_timestamp);

CREATE VIEW l_address_obsolete_reason AS (SELECT * FROM tbl_l_address_obsolete_reason WHERE NOT is_deleted);

