CREATE TABLE tbl_residence_other (
	residence_other_id SERIAL PRIMARY KEY,
	client_id INTEGER NOT NULL REFERENCES tbl_client (client_id),
	facility_code VARCHAR(10) NOT NULL REFERENCES tbl_l_facility (facility_code),
	geography_code VARCHAR(10) NOT NULL REFERENCES tbl_l_geography (geography_code),
	geography_detail_code INTEGER REFERENCES tbl_l_geography_detail (geography_detail_code),
	city VARCHAR(30),
	state_code VARCHAR(2) REFERENCES tbl_l_state (state_code),
	zipcode VARCHAR(5),
	residence_date DATE NOT NULL,
	residence_date_accuracy VARCHAR(10) NOT NULL REFERENCES tbl_l_accuracy (accuracy_code),
	residence_date_end DATE, -- NULL is OK, for current residence
	residence_date_end_accuracy VARCHAR(10) REFERENCES tbl_l_accuracy (accuracy_code),
	moved_from_code VARCHAR(10) NOT NULL REFERENCES tbl_l_facility (facility_code),
	moved_to_code	VARCHAR(10) REFERENCES tbl_l_facility (facility_code),
	departure_type_code	VARCHAR(10) REFERENCES tbl_l_departure_type (departure_type_code),
	departure_reason_code	VARCHAR(10) REFERENCES tbl_l_departure_reason (departure_reason_code),
	incentive_sent_date DATE,
	incentive_sent_by INTEGER REFERENCES tbl_staff (staff_id),
	verified_method VARCHAR(10) REFERENCES tbl_l_contact_type (contact_type_code),
	verified_date DATE,
	verified_by INTEGER REFERENCES tbl_staff (staff_id),	
	comment	TEXT,
	--system fields
	added_by INTEGER NOT NULL REFERENCES tbl_staff (staff_id),
	added_at TIMESTAMP(0) NOT NULL DEFAULT current_timestamp,
	changed_by INTEGER NOT NULL REFERENCES tbl_staff (staff_id),
	changed_at TIMESTAMP(0) NOT NULL DEFAULT current_timestamp,
	is_deleted BOOLEAN DEFAULT false,
	deleted_at	TIMESTAMP(0),
	deleted_by	INTEGER REFERENCES tbl_staff (staff_id),
	deleted_comment	TEXT,
	sys_log	TEXT

	CONSTRAINT date_sanity CHECK (residence_date_end IS NULL OR residence_date <= residence_date_end )
	CONSTRAINT date_sanity_incentive CHECK (incentive_sent_date IS NULL OR incentive_sent_date >= residence_date)
	CONSTRAINT date_sanity_verified CHECK (verified_date IS NULL OR verified_date >= residence_date)
	CONSTRAINT zipcode CHECK (zipcode IS NULL OR zipcode='' OR zipcode ~ '[0-9]{5}')
	CONSTRAINT not_own_housing CHECK (facility_code NOT IN ('ADD','OWN','PROJECTS','HERE'))
	CONSTRAINT moveout_info CHECK ( 
			 (residence_date_end IS NOT NULL AND
			 moved_to_code IS NOT NULL
			 AND departure_type_code IS NOT NULL
			 AND departure_reason_code IS NOT NULL)
			 OR 
			 (residence_date_end IS NULL AND
			 moved_to_code IS NULL
			 AND departure_type_code IS NULL
			 AND departure_reason_code IS NULL)
	)
	CONSTRAINT incentive_info CHECK (
			(incentive_sent_date IS NULL 
			AND incentive_sent_by IS NULL) 
			OR 
			(incentive_sent_date IS NOT NULL 
			AND incentive_sent_by IS NOT NULL)
	)
	CONSTRAINT verified_info CHECK (
			(verified_method IS NULL
			AND verified_date IS NULL
			AND verified_by IS NULL)
			OR
			(verified_method IS NOT NULL
			AND verified_date IS NOT NULL
			AND verified_by IS NOT NULL)
	)
);

CREATE OR REPLACE VIEW residence_other AS SELECT * FROM tbl_residence_other WHERE NOT is_deleted;

