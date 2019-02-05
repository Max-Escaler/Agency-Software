/*
 *
 * Creates a 'system user' and a 'super user'
 *
 * Edit passwords and names accordingly
 *
 */

INSERT INTO tbl_staff(
    username,
    name_last,
    name_first,
    is_active,
    gender_code,
    login_allowed,
    added_by,
    changed_by)

/* This user is meant for automated tasks (it shouldn't need to be changed), and won't be allowed to log in via the web interface */
SELECT 'sys_user', --username
	'USER', --last name
	'SYSTEM', -- first name
	true, --is_active
	'UNKNOWN', --gender_code
	false, --login_allowed
	1,    --added_by
	1    --changed_by

UNION ALL

/* This is the system administator account, and should be edited to reflect real values */
/* Like any staff record, it can also be edited in AGENCY after installation */

SELECT 
	'super_user', --change to desired username
	'USER',       --change to sys admin's last name
	'SUPER',      --change to sys admin's first name
	true,   
	'UNKNOWN',    --change to sys admin's gender (complete list in create.l_gender.sql) or FEMALEE for Female, MALE for Male
	true,
	1,
	1
;

-- This may need adjusting for different encryption methods
INSERT INTO tbl_staff_password (staff_id,staff_password_md5,added_by,changed_by) 
	VALUES (2,md5('PASSWORD' /*CHANGE THIS TO THE DESIRED PASSWORD */),1,1);


-- This file shouldn't need to be edited below here. --

/* system user function to return sys_user's id */

-- If you do a clean install, the System User will have an ID of 1.
-- This is what is assumed and set automatically.
-- If for some reason your system user has a different ID,
-- change this function to match.

CREATE OR REPLACE FUNCTION sys_user() RETURNS INTEGER AS '
BEGIN
	RETURN 1;
END;' LANGUAGE 'plpgsql';
