CREATE TABLE tbl_housing_unit (
	housing_unit_id			SERIAL PRIMARY KEY,
	housing_unit_code 		VARCHAR(6) UNIQUE NOT NULL,
	housing_unit_date			DATE NOT NULL,
	housing_unit_date_end		DATE,
	unit_type_code 			VARCHAR(10) NOT NULL REFERENCES tbl_l_unit_type (unit_type_code),
	unit_size 				INTEGER,
	housing_project_code 	VARCHAR(10) NOT NULL REFERENCES tbl_l_housing_project (housing_project_code),
	tax_credit 				BOOLEAN NOT NULL,
	security_deposit 			NUMERIC(8,2) NOT NULL,
	tax_credit_percent 		INTEGER,
	max_occupant			INTEGER NOT NULL DEFAULT 1,
-- Scattered Site Specific Data --
	address_1				VARCHAR(70),
	address_2				VARCHAR(70),
	city					VARCHAR(30),
	state					CHAR(2),
	zipcode				VARCHAR(5),
	landlord_contact			TEXT,
--system fields
	added_by     			INTEGER NOT NULL REFERENCES tbl_staff (staff_id),
	added_at     			TIMESTAMP(0)     NOT NULL DEFAULT CURRENT_TIMESTAMP,
	changed_by     			INTEGER NOT NULL  REFERENCES tbl_staff (staff_id),
	changed_at     			TIMESTAMP(0)     NOT NULL DEFAULT CURRENT_TIMESTAMP,
	is_deleted    			BOOLEAN NOT NULL DEFAULT FALSE,
	deleted_at   			TIMESTAMP(0),
	deleted_by   			INTEGER REFERENCES tbl_staff(staff_id),
	deleted_comment 			TEXT,
	sys_log 				TEXT

	CONSTRAINT scattered_address CHECK ((housing_project_code NOT IN ('SCATTERED','LEASED') AND
								(address_1 IS NULL
								AND address_2 IS NULL
								AND city IS NULL
								AND state IS NULL
								AND zipcode IS NULL
								AND landlord_contact IS NULL))
							OR
							(housing_project_code = 'SCATTERED' AND
								(address_1 IS NOT NULL 
								AND city IS NOT NULL
								AND state IS NOT NULL
								AND zipcode IS NOT NULL
								AND landlord_contact IS NOT NULL))

							OR
							(housing_project_code = 'LEASED' AND
								(address_1 IS NOT NULL 
								AND city IS NOT NULL
								AND state IS NOT NULL
								AND zipcode IS NOT NULL
								AND landlord_contact IS NULL)))
);

CREATE OR REPLACE VIEW housing_unit AS
SELECT * FROM tbl_housing_unit WHERE NOT is_deleted;

CREATE OR REPLACE VIEW housing_unit_current AS
SELECT * FROM housing_unit WHERE housing_unit_date_end > CURRENT_DATE OR housing_unit_date_end IS NULL;
