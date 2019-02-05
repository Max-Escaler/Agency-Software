CREATE TABLE tbl_donor (
	donor_id			SERIAL PRIMARY KEY,
	donor_name 			VARCHAR(80) NOT NULL,
	donor_type_code		VARCHAR(10) NOT NULL REFERENCES tbl_l_donor_type (donor_type_code),
	public_listing  		VARCHAR(80), -- was ar_listing
	www_url			VARCHAR(80),
	dob				DATE,
	source			VARCHAR(80),
	is_anonymous		BOOLEAN NOT NULL DEFAULT FALSE,
	skip_thanks			BOOLEAN NOT NULL DEFAULT FALSE,
	send_mail_code		VARCHAR(10) REFERENCES tbl_l_send_mail (send_mail_code),
	ask_code			VARCHAR(10) REFERENCES tbl_l_ask (ask_code),
	preferred_address_code	VARCHAR(10) REFERENCES tbl_l_address_type (address_type_code ) NOT NULL,
	is_inactive			BOOLEAN NOT NULL DEFAULT FALSE,
	from_united_way		BOOLEAN NOT NULL DEFAULT FALSE,
	special_next_mail		BOOLEAN NOT NULL DEFAULT FALSE,
	scratch			VARCHAR(20),
/*
Used by DESC to export gifts to MIP:

	mip_export_donor_id	VARCHAR(20),
	mip_export_session_id	VARCHAR(13) REFERENCES tbl_mip_export_session ( mip_export_session_id )
		CHECK (mip_export_session_id ~ '^CX[0-9]{6}FDD[0-9]{2}$'),
*/
	--system fields
	added_by			INTEGER NOT NULL REFERENCES tbl_staff (staff_id),
	added_at			TIMESTAMP(0)     NOT NULL DEFAULT CURRENT_TIMESTAMP,
	changed_by			INTEGER NOT NULL  REFERENCES tbl_staff (staff_id),
	changed_at			TIMESTAMP(0)     NOT NULL DEFAULT CURRENT_TIMESTAMP,
	is_deleted			BOOLEAN NOT NULL DEFAULT FALSE,
	deleted_at			TIMESTAMP(0) CHECK ((NOT is_deleted AND deleted_at IS NULL) OR (is_deleted AND deleted_at IS NOT NULL)),
	deleted_by			INTEGER REFERENCES tbl_staff(staff_id)
						  CHECK ((NOT is_deleted AND deleted_by IS NULL) OR (is_deleted AND deleted_by IS NOT NULL)),
	deleted_comment		TEXT,
	sys_log			TEXT
);

CREATE INDEX index_tbl_donor_donor_name ON tbl_donor(donor_name);
CREATE INDEX index_tbl_donor_donor_type_code ON tbl_donor(donor_type_code);
CREATE INDEX index_tbl_donor_preferred_address_code ON tbl_donor(preferred_address_code);
