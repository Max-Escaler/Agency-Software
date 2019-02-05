CREATE TABLE      tbl_l_agency_project (
	agency_project_code	VARCHAR(10) PRIMARY KEY,
	description		VARCHAR(80) NOT NULL UNIQUE,
	short_description		VARCHAR(15) NOT NULL UNIQUE,
	agency_program_code	VARCHAR(10) NOT NULL REFERENCES tbl_l_agency_program (agency_program_code),
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

CREATE VIEW l_agency_project AS SELECT * FROM tbl_l_agency_project WHERE NOT is_deleted;

/*
 * AGENCY uses a scheme where an organization is divided
 * hierarchically into programs, and then projects.
 *
 * This table needs to be adjusted in tandem with l_agency_program.
 *
 * In this simple example, 4 projects are grouped into two programs:
 *
 *  Program A -->  Projects 1,2
 *  Program B -->  Projects 3,4
 *
 * fixme:  for more information, see ____
 */

INSERT INTO tbl_l_agency_project VALUES ('ACCOUNT', 'Accounting','Acctg.','ADMIN',sys_user(),current_timestamp,sys_user(),current_timestamp);
INSERT INTO tbl_l_agency_project VALUES ('ALL', '(For the E.D.)','Agency','ADMIN',sys_user(),current_timestamp,sys_user(),current_timestamp);
INSERT INTO tbl_l_agency_project VALUES ('PROJECT_1', 'Project 1','Proj1','PROG_A',sys_user(),current_timestamp,sys_user(),current_timestamp);
INSERT INTO tbl_l_agency_project VALUES ('PROJECT_2', 'Project 2','Proj2','PROG_A',sys_user(),current_timestamp,sys_user(),current_timestamp);
INSERT INTO tbl_l_agency_project VALUES ('PROJECT_3', 'Project 3','Proj3','PROG_B',sys_user(),current_timestamp,sys_user(),current_timestamp);
INSERT INTO tbl_l_agency_project VALUES ('PROJECT_4', 'Project 4','Proj4','PROG_B',sys_user(),current_timestamp,sys_user(),current_timestamp);

