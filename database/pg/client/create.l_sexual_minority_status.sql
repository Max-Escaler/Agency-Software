CREATE TABLE tbl_l_sexual_minority_status (
    sexual_minority_status_code     VARCHAR(10) PRIMARY KEY,
    description VARCHAR(200) NOT NULL UNIQUE,
	king_cty_code	VARCHAR(2),
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

INSERT INTO tbl_l_sexual_minority_status VALUES
 ('1','Client states he/she is heterosexual','1',sys_user(),current_timestamp,sys_user(),current_timestamp);
INSERT INTO tbl_l_sexual_minority_status VALUES
 ('2','Client states he/she is a member of a sexual minority','2',sys_user(),current_timestamp,sys_user(),current_timestamp);
INSERT INTO tbl_l_sexual_minority_status VALUES
 ('NOT_ASKED','Not Asked/Not Reported/Client didn''t self identify','8',sys_user(),current_timestamp,sys_user(),current_timestamp);
INSERT INTO tbl_l_sexual_minority_status VALUES
 ('UNKNOWN','Unknown','9',sys_user(),current_timestamp,sys_user(),current_timestamp);

CREATE VIEW l_sexual_minority_status AS (SELECT * FROM tbl_l_sexual_minority_status WHERE NOT is_deleted);

