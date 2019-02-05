/* This is a lookup table for programs handled in simple registration */

CREATE TABLE tbl_l_program (
    program_code VARCHAR(10) PRIMARY KEY,
    description TEXT NOT NULL UNIQUE,
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

CREATE VIEW l_program AS SELECT * FROM tbl_l_program WHERE NOT is_deleted;

/* Initially populate from l_agency_program.  Adjust to suit your needs. */

INSERT INTO tbl_l_program (program_code,description,added_by,changed_by)
	SELECT agency_program_code, description, sys_user(),sys_user()
	FROM l_agency_program;


