CREATE OR REPLACE VIEW jail_charge AS 

SELECT  tbl_jail_charge.jail_charge_id,
	jail.client_id,
	tbl_jail_charge.jail_id,
	tbl_jail_charge.ba_number,
	tbl_jail_charge.cause_number,
	tbl_jail_charge.court,
	tbl_jail_charge.rcw,
	tbl_jail_charge.release_reason,
	tbl_jail_charge.bail,
	tbl_jail_charge.charge,
	tbl_jail_charge.added_by,
	tbl_jail_charge.added_at,
	tbl_jail_charge.changed_by,
	tbl_jail_charge.changed_at,
	tbl_jail_charge.is_deleted,
	tbl_jail_charge.deleted_at,
	tbl_jail_charge.deleted_by,
	tbl_jail_charge.deleted_comment,
	tbl_jail_charge.sys_log	
FROM tbl_jail_charge 
	LEFT JOIN jail USING (jail_id)
WHERE NOT tbl_jail_charge.is_deleted;