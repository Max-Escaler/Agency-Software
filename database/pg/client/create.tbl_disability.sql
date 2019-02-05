CREATE TABLE tbl_disability (
	disability_id		SERIAL PRIMARY KEY NOT NULL,
	client_id     		INTEGER NOT NULL,
	disability_code     	VARCHAR(10) NOT NULL REFERENCES tbl_l_disability (disability_code),
	disability_date		DATE NOT NULL,
	disability_date_end	DATE,
	comment			TEXT,
	source			VARCHAR(10) NOT NULL DEFAULT 'AGENCY',
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

CREATE INDEX index_tbl_disability_client_id ON tbl_disability ( client_id );
CREATE INDEX index_tbl_disability_disability_date ON tbl_disability ( disability_date );
CREATE INDEX index_tbl_disability_client_id_disability_date ON tbl_disability ( client_id,disability_date );

CREATE VIEW disability AS SELECT * FROM tbl_disability WHERE NOT is_deleted;
