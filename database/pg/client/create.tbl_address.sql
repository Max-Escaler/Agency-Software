CREATE TABLE tbl_address (
	address_id		SERIAL PRIMARY KEY,
	client_id 		INTEGER REFERENCES tbl_client (client_id),
	staff_id 		INTEGER REFERENCES tbl_staff (staff_id),
	address_date	DATE NOT NULL,
	address_date_end	DATE,
	address1		VARCHAR(80),
	address2		VARCHAR(80),
	city			VARCHAR(80),
	state_code		VARCHAR(3),
	zipcode			VARCHAR(10),
	address_email	VARCHAR(80),
	address_email2	VARCHAR(80),
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

	CONSTRAINT address_has_exactly_one_id CHECK (COALESCE(staff_id,client_id) IS NOT NULL AND NOT (staff_id is NOT NULL AND client_id IS NOT NULL))
);

CREATE INDEX index_tbl_address_client_id ON tbl_address ( client_id );
CREATE INDEX index_tbl_address_staff_id ON tbl_address ( staff_id );

