/*
 * These are permissions specific to a client installation of AGENCY.
 *
 * The table and some core permissions are created in agency_core.
 */

INSERT INTO tbl_l_permission_type VALUES ('JILS_IMPORT', 'Import from JILS',sys_user(),current_timestamp,sys_user(),current_timestamp);
INSERT INTO tbl_l_permission_type VALUES ('HOUSING', 'Housing',sys_user(),current_timestamp,sys_user(),current_timestamp);
INSERT INTO tbl_l_permission_type VALUES ('RENT', 'Rent Charges',sys_user(),current_timestamp,sys_user(),current_timestamp);
INSERT INTO tbl_l_permission_type VALUES ('CLINICAL', 'Clinical',sys_user(),current_timestamp,sys_user(),current_timestamp);
INSERT INTO tbl_l_permission_type VALUES ('REG_BED_MENS', 'Register Men''s Beds',sys_user(),current_timestamp,sys_user(),current_timestamp);
INSERT INTO tbl_l_permission_type VALUES ('REG_BED_WOMENS', 'Register Women''s Beds',sys_user(),current_timestamp,sys_user(),current_timestamp);
INSERT INTO tbl_l_permission_type VALUES ('REG_BED_OVERFLOW', 'Register Overflow Beds',sys_user(),current_timestamp,sys_user(),current_timestamp);
INSERT INTO tbl_l_permission_type VALUES ('REG_BED_FREEZE', 'Freeze Bed Registration',sys_user(),current_timestamp,sys_user(),current_timestamp);
INSERT INTO tbl_l_permission_type VALUES ('LOG_SHELTER', 'Shelter Logs',sys_user(),current_timestamp,sys_user(),current_timestamp);
INSERT INTO tbl_l_permission_type VALUES ('BED_ADJUST', 'Adjust Bed Records',sys_user(),current_timestamp,sys_user(),current_timestamp);
INSERT INTO tbl_l_permission_type VALUES ('TESTING', 'Reports in Testing',sys_user(),current_timestamp,sys_user(),current_timestamp);
INSERT INTO tbl_l_permission_type VALUES ('HIV', 'Access HIV records',sys_user(),current_timestamp,sys_user(),current_timestamp);
INSERT INTO tbl_l_permission_type VALUES ('HOUSING_ADMIN', 'Housing administrative',sys_user(),current_timestamp,sys_user(),current_timestamp);
INSERT INTO tbl_l_permission_type VALUES ('CM_MH_ASSIGNS', 'Assign MH Case Managers',sys_user(),current_timestamp,sys_user(),current_timestamp);
INSERT INTO tbl_l_permission_type VALUES ('HOUSING_SCATTERED', 'Scattered Site Admin',sys_user(),current_timestamp,sys_user(),current_timestamp);
INSERT INTO tbl_l_permission_type VALUES ('MAIL_ENTRY', 'Mail Entry',sys_user(),current_timestamp,sys_user(),current_timestamp);
INSERT INTO tbl_l_permission_type VALUES ('INTAKE_CAL', 'Intake Calendars',sys_user(),current_timestamp,sys_user(),current_timestamp);
INSERT INTO tbl_l_permission_type VALUES ('SHELTER_LOCKER', 'Shelter Locker System',sys_user(),current_timestamp,sys_user(),current_timestamp);
INSERT INTO tbl_l_permission_type VALUES ('AGENCY_PROFILE_REP', 'AGENCY Profile Report',sys_user(),current_timestamp,sys_user(),current_timestamp);
