/* Values are:

Code
Description
Coding (we use for envelope coding, like stripe on the side, etc.)
Comment

*/

INSERT INTO tbl_l_response (response_code,description,response_coding,response_comment,added_by,added_at,changed_by,changed_at) VALUES ('011','Grant',NULL,NULL,sys_user(),current_timestamp,sys_user(),current_timestamp);
INSERT INTO tbl_l_response (response_code,description,response_coding,response_comment,added_by,added_at,changed_by,changed_at) VALUES ('080','In Lieu of Gift',NULL,NULL,sys_user(),current_timestamp,sys_user(),current_timestamp);
INSERT INTO tbl_l_response (response_code,description,response_coding,response_comment,added_by,added_at,changed_by,changed_at) VALUES ('085','In Honor/Memory of',NULL,NULL,sys_user(),current_timestamp,sys_user(),current_timestamp);
INSERT INTO tbl_l_response (response_code,description,response_coding,response_comment,added_by,added_at,changed_by,changed_at) VALUES ('086','Distribution of estate/will',NULL,NULL,sys_user(),current_timestamp,sys_user(),current_timestamp);
INSERT INTO tbl_l_response (response_code,description,response_coding,response_comment,added_by,added_at,changed_by,changed_at) VALUES ('090','Self-Starter',NULL,NULL,sys_user(),current_timestamp,sys_user(),current_timestamp);
INSERT INTO tbl_l_response (response_code,description,response_coding,response_comment,added_by,added_at,changed_by,changed_at) VALUES ('094','Network For Good / Online Gift',NULL,NULL,sys_user(),current_timestamp,sys_user(),current_timestamp);
INSERT INTO tbl_l_response (response_code,description,response_coding,response_comment,added_by,added_at,changed_by,changed_at) VALUES ('095','Board Pledge',NULL,NULL,sys_user(),current_timestamp,sys_user(),current_timestamp);
INSERT INTO tbl_l_response (response_code,description,response_coding,response_comment,added_by,added_at,changed_by,changed_at) VALUES ('097','Telephone Contribution',NULL,NULL,sys_user(),current_timestamp,sys_user(),current_timestamp);
INSERT INTO tbl_l_response (response_code,description,response_coding,response_comment,added_by,added_at,changed_by,changed_at) VALUES ('098','Mail--not donor envelope',NULL,NULL,sys_user(),current_timestamp,sys_user(),current_timestamp);
INSERT INTO tbl_l_response (response_code,description,response_coding,response_comment,added_by,added_at,changed_by,changed_at) VALUES ('099','Other',NULL,NULL,sys_user(),current_timestamp,sys_user(),current_timestamp);
INSERT INTO tbl_l_response (response_code,description,response_coding,response_comment,added_by,added_at,changed_by,changed_at) VALUES ('100','Direct Mail--Unknown',NULL,NULL,sys_user(),current_timestamp,sys_user(),current_timestamp);
/* Add Specific Direct Mail Campaigns here, or via AGENCY interface */
INSERT INTO tbl_l_response (response_code,description,response_coding,response_comment,added_by,added_at,changed_by,changed_at) VALUES ('101','Direct Mail--A specific campaign','Envelope coded with yellow stripe','Comments:  this went to new donors only',sys_user(),current_timestamp,sys_user(),current_timestamp);
/* Prospect Mailings */ 
INSERT INTO tbl_l_response (response_code,description,response_coding,response_comment,added_by,added_at,changed_by,changed_at) VALUES ('500','Prospect Mailings--general',NULL,NULL,sys_user(),current_timestamp,sys_user(),current_timestamp);
INSERT INTO tbl_l_response (response_code,description,response_coding,response_comment,added_by,added_at,changed_by,changed_at) VALUES ('501','Prospect Mailings--A specific one',NULL,NULL,sys_user(),current_timestamp,sys_user(),current_timestamp);
/* Matching Gifts */
INSERT INTO tbl_l_response (response_code,description,response_coding,response_comment,added_by,added_at,changed_by,changed_at) VALUES ('600','Matching Gift Program--General',NULL,NULL,sys_user(),current_timestamp,sys_user(),current_timestamp);
INSERT INTO tbl_l_response (response_code,description,response_coding,response_comment,added_by,added_at,changed_by,changed_at) VALUES ('601','Matching Gift--Specific Company',NULL,NULL,sys_user(),current_timestamp,sys_user(),current_timestamp);
INSERT INTO tbl_l_response (response_code,description,response_coding,response_comment,added_by,added_at,changed_by,changed_at) VALUES ('650','Matching Gift--Volunteer Time--General',NULL,NULL,sys_user(),current_timestamp,sys_user(),current_timestamp);
INSERT INTO tbl_l_response (response_code,description,response_coding,response_comment,added_by,added_at,changed_by,changed_at) VALUES ('651','Matching Gift--Volunteer Time--Specific Company',NULL,NULL,sys_user(),current_timestamp,sys_user(),current_timestamp);
/* Direct Workplace Campaigns */
INSERT INTO tbl_l_response (response_code,description,response_coding,response_comment,added_by,added_at,changed_by,changed_at) VALUES ('700','Workplace Campaigns--General',NULL,NULL,sys_user(),current_timestamp,sys_user(),current_timestamp);
INSERT INTO tbl_l_response (response_code,description,response_coding,response_comment,added_by,added_at,changed_by,changed_at) VALUES ('701','Workplace--Specific Workplace',NULL,NULL,sys_user(),current_timestamp,sys_user(),current_timestamp);

/* United Way / Other Combined or Indirect Fundraising Campaigns */
INSERT INTO tbl_l_response (response_code,description,response_coding,response_comment,added_by,added_at,changed_by,changed_at) VALUES ('800','Indirect Donation--i.e., United Way, et al.',NULL,NULL,sys_user(),current_timestamp,sys_user(),current_timestamp);
INSERT INTO tbl_l_response (response_code,description,response_coding,response_comment,added_by,added_at,changed_by,changed_at) VALUES ('810','United Way--General Campaign',NULL,NULL,sys_user(),current_timestamp,sys_user(),current_timestamp);
INSERT INTO tbl_l_response (response_code,description,response_coding,response_comment,added_by,added_at,changed_by,changed_at) VALUES ('814','United Way--Combined Federal Campaign',NULL,NULL,sys_user(),current_timestamp,sys_user(),current_timestamp);
