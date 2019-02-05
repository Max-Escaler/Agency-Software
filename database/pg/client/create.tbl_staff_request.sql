CREATE TABLE tbl_staff_request (
	staff_request_id			SERIAL PRIMARY KEY,

--new staff
	name_last       			VARCHAR(40),
	name_first      			VARCHAR(30),
	name_first_legal			VARCHAR(30),
	size_head       			FLOAT,
	gender_code              	VARCHAR(10) REFERENCES tbl_l_gender (gender_code),

--staff transfer
	is_transfer				BOOLEAN NOT NULL DEFAULT FALSE,
	staff_id				INTEGER REFERENCES tbl_staff ( staff_id ),
	prior_employee_code			VARCHAR(10) NOT NULL REFERENCES tbl_l_yes_no (yes_no_code),
	prior_staff_id				INTEGER,

	agency_program_code			VARCHAR(10) NOT NULL,
	agency_project_code			VARCHAR(10) NOT NULL,
	staff_position_code		VARCHAR(10) NOT NULL REFERENCES tbl_l_staff_position(staff_position_code),
	agency_facility_code      	VARCHAR(10) NOT NULL REFERENCES tbl_l_agency_facility(agency_facility_code),
	additional_locations		TEXT,
	staff_shift_code         	VARCHAR(10) NOT NULL REFERENCES tbl_l_staff_shift(staff_shift_code),
	agency_staff_type_code    	VARCHAR(10) NOT NULL REFERENCES tbl_l_agency_staff_type(agency_staff_type_code),
	staff_employment_status_code 	VARCHAR(10) NOT NULL REFERENCES tbl_l_staff_employment_status(staff_employment_status_code),
	day_off_1_code			INTEGER NOT NULL REFERENCES tbl_l_day_of_week (day_of_week_code) DEFAULT 6,
	day_off_2_code			INTEGER NOT NULL REFERENCES tbl_l_day_of_week (day_of_week_code) DEFAULT 0,
	starts_on          		DATE NOT NULL,
	request_permissions		TEXT,
	request_building_access		TEXT,
	replacing_staff			INTEGER REFERENCES tbl_staff ( staff_id ),
	supervised_by			INTEGER REFERENCES tbl_staff ( staff_id ),

	phone_extension			VARCHAR(5),
	voice_mail_number			VARCHAR(14) CHECK (voice_mail_number ~ '\\([0-9]{3}\\) [0-9]{3}-[0-9]{4}$'),
	voice_mail_extension		VARCHAR(5),

	staff_pay_step_code		VARCHAR(10) NOT NULL REFERENCES tbl_l_staff_pay_step ( staff_pay_step_code ),
	home_address			TEXT,
	home_phone				VARCHAR(14) CHECK (home_phone ~ '\\([0-9]{3}\\) [0-9]{3}-[0-9]{4}$'),
	comment				TEXT,
	staff_request_status_code	VARCHAR(10) REFERENCES tbl_l_staff_request_status ( staff_request_status_code ),
	hr_request_status_code		VARCHAR(10) REFERENCES tbl_l_staff_request_status ( staff_request_status_code ),
	--system fields
	added_by				INTEGER NOT NULL REFERENCES tbl_staff (staff_id),
	added_at				TIMESTAMP(0)     NOT NULL DEFAULT CURRENT_TIMESTAMP,
	changed_by				INTEGER NOT NULL  REFERENCES tbl_staff (staff_id),
	changed_at				TIMESTAMP(0)     NOT NULL DEFAULT CURRENT_TIMESTAMP,
	is_deleted				BOOLEAN NOT NULL DEFAULT FALSE,
	deleted_at				TIMESTAMP(0),
	deleted_by				INTEGER REFERENCES tbl_staff(staff_id),
	deleted_comment			TEXT,
	sys_log				TEXT

	CONSTRAINT new_staff_or_transfer CHECK ( (is_transfer IS TRUE AND staff_id IS NOT NULL AND name_last IS NULL 
									AND name_first IS NULL AND size_head IS NULL
									AND home_phone IS NULL AND home_address IS NULL AND gender_code IS NULL)
								OR (is_transfer IS FALSE AND staff_id IS NULL AND name_last IS NOT NULL 
									AND name_first IS NOT NULL AND gender_code IS NOT NULL)
	)
);

CREATE VIEW staff_request AS SELECT * FROM tbl_staff_request WHERE NOT is_deleted;

CREATE OR REPLACE FUNCTION staff_request_sensitive_data_trigger() RETURNS trigger AS $$
BEGIN

	IF NEW.hr_request_status_code IS NOT NULL
		AND (OLD.hr_request_status_code IS NULL) THEN

		RAISE NOTICE 'Address and Phone will be blanked for privacy on staff_request_id %', NEW.staff_request_id;

		NEW.home_phone   := NULL;
		NEW.home_address := NULL;

	END IF;

	RETURN NEW;

END;$$ LANGUAGE plpgsql;

CREATE OR REPLACE FUNCTION staff_request_clear_log_trigger() RETURNS trigger AS $$
BEGIN
	IF NEW.hr_request_status_code IS NOT NULL
		AND (OLD.hr_request_status_code IS NULL) THEN

		UPDATE tbl_staff_request_log SET home_phone=NULL,home_address = NULL
			WHERE staff_request_id = NEW.staff_request_id	
				AND (home_phone IS NOT NULL OR home_address IS NOT NULL);

	END IF;

	RETURN NEW;

END;$$ LANGUAGE plpgsql;

CREATE TRIGGER tbl_staff_request_sensitive_data BEFORE UPDATE ON tbl_staff_request
	FOR EACH ROW EXECUTE PROCEDURE staff_request_sensitive_data_trigger();

CREATE TRIGGER ztbl_staff_request_clear_log_data AFTER UPDATE ON tbl_staff_request
	FOR EACH ROW EXECUTE PROCEDURE staff_request_clear_log_trigger();

COMMENT ON TRIGGER ztbl_staff_request_clear_log_data ON tbl_staff_request IS 'The "z" at the beginning of the name is needed to insure that this trigger fires after the table logging trigger';

