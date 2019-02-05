CREATE TABLE      tbl_l_barred_from (
	barred_from_code		VARCHAR(10)     PRIMARY KEY,
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

/* Pre-populate with Bar location list--customize as needed */
INSERT INTO tbl_l_barred_from (SELECT bar_location_code AS barred_from_code, description,sys_user(),current_timestamp,sys_user(),current_timestamp FROM l_bar_location);

CREATE VIEW l_barred_from AS (SELECT * FROM tbl_l_barred_from WHERE NOT is_deleted);

