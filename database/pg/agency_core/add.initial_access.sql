/*
 * Defines remote/local access. The default is to only define localhost
 * connections as local. If you're running a separate webserver from your
 * database server, uncomment the section below to define additional access.
 *
 * You may also want to define the true IP address of the machine as local so
 * you may refer to the db server by hostname instead of IP in agency_config_local.php
 */

INSERT INTO tbl_db_access (access_ip,
	is_internal,
	description,
	added_by,
	changed_by
) VALUES (

	'127.0.0.1',
	true,
	'Localhost',
        1,
	1),
        (

        '::1',
        true,
        'Localhost',
        1,
        1)

/*
--edit and uncomment this section to define additional access
,(
	'0.0.0.0', --edit to be a real IP address
	true,      --define as local. Change to "false" to define as remote
	1,
        1)


*/
;
