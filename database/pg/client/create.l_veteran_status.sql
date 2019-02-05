CREATE TABLE tbl_l_veteran_status (
	veteran_status_code	VARCHAR(10) PRIMARY KEY,
	description			VARCHAR(30) NOT NULL UNIQUE,
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

INSERT INTO tbl_l_veteran_status VALUES ('0', 'Not a US Veteran',sys_user(),current_timestamp,sys_user(),current_timestamp);
INSERT INTO tbl_l_veteran_status VALUES ('1', 'Pre Viet Nam Era Vet',sys_user(),current_timestamp,sys_user(),current_timestamp);
INSERT INTO tbl_l_veteran_status VALUES ('2', 'Viet Nam Era Vet',sys_user(),current_timestamp,sys_user(),current_timestamp);
INSERT INTO tbl_l_veteran_status VALUES ('3', 'Post Viet Nam Era Vet',sys_user(),current_timestamp,sys_user(),current_timestamp);
INSERT INTO tbl_l_veteran_status VALUES ('4', 'Veteran - Era Unkown',sys_user(),current_timestamp,sys_user(),current_timestamp);
INSERT INTO tbl_l_veteran_status VALUES ('5', 'Unknown',sys_user(),current_timestamp,sys_user(),current_timestamp);

CREATE VIEW l_veteran_status AS (SELECT * FROM tbl_l_veteran_status WHERE NOT is_deleted);

