CREATE VIEW donor AS
SELECT
	donor_id,
	donor_name,
	donor_type_code,
	public_listing,
	www_url,
	dob,
	source,
	is_anonymous,
	skip_thanks,
	send_mail_code,
	ask_code,
	preferred_address_code,
	is_inactive,
	from_united_way,
	special_next_mail,
	scratch,
/*
	mip_export_donor_id,
	mip_export_session_id,
*/
	address_mail((SELECT address_id FROM address a WHERE a.donor_id = tbl_donor.donor_id
			ORDER BY tbl_donor.preferred_address_code = a.address_type_code DESC, address_id DESC LIMIT 1)),
	added_by,
	added_at,
	changed_by,
	changed_at,
	is_deleted,
	deleted_at,
	deleted_by,
	deleted_comment,
	sys_log
FROM tbl_donor WHERE NOT is_deleted;
