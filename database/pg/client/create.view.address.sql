CREATE VIEW address AS (
	SELECT address_id,
			staff_id,
			client_id,
			address_date,
			address_date_end,
			address1,
			address2,
			city,
			state_code,
			zipcode,
			address_email,
			address_email2,
			added_by,
			added_at,
			changed_by,
			changed_at,
			is_deleted,
			deleted_at,
			deleted_by,
			deleted_comment,
			sys_log
	FROM tbl_address
	WHERE NOT is_deleted);

CREATE VIEW address_current AS (SELECT * FROM address WHERE COALESCE(address_date_end,current_date) >= current_date);

CREATE VIEW address_client AS (
	SELECT address_id,
			client_id,
			address_date,
			address_date_end,
			address1,
			address2,
			city,
			state_code,
			zipcode,
			address_email,
			address_email2,
			added_by,
			added_at,
			changed_by,
			changed_at,
			is_deleted,
			deleted_at,
			deleted_by,
			deleted_comment,
			sys_log
	FROM address
	WHERE client_id IS NOT NULL

/* Commenting out union with residence own/l_housing_project,
   which doesn't seem to have address info.
*/

/*
UNION
	SELECT NULL AS address_id,
		client_id,
		residence_date AS address_date,
		residence_date_end AS address_date_end,
		address1,
		address2,
		city,
		state_code,
		zipcode,
		NULL as address_email,
		NULL as address_email2,
		ro.added_by,
		ro.added_at,
		ro.changed_by,
		ro.changed_at,
		ro.is_deleted,
		ro.deleted_at,
		ro.deleted_by,
		ro.deleted_comment,
		ro.sys_log
	FROM residence_own ro
	LEFT JOIN l_housing_project USING (housing_project_code)
	WHERE COALESCE(residence_date_end,current_date) >= current_date
*/
);

CREATE VIEW address_client_current AS (SELECT * FROM address_client WHERE COALESCE(address_date_end,current_date) >= current_date);

CREATE VIEW address_staff AS (
	SELECT address_id,
			staff_id,
			address_date,
			address_date_end,
			address1,
			address2,
			city,
			state_code,
			zipcode,
			address_email,
			address_email2,
			added_by,
			added_at,
			changed_by,
			changed_at,
			is_deleted,
			deleted_at,
			deleted_by,
			deleted_comment,
			sys_log
	FROM address
	WHERE staff_id IS NOT NULL);

CREATE VIEW address_staff_current AS (SELECT * FROM address_staff WHERE COALESCE(address_date_end,current_date) >= current_date);

