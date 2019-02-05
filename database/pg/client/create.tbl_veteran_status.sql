CREATE TABLE tbl_veteran_status (
	veteran_status_id	SERIAL PRIMARY KEY,
	client_id			INTEGER NOT NULL REFERENCES tbl_client ( client_id ),
	veteran_status_date	DATE NOT NULL,
	veteran_status_code	VARCHAR(10) REFERENCES tbl_l_veteran_status ( veteran_status_code ),
	has_va_benefits		BOOLEAN NOT NULL,
	has_service_disability	BOOLEAN NOT NULL,
	has_military_pension	BOOLEAN NOT NULL,
	has_received_va_hospital_care	BOOLEAN NOT NULL,
	added_by			INTEGER NOT NULL REFERENCES tbl_staff (staff_id),
	added_at			TIMESTAMP(0)     NOT NULL DEFAULT CURRENT_TIMESTAMP,
	changed_by			INTEGER NOT NULL  REFERENCES tbl_staff (staff_id),
	changed_at			TIMESTAMP(0)     NOT NULL DEFAULT CURRENT_TIMESTAMP,
	is_deleted			BOOLEAN NOT NULL DEFAULT FALSE,
	deleted_at			TIMESTAMP(0),
	deleted_by			INTEGER REFERENCES tbl_staff(staff_id),
	deleted_comment		TEXT,
	sys_log			TEXT

	CONSTRAINT	non_conflicting_data CHECK (
		(veteran_status_code IN ('0','5') AND has_va_benefits IS FALSE AND has_service_disability IS FALSE
			AND has_military_pension IS FALSE AND has_received_va_hospital_care IS FALSE)
		OR veteran_status_code NOT IN ('0','5')
	)
);