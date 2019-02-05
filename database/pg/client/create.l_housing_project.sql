CREATE TABLE tbl_l_housing_project (
	housing_project_code	VARCHAR(10) PRIMARY KEY,
	description		VARCHAR(80) NOT NULL UNIQUE,
    address1        VARCHAR(80),
    address2        VARCHAR(80),
    city            VARCHAR(80),
    state_code      VARCHAR(3),
    zipcode         VARCHAR(10),
	geography_code			VARCHAR(10),
	geography_detail_code	INTEGER,
	auto_calculate_subsidy_charges	BOOLEAN,
	auto_calculate_rent_charges		BOOLEAN,
	agency_program_code	VARCHAR(10) REFERENCES tbl_l_agency_program (agency_program_code),
	agency_project_code	VARCHAR(10) REFERENCES tbl_l_agency_project (agency_project_code),
	unit_code_prefix VARCHAR(10),

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

/*
 * agency_program_code & agency_project_code are currently unused, but
 * could be used later to tie housing projects to agency programs or projects
 *
 */

INSERT INTO tbl_l_housing_project VALUES ('HOUSING_1', 'Housing Project 1', '123 Main Street','','Seattle','WA','98102','SEATTLE',210,false, false,'PROG_A','PROJECT_1','A',sys_user(),current_timestamp,sys_user(),current_timestamp);
INSERT INTO tbl_l_housing_project VALUES ('HOUSING_2', 'Housing Project 2', '393 Papermill Lane','','Tacoma','WA','98401','WA',NULL,false, false,'PROG_A','PROJECT_2','B',sys_user(),current_timestamp,sys_user(),current_timestamp);

CREATE VIEW l_housing_project AS (SELECT * FROM tbl_l_housing_project WHERE NOT is_deleted);

