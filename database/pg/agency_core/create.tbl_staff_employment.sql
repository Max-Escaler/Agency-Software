CREATE TABLE tbl_staff_employment (
	staff_employment_id		SERIAL PRIMARY KEY,
	staff_id				INTEGER NOT NULL REFERENCES tbl_staff ( staff_id ),
	agency_program_code			VARCHAR(10) REFERENCES tbl_l_agency_program,--NOT NULL,
	agency_project_code			VARCHAR(10) REFERENCES tbl_l_agency_project,--NOT NULL,
	staff_position_code		VARCHAR(20) REFERENCES tbl_l_staff_position(staff_position_code) NOT NULL,
	staff_title			TEXT,
	agency_facility_code		VARCHAR(10) REFERENCES tbl_l_agency_facility(agency_facility_code),
	staff_shift_code			VARCHAR(10) REFERENCES tbl_l_staff_shift(staff_shift_code),
	agency_staff_type_code		VARCHAR(10) REFERENCES tbl_l_agency_staff_type(agency_staff_type_code),
	staff_employment_status_code	VARCHAR(10) REFERENCES tbl_l_staff_employment_status(staff_employment_status_code),
	day_off_1_code			INTEGER NOT NULL REFERENCES tbl_l_day_of_week (day_of_week_code) DEFAULT 6,
	day_off_2_code			INTEGER NOT NULL REFERENCES tbl_l_day_of_week (day_of_week_code) DEFAULT 0,
	hired_on				DATE NOT NULL,
	terminated_on			DATE CHECK (terminated_on >= hired_on),
	supervised_by			INTEGER REFERENCES tbl_staff ( staff_id ),
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
);

CREATE INDEX index_tbl_staff_employment_staff_id ON tbl_staff_employment ( staff_id );
CREATE INDEX index_tbl_staff_employment_hired_on ON tbl_staff_employment ( hired_on );
CREATE INDEX index_tbl_staff_employment_dates ON tbl_staff_employment ( hired_on, terminated_on );
CREATE INDEX index_tbl_staff_employment_terminated_on ON tbl_staff_employment ( terminated_on );
CREATE INDEX index_tbl_staff_employment_agency_project_code ON tbl_staff_employment ( agency_project_code );
CREATE INDEX index_tbl_staff_employment_agency_program_code ON tbl_staff_employment ( agency_program_code );
CREATE INDEX index_tbl_staff_employment_supervised_by ON tbl_staff_employment ( supervised_by );
