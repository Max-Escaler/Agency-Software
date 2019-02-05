CREATE TABLE tbl_l_has_language_barrier (
	has_language_barrier_code	VARCHAR(10) PRIMARY KEY,
	description				VARCHAR(80) NOT NULL UNIQUE,
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

INSERT INTO tbl_l_has_language_barrier VALUES ('0','None',sys_user(),current_timestamp,sys_user(),current_timestamp);
INSERT INTO tbl_l_has_language_barrier VALUES ('1','Needs Interpreter',sys_user(),current_timestamp,sys_user(),current_timestamp);
INSERT INTO tbl_l_has_language_barrier VALUES ('2','Uses Sign Language/TTY',sys_user(),current_timestamp,sys_user(),current_timestamp);

CREATE VIEW l_has_language_barrier AS (SELECT * FROM tbl_l_has_language_barrier WHERE NOT is_deleted);
