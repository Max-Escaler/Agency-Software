CREATE VIEW guest AS SELECT 

	guest_id,
	name_last,
	name_first,
	name_middle,
	name_alias,
	client_id,
	CASE WHEN client_id IS NOT NULL THEN
		client_name(client_id)
	ELSE
		name_first
		|| COALESCE(' ' || name_middle,'')
		|| ' '
		|| name_last
	END AS name_full,
    dob,
    guest_photo,
	CASE
		WHEN identification_type_code IS NULL
			AND identification_number IS NULL
			AND identification_expiration_date IS NULL
		THEN (SELECT identification_type_code FROM guest_identification
			WHERE guest_id=tbl_guest.guest_id ORDER BY identification_expiration_date DESC limit 1 )	
		ELSE identification_type_code
	END AS identification_type_code,
	CASE
		WHEN identification_type_code IS NULL
			AND identification_number IS NULL
			AND identification_expiration_date IS NULL
		THEN (SELECT identification_number FROM guest_identification
			WHERE guest_id=tbl_guest.guest_id ORDER BY identification_expiration_date DESC limit 1 )	
		ELSE identification_number
	END AS identification_number,
	CASE
		WHEN identification_type_code IS NULL
			AND identification_number IS NULL
			AND identification_expiration_date IS NULL
		THEN (SELECT identification_expiration_date FROM guest_identification
			WHERE guest_id=tbl_guest.guest_id ORDER BY identification_expiration_date DESC limit 1 )	
		ELSE identification_expiration_date
	END AS identification_expiration_date,
	(SELECT guest_identification_id FROM guest_identification_current WHERE guest_id=tbl_guest.guest_id LIMIT 1)
	AS identification_status,
	comment,

    --system fields
    added_by,
    added_at,
    changed_by,
    changed_at,
    is_deleted,
    deleted_at,
    deleted_by,
    deleted_comment,
    sys_log

FROM tbl_guest WHERE NOT is_deleted;
