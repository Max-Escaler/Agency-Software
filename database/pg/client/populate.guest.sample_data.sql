-- Sample data for testing

INSERT INTO tbl_guest (name_last,name_first,client_id,dob,added_by,changed_by) VALUES ('Ride','Sally',NULL,'1985-01-14',sys_user(),sys_user());
INSERT INTO tbl_guest_identification (guest_id,identification_type_code,identification_expiration_date,added_by,changed_by) VALUES (1,'DRIVER','2015-01-01',sys_user(),sys_user());

INSERT INTO tbl_guest (name_last,name_first,client_id,dob,added_by,changed_by) VALUES ('Romney','Mitt',NULL,'1985-01-14',sys_user(),sys_user());
INSERT INTO tbl_guest_identification (guest_id,identification_type_code,identification_expiration_date,added_by,changed_by) VALUES (2,'DRIVER','2014-01-01',sys_user(),sys_user());

INSERT INTO tbl_guest (name_last,name_first,client_id,dob,added_by,changed_by) VALUES ('Parker','Pete',NULL,'1989-04-14',sys_user(),sys_user());

--These client IDs must match clients in your database, or change appropriately:
INSERT INTO tbl_guest_authorization (guest_id,client_id,guest_authorization_date,added_by,changed_by) VALUES (1,384,'2012-01-01',sys_user(),sys_user());
INSERT INTO tbl_guest_authorization (guest_id,client_id,guest_authorization_date,added_by,changed_by) VALUES (2,384,'2012-01-01',sys_user(),sys_user());
INSERT INTO tbl_guest_authorization (guest_id,client_id,guest_authorization_date,added_by,changed_by) VALUES (3,384,'2012-01-01',sys_user(),sys_user());
INSERT INTO tbl_guest_authorization (guest_id,client_id,guest_authorization_date,added_by,changed_by) VALUES (1,229,'2012-01-01',sys_user(),sys_user());
