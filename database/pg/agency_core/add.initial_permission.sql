INSERT INTO tbl_permission(
    permission_type_code,
    staff_id,
    permission_date,
    permission_read,
    permission_write,
    permission_super,
    added_by,
    changed_by)
/* grants system admin permissions to user created above */
VALUES ('SUPER_USER',2,CURRENT_DATE,true,true,true,1,1);


