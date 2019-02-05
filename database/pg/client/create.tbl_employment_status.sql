CREATE TABLE tbl_employment_status (
	employment_status_id		SERIAL PRIMARY KEY,
	client_id				INTEGER NOT NULL REFERENCES tbl_client(client_id),
	application_date			DATE_PAST,
	application_status_code		VARCHAR(10) REFERENCES tbl_l_employment_application_status,
	employment_date			DATE CHECK (employment_date >= application_date),
	employment_status_code		VARCHAR(10) NOT NULL REFERENCES tbl_l_employment_status (employment_status_code),
	employment_level_code 		VARCHAR(10) NOT NULL REFERENCES tbl_l_employment_level (employment_level_code),
	employment_description 		VARCHAR(70),
	employer_name 			VARCHAR(100),
	employment_date_end		DATE CHECK (employment_date_end >= employment_date),
	employment_termination_code	VARCHAR(10) REFERENCES tbl_l_employment_termination ( employment_termination_code ),
	employment_termination_code_other	VARCHAR(100),
	comment				TEXT,
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

	CONSTRAINT no_useless_data CHECK ( (employment_status_code NOT IN ('UNEMPLOYED','UNKNOWN') AND
							(employer_name IS NOT NULL OR comment IS NOT NULL OR employment_description IS NOT NULL))
							OR (employment_status_code IN ('UNEMPLOYED','UNKNOWN')))
	CONSTRAINT termination_check CHECK ( (employment_date_end IS NULL AND employment_termination_code IS NULL)
							OR (employment_date_end IS NOT NULL AND employment_termination_code IS NOT NULL) )
	CONSTRAINT application_check CHECK ( (application_date IS NULL AND application_status_code IS NULL)
							OR (application_date IS NOT NULL AND application_status_code IS NOT NULL) )
	CONSTRAINT one_date_check CHECK (application_date IS NOT NULL OR employment_date IS NOT NULL)
);

CREATE VIEW employment_status AS 
SELECT * FROM tbl_employment_status WHERE NOT is_deleted;

CREATE INDEX index_tbl_employment_status_client_id ON tbl_employment_status ( client_id );
CREATE INDEX index_tbl_employment_status_client_id_date ON tbl_employment_status ( client_id,application_date );
CREATE INDEX index_tbl_employment_status_application_date ON tbl_employment_status ( application_date );
