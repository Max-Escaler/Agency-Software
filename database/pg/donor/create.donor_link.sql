CREATE TABLE tbl_donor_link (

	donor_link_id	SERIAL PRIMARY KEY,
	donor_id		INTEGER NOT NULL REFERENCES tbl_donor (donor_id),
	donor_2_id		INTEGER NOT NULL REFERENCES tbl_donor (donor_id),
	donor_link_type_code	VARCHAR(10), -- REFERENCES tbl_l_donor_link_type (donor_link_type_code),
	added_by     INTEGER NOT NULL REFERENCES tbl_staff (staff_id),
	added_at     TIMESTAMP(0)     NOT NULL DEFAULT CURRENT_TIMESTAMP,
	changed_by     INTEGER NOT NULL  REFERENCES tbl_staff (staff_id),
	changed_at     TIMESTAMP(0)     NOT NULL DEFAULT CURRENT_TIMESTAMP,
	is_deleted    BOOLEAN NOT NULL DEFAULT FALSE,
	deleted_at   TIMESTAMP(0),
	deleted_by   INTEGER REFERENCES tbl_staff(staff_id),
	deleted_comment TEXT,
	sys_log TEXT

);

CREATE VIEW donor_link AS 
SELECT 
		donor_link_id,
		donor_id,
		donor_2_id,
		donor_link_type_code,
		added_by,
		added_at,
		changed_by,
		changed_at,
		is_deleted,
		deleted_at,
		deleted_by,
		deleted_comment,
		sys_log

FROM tbl_donor_link 
WHERE NOT is_deleted
UNION
SELECT 
		donor_link_id,
		donor_2_id,
		donor_id,
		donor_link_type_code,
		added_by,
		added_at,
		changed_by,
		changed_at,
		is_deleted,
		deleted_at,
		deleted_by,
		deleted_comment,
		sys_log

FROM tbl_donor_link 
WHERE NOT is_deleted
;
