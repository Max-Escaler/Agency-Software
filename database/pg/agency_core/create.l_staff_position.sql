CREATE TABLE tbl_l_staff_position (
	staff_position_code VARCHAR(10) NOT NULL PRIMARY KEY ,
	description VARCHAR(60) NOT NULL UNIQUE, 
	is_supervisor BOOLEAN,
	supervised_by_position VARCHAR(10) REFERENCES tbl_l_staff_position(staff_position_code),
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

CREATE VIEW l_staff_position AS SELECT * FROM tbl_l_staff_position WHERE NOT is_deleted;

INSERT INTO tbl_l_staff_position VALUES ('ADMINSUPP', 'Administrative Support', NULL, NULL,sys_user(),current_timestamp,sys_user(),current_timestamp);
INSERT INTO tbl_l_staff_position VALUES ('CASE_MGR', 'Case Manager', NULL, NULL,sys_user(),current_timestamp,sys_user(),current_timestamp);
INSERT INTO tbl_l_staff_position VALUES ('CNSLRCD', 'Chemical Dependency Counselor', NULL, NULL,sys_user(),current_timestamp,sys_user(),current_timestamp);
INSERT INTO tbl_l_staff_position VALUES ('CSS', 'Clinical Support Specialist', NULL, NULL,sys_user(),current_timestamp,sys_user(),current_timestamp);
INSERT INTO tbl_l_staff_position VALUES ('EXECDIR', 'Executive Director', NULL, NULL,sys_user(),current_timestamp,sys_user(),current_timestamp);
INSERT INTO tbl_l_staff_position VALUES ('CNSLRIR', 'Information & Referral Counselor', NULL, NULL,sys_user(),current_timestamp,sys_user(),current_timestamp);
INSERT INTO tbl_l_staff_position VALUES ('JANITOR', 'Janitor', NULL, NULL,sys_user(),current_timestamp,sys_user(),current_timestamp);
INSERT INTO tbl_l_staff_position VALUES ('CASEMGROUT', 'Outreach Case Manager', NULL, NULL,sys_user(),current_timestamp,sys_user(),current_timestamp);
INSERT INTO tbl_l_staff_position VALUES ('ASSTPROJ', 'Project Assistant', NULL, NULL,sys_user(),current_timestamp,sys_user(),current_timestamp);
INSERT INTO tbl_l_staff_position VALUES ('MGRPROJ', 'Project Manager', NULL, NULL,sys_user(),current_timestamp,sys_user(),current_timestamp);
INSERT INTO tbl_l_staff_position VALUES ('RN', 'Registered Nurse', NULL, NULL,sys_user(),current_timestamp,sys_user(),current_timestamp);
INSERT INTO tbl_l_staff_position VALUES ('RC', 'Residential Counselor', NULL, NULL,sys_user(),current_timestamp,sys_user(),current_timestamp);
INSERT INTO tbl_l_staff_position VALUES ('CNSLRSHLT', 'Shelter Counselor', NULL, NULL,sys_user(),current_timestamp,sys_user(),current_timestamp);
INSERT INTO tbl_l_staff_position VALUES ('INTERN', 'Intern', NULL, NULL,sys_user(),current_timestamp,sys_user(),current_timestamp);
INSERT INTO tbl_l_staff_position VALUES ('SUPSHLT', 'Shelter Supervisor', NULL, NULL,sys_user(),current_timestamp,sys_user(),current_timestamp);
INSERT INTO tbl_l_staff_position VALUES ('SUPSHLTAST', 'Shelter Assistant Supervisor', NULL, NULL,sys_user(),current_timestamp,sys_user(),current_timestamp);
INSERT INTO tbl_l_staff_position VALUES ('SYSUSER', 'System User (not a person)', NULL, NULL,sys_user(),current_timestamp,sys_user(),current_timestamp);
INSERT INTO tbl_l_staff_position VALUES ('VOLUNTEER', 'Community Volunteer', NULL, NULL,sys_user(),current_timestamp,sys_user(),current_timestamp);

