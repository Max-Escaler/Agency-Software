CREATE TABLE tbl_l_agency_program (
    agency_program_code VARCHAR(10) PRIMARY KEY,
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

CREATE VIEW l_agency_program AS SELECT * FROM tbl_l_agency_program WHERE NOT is_deleted;

/*
 * AGENCY uses a scheme where an organization is divided
 * hierarchically into programs, and then projects.
 *
 * This table needs to be adjusted in tandem with l_agency_project.
 *
 * In this simple example, 4 projects are grouped into two programs:
 *
 *  Program A -->  Projects 1,2
 *  Program B -->  Projects 3,4
 *
 * fixme:  for more information, see ____
 */

INSERT INTO tbl_l_agency_program VALUES ('ADMIN', 'Administration',sys_user(),current_timestamp,sys_user(),current_timestamp);
INSERT INTO tbl_l_agency_program VALUES ('ALL', 'Agency wide (e.g., Executive Director',sys_user(),current_timestamp,sys_user(),current_timestamp);
INSERT INTO tbl_l_agency_program VALUES ('PROG_A', 'Program A',sys_user(),current_timestamp,sys_user(),current_timestamp);
INSERT INTO tbl_l_agency_program VALUES ('PROG_B', 'Program B',sys_user(),current_timestamp,sys_user(),current_timestamp);

