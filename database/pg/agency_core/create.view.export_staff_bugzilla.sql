/*
 * mysql> describe profiles;
 * +----------------+--------------+------+-----+---------+----------------+
 * | Field          | Type         | Null | Key | Default | Extra          |
 * +----------------+--------------+------+-----+---------+----------------+
 * | userid         | mediumint(9) | NO   | PRI | NULL    | auto_increment |
 * | login_name     | varchar(255) | NO   | UNI |         |                |
 * | cryptpassword  | varchar(128) | YES  |     | NULL    |                |
 * | realname       | varchar(255) | YES  |     | NULL    |                |
 * | disabledtext   | mediumtext   | NO   |     |         |                |
 * | mybugslink     | tinyint(4)   | NO   |     | 1       |                |
 * | refreshed_when | datetime     | NO   |     |         |                |
 * | extern_id      | varchar(64)  | YES  |     | NULL    |                |
 * +----------------+--------------+------+-----+---------+----------------+
 * 8 rows in set (0.00 sec)
 */

CREATE OR REPLACE VIEW export_staff_bugzilla AS
SELECT
	username_unix::varchar(255) AS login_name,
	md5(staff_password_md5)::varchar(128) AS cryptpassword,
	staff_name(staff_id) AS realname
FROM staff
	LEFT JOIN staff_password USING (staff_id)
WHERE is_active AND is_human_staff(staff_id);
