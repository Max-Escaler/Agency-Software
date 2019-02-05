INSERT INTO tbl_l_restriction VALUES ('001','Unrestricted',sys_user(),current_timestamp,sys_user(),current_timestamp);
INSERT INTO tbl_l_restriction VALUES ('002','Restricted--Miscellaneous',sys_user(),current_timestamp,sys_user(),current_timestamp);
INSERT INTO tbl_l_restriction VALUES ('010','Direct Client Assistance',sys_user(),current_timestamp,sys_user(),current_timestamp);
INSERT INTO tbl_l_restriction VALUES ('100','Supportive Housing Program',sys_user(),current_timestamp,sys_user(),current_timestamp);
INSERT INTO tbl_l_restriction VALUES ('200','Shelter Program',sys_user(),current_timestamp,sys_user(),current_timestamp);
INSERT INTO tbl_l_restriction VALUES ('300','Mental Health Program',sys_user(),current_timestamp,sys_user(),current_timestamp);
INSERT INTO tbl_l_restriction VALUES ('400','Supplies',sys_user(),current_timestamp,sys_user(),current_timestamp);
INSERT INTO tbl_l_restriction VALUES ('410','Supplies--Food',sys_user(),current_timestamp,sys_user(),current_timestamp);
INSERT INTO tbl_l_restriction VALUES ('600','Chemical Dependency Treatment Program',sys_user(),current_timestamp,sys_user(),current_timestamp);
/* 
 * Keep all your capital/equipment codes in the 900s.
 * It is easy to exclude/group them that way, and some
 * pre-built reports already do this.
 *
 */

INSERT INTO tbl_l_restriction VALUES ('900','Capital/Equipment--General',sys_user(),current_timestamp,sys_user(),current_timestamp);
INSERT INTO tbl_l_restriction VALUES ('910','Capital/Equipment--Union',sys_user(),current_timestamp,sys_user(),current_timestamp);
