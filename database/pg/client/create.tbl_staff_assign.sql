CREATE TABLE tbl_staff_assign (
	staff_assign_id			SERIAL PRIMARY KEY,
	client_id				INTEGER NOT NULL REFERENCES tbl_client (client_id),
	staff_id				INTEGER REFERENCES tbl_staff(staff_id),
	staff_assign_type_code		VARCHAR(10) NOT NULL REFERENCES tbl_l_staff_assign_type (staff_assign_type_code),
	staff_assign_date			DATE NOT NULL,
	staff_assign_date_end		DATE,	
	send_alert				BOOLEAN NOT NULL DEFAULT TRUE,
	comment				TEXT,
	name_last				VARCHAR(20),
	name_first				VARCHAR(20),
	agency_code				VARCHAR(10) REFERENCES tbl_l_agency (agency_code),
	phone_1				VARCHAR(20),
	phone_2				VARCHAR(20),
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

	--constraints
	CONSTRAINT org_staff_check CHECK (
		(staff_id IS NOT NULL 
			AND name_last IS NULL AND name_first IS NULL AND agency_code IS NULL
			AND phone_1 IS NULL AND phone_2 IS NULL
		)
		OR
		(staff_id IS NULL 
			AND name_last IS NOT NULL AND agency_code IS NOT NULL)
	)
);

CREATE OR REPLACE VIEW staff_assign AS
SELECT tbl_staff_assign.*,
	COALESCE(staff_id::text,name_last||', '||COALESCE(name_first,'')) AS staff_id_name,
	CASE
		WHEN staff_id IS NULL THEN
			l_agency.description||'\n'
			||COALESCE(phone_1,'')
			||COALESCE(CASE WHEN phone_2 IS NOT NULL THEN '\n'||phone_2 END,'')
	END AS contact_information
 FROM tbl_staff_assign 
 	LEFT JOIN l_agency USING (agency_code)
 WHERE NOT tbl_staff_assign.is_deleted;

CREATE OR REPLACE VIEW staff_assign_current AS
SELECT * FROM staff_assign
WHERE staff_assign_date <= CURRENT_DATE
	AND (staff_assign_date_end > CURRENT_DATE OR staff_assign_date_end IS NULL);
