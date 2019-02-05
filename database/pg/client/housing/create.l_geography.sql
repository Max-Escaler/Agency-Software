CREATE TABLE tbl_l_geography (
    geography_code VARCHAR(10) NOT NULL PRIMARY KEY,
    description             TEXT UNIQUE NOT NULL,
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

-- These categories are taken from l_last_residence (shelter regs),
-- except that I removed the "Other" option (9) (which was in
-- addition to "Other Country".
-- I figure the first 5 cover all the possibilities (until we colonize Mars),
-- So that we don't need a generic other.
INSERT INTO tbl_l_geography VALUES ('SEATTLE','Seattle',sys_user(),current_timestamp,sys_user(),current_timestamp);
INSERT INTO tbl_l_geography VALUES ('KING','King County (Outside Seattle)',sys_user(),current_timestamp,sys_user(),current_timestamp);
INSERT INTO tbl_l_geography VALUES ('WA','WA (Outside King County)',sys_user(),current_timestamp,sys_user(),current_timestamp);
INSERT INTO tbl_l_geography VALUES ('US','United States (Outside WA)',sys_user(),current_timestamp,sys_user(),current_timestamp);
INSERT INTO tbl_l_geography VALUES ('OTHER','Other Country',sys_user(),current_timestamp,sys_user(),current_timestamp);
INSERT INTO tbl_l_geography VALUES ('UNKNOWN','Unknown',sys_user(),current_timestamp,sys_user(),current_timestamp);

CREATE VIEW l_geography AS (SELECT * FROM tbl_l_geography WHERE NOT is_deleted);

