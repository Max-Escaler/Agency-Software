CREATE TABLE tbl_gift_cash (
	gift_cash_id		SERIAL PRIMARY KEY,
	donor_id			INTEGER NOT NULL REFERENCES tbl_donor (donor_id),
	gift_cash_amount		DECIMAL(12,2), -- can be null for UWAY gifts
	received_date		DATE NOT NULL CHECK (received_date <= CURRENT_DATE),
	gift_cash_date		DATE CHECK (gift_cash_date <= CURRENT_DATE),
	gift_cash_form_code	VARCHAR(10) REFERENCES tbl_l_gift_cash_form (gift_cash_form_code),
	reference_no		VARCHAR(16),
	response_code		VARCHAR(10) REFERENCES tbl_l_response (response_code),
	restriction_code		VARCHAR(10) REFERENCES tbl_l_restriction (restriction_code) NOT NULL,
	skip_thanks			BOOLEAN,
	gift_cash_comment		TEXT,
	expiration			VARCHAR(5),
	authorization_no		VARCHAR(8),
	is_anonymous		BOOLEAN,
	contract_code		INTEGER REFERENCES tbl_l_contract ( contract_code ),
/*
	mip_export_session_id	VARCHAR(13) REFERENCES tbl_mip_export_session ( mip_export_session_id )
		CHECK (mip_export_session_id ~ '^CX[0-9]{6}FDG[0-9]{2}$'),
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

CREATE INDEX index_tbl_gift_cash_donor_id ON tbl_gift_cash ( donor_id );
CREATE INDEX index_tbl_gift_cash_gift_cash_date ON tbl_gift_cash ( gift_cash_date );
/*
CREATE INDEX index_tbl_gift_cash_mip_export_session_id ON tbl_gift_cash ( mip_export_session_id );
*/

COMMENT ON COLUMN tbl_gift_cash.contract_code IS 'Use this field only to override default 117 code';
