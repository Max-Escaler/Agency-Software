CREATE TABLE tbl_l_connection_type (
    connection_type_code VARCHAR(10) PRIMARY KEY,
    description VARCHAR(30) NOT NULL UNIQUE,
    is_current BOOLEAN NOT NULL DEFAULT TRUE,
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

CREATE VIEW l_connection_type AS SELECT * FROM tbl_l_connection_type WHERE NOT is_deleted;

INSERT INTO tbl_l_connection_type  VALUES ('FRIEND', 'Friend', TRUE, sys_user(),current_timestamp,sys_user(),current_timestamp);
INSERT INTO tbl_l_connection_type  VALUES ('FAMILY', 'Family member', TRUE, sys_user(),current_timestamp,sys_user(),current_timestamp);
INSERT INTO tbl_l_connection_type  VALUES ('CHORE', 'Chore Worker', TRUE, sys_user(),current_timestamp,sys_user(),current_timestamp);
INSERT INTO tbl_l_connection_type  VALUES ('COMMERCE', 'Providing Business or Service', TRUE, sys_user(),current_timestamp,sys_user(),current_timestamp);

INSERT INTO tbl_l_connection_type  VALUES ('MEDICAL', 'Medical Worker', TRUE, sys_user(),current_timestamp,sys_user(),current_timestamp);
INSERT INTO tbl_l_connection_type  VALUES ('SOCIAL', 'Social Worker', TRUE, sys_user(),current_timestamp,sys_user(),current_timestamp);
INSERT INTO tbl_l_connection_type  VALUES ('OFFICER', 'Parole/Correction Officer', TRUE, sys_user(),current_timestamp,sys_user(),current_timestamp);
INSERT INTO tbl_l_connection_type  VALUES ('OTHER', 'Other', TRUE, sys_user(),current_timestamp,sys_user(),current_timestamp);

