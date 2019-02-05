CREATE TABLE tbl_ethnicity (
	ethnicity_id		SERIAL PRIMARY KEY NOT NULL,
	client_id     		INTEGER NOT NULL,
	ethnicity_code     	VARCHAR(10) NOT NULL REFERENCES tbl_l_ethnicity (ethnicity_code) CHECK (ethnicity_code<>'12'), /* no multi-ethnic */
	ethnicity_date		DATE NOT NULL,
	ethnicity_date_end	DATE,
	CONSTRAINT valid_date_range CHECK (COALESCE(ethnicity_date_end,ethnicity_date) >= ethnicity_date),
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

CREATE INDEX index_tbl_ethnicity_client_id ON tbl_ethnicity ( client_id );
CREATE INDEX index_tbl_ethnicity_ethnicity_date ON tbl_ethnicity ( ethnicity_date );
CREATE INDEX index_tbl_ethnicity_client_id_ethnicity_date ON tbl_ethnicity ( client_id,ethnicity_date );

CREATE VIEW ethnicity AS SELECT * FROM tbl_ethnicity WHERE NOT is_deleted;
