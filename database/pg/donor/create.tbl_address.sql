CREATE TABLE tbl_address (
	address_id		SERIAL PRIMARY KEY,
	donor_id 		INTEGER NOT NULL REFERENCES tbl_donor (donor_id),
	address_date	DATE NOT NULL,
	address_date_end	DATE,
	address_obsolete_reason_code	VARCHAR(10) REFERENCES tbl_l_address_obsolete_reason ( address_obsolete_reason_code ),
	address_type_code	VARCHAR(10) NOT NULL REFERENCES tbl_l_address_type (address_type_code),
	exclude_from_mailing	BOOLEAN NOT NULL DEFAULT FALSE,
	name_prefix		VARCHAR(80),
	name_first		VARCHAR(80),
	name_middle		VARCHAR(80),	
	name_last		VARCHAR(80),
	name_suffix		VARCHAR(80),
	name_email		VARCHAR(80),
	name2_prefix	VARCHAR(80),
	name2_first		VARCHAR(80),
	name2_middle	VARCHAR(80),
	name2_last		VARCHAR(80),
	name2_suffix	VARCHAR(80),
	name2_email		VARCHAR(80),
	organization	VARCHAR(80),
	title			VARCHAR(80),
	address1		VARCHAR(80),
	address2		VARCHAR(80),
	city			VARCHAR(80),
	state_code		VARCHAR(3),
	zipcode			VARCHAR(10),
	salutation		VARCHAR(80),
	country			VARCHAR(80),
	phone_no		VARCHAR(80),
	send_mail_code	VARCHAR(10) REFERENCES tbl_l_send_mail (send_mail_code),
	latitude_longitude POINT CHECK (poly_contain_pt('((-90,-180),(-90,180),(90,180),(90,-180))'::polygon,latitude_longitude)),
	address_comment TEXT,
	added_by     INTEGER NOT NULL REFERENCES tbl_staff (staff_id),
	added_at     TIMESTAMP(0)     NOT NULL DEFAULT CURRENT_TIMESTAMP,
	changed_by     INTEGER NOT NULL  REFERENCES tbl_staff (staff_id),
	changed_at     TIMESTAMP(0)     NOT NULL DEFAULT CURRENT_TIMESTAMP,
	is_deleted    BOOLEAN NOT NULL DEFAULT FALSE,
	deleted_at   TIMESTAMP(0),
	deleted_by   INTEGER REFERENCES tbl_staff(staff_id),
	deleted_comment TEXT,
	sys_log TEXT

	CONSTRAINT address_obsolete_date CHECK (
		(address_date_end IS NOT NULL AND address_obsolete_reason_code IS NOT NULL)
			OR
		(address_date_end IS NULL AND address_obsolete_reason_code IS NULL)
	)
);

CREATE INDEX index_tbl_address_donor_id ON tbl_address ( donor_id );
