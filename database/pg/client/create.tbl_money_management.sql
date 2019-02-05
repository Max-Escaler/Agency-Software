/*
Client ID
Start Date
End Date
Agency (defaults to DESC)
Voluntary or Protected Payee
Comments
*/

CREATE TABLE tbl_money_management (
	money_management_id		SERIAL PRIMARY KEY,
	client_id				INTEGER NOT NULL REFERENCES tbl_client ( client_id ),
	money_management_date		DATE NOT NULL,
	money_management_date_end	DATE CHECK (money_management_date_end >= money_management_date),
	payee_agency_code			VARCHAR(10) NOT NULL DEFAULT 'DESC' REFERENCES tbl_l_agency ( agency_code ),
	payee_type_code			VARCHAR(10) NOT NULL REFERENCES tbl_l_payee_type ( payee_type_code ),
	comments				TEXT,
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

CREATE VIEW money_management AS SELECT * FROM tbl_money_management WHERE NOT is_deleted;

CREATE INDEX index_tbl_money_management_client_id ON tbl_money_management ( client_id );
CREATE INDEX index_tbl_money_management_money_management_date ON tbl_money_management ( money_management_date );
CREATE INDEX index_tbl_money_management_dates ON tbl_money_management ( money_management_date, money_management_date_end );
CREATE INDEX index_tbl_money_management_agency_code ON tbl_money_management ( payee_agency_code );
