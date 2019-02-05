CREATE TABLE tbl_l_permission_type (
	permission_type_code VARCHAR(20) PRIMARY KEY,
	description	TEXT NOT NULL UNIQUE,
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
 * These are intended to be generic, agency_core permissions
 * Permissions specific to a flavor of AGENCY should
 * go in an add.X_permission_types.sql file.
 *
 * e.g., database/pg/client/add.client_permission_type.sql
 *
 * Undesired permissions can be commented out here, or
 * simply removed from the table after installation.
 *
**/

CREATE VIEW l_permission_type AS SELECT * FROM tbl_l_permission_type WHERE NOT is_deleted;

INSERT INTO tbl_l_permission_type VALUES ('ADMIN', 'Administrative',sys_user(),current_timestamp,sys_user(),current_timestamp);
INSERT INTO tbl_l_permission_type VALUES ('ADD_REMOTE_ACCESS', 'Add/edit Remote Access',sys_user(),current_timestamp,sys_user(),current_timestamp);
INSERT INTO tbl_l_permission_type VALUES ('REPORTS', 'Reports',sys_user(),current_timestamp,sys_user(),current_timestamp);
INSERT INTO tbl_l_permission_type VALUES ('STAFF', 'STAFF Records',sys_user(),current_timestamp,sys_user(),current_timestamp);
INSERT INTO tbl_l_permission_type VALUES ('OPEN_QUERY', 'Run Direct SQL Queries',sys_user(),current_timestamp,sys_user(),current_timestamp);
INSERT INTO tbl_l_permission_type VALUES ('READ_ALL', 'Read access to everything',sys_user(),current_timestamp,sys_user(),current_timestamp);
INSERT INTO tbl_l_permission_type VALUES ('MANAGEMENT', 'Management',sys_user(),current_timestamp,sys_user(),current_timestamp);
INSERT INTO tbl_l_permission_type VALUES ('DEMO_MODE', 'Demo Mode',sys_user(),current_timestamp,sys_user(),current_timestamp);
INSERT INTO tbl_l_permission_type VALUES ('CAL_AHEAD', 'Calendar: Schedule Ahead',sys_user(),current_timestamp,sys_user(),current_timestamp);
INSERT INTO tbl_l_permission_type VALUES ('STAFF_ID_CARDS', 'Print Staff ID Cards',sys_user(),current_timestamp,sys_user(),current_timestamp);
INSERT INTO tbl_l_permission_type VALUES ('BROWSE_STAFF_REQUEST', 'Browse Staff Request Records',sys_user(),current_timestamp,sys_user(),current_timestamp);
INSERT INTO tbl_l_permission_type VALUES ('SUPER_USER', 'AGENCY Super-User',sys_user(),current_timestamp,sys_user(),current_timestamp);
INSERT INTO tbl_l_permission_type VALUES ('PASSWORD', 'Change Passwords',sys_user(),current_timestamp,sys_user(),current_timestamp);
INSERT INTO tbl_l_permission_type VALUES ('PHOTO','Photos',sys_user(),current_timestamp,sys_user(),current_timestamp);

INSERT INTO tbl_l_permission_type VALUES ('USER_SWITCH', 'Switch User Identity',sys_user(),current_timestamp,sys_user(),current_timestamp);
INSERT INTO tbl_l_permission_type VALUES ('STAFF_REQUEST', 'Staff Requests',sys_user(),current_timestamp,sys_user(),current_timestamp);
INSERT INTO tbl_l_permission_type VALUES ('UPDATE_ENGINE', 'Update the Engine Array',sys_user(),current_timestamp,sys_user(),current_timestamp);
INSERT INTO tbl_l_permission_type VALUES ('GENERIC_OO_EXPORT', 'Output Generic OpenOffice files',sys_user(),current_timestamp,sys_user(),current_timestamp);
INSERT INTO tbl_l_permission_type VALUES ('SQL_DUMP', 'Output Data in Raw Formats',sys_user(),current_timestamp,sys_user(),current_timestamp);

