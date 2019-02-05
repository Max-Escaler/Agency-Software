CREATE TABLE tbl_residence_own (
	residence_own_id 		SERIAL PRIMARY KEY,
	client_id 				INTEGER NOT NULL REFERENCES tbl_client (client_id),
	housing_project_code 	VARCHAR(10) NOT NULL REFERENCES tbl_l_housing_project (housing_project_code),
	housing_unit_code			VARCHAR(10) NOT NULL REFERENCES tbl_housing_unit(housing_unit_code),
	residence_date 			DATE NOT NULL ,
	residence_date_end 		DATE,
	moved_from_code 			VARCHAR(10) NOT NULL REFERENCES tbl_l_facility (facility_code),
	chronic_homeless_status_code	VARCHAR(10) NOT NULL REFERENCES tbl_l_chronic_homeless_status ( chronic_homeless_status_code ),
	lease_on_file			BOOLEAN,
	moved_to_code			VARCHAR(10) REFERENCES tbl_l_facility (facility_code),
	departure_type_code		VARCHAR(10) REFERENCES tbl_l_departure_type (departure_type_code),
	departure_reason_code		VARCHAR(10) REFERENCES tbl_l_departure_reason (departure_reason_code),
	move_out_was_code 		VARCHAR(10) REFERENCES tbl_l_exit_status (exit_status_code),
	returned_homeless			BOOLEAN,
	comment				TEXT,
	--system fields
	added_by 				INTEGER NOT NULL REFERENCES tbl_staff (staff_id),
	added_at 				TIMESTAMP(0) NOT NULL DEFAULT current_timestamp,
	changed_by 				INTEGER NOT NULL REFERENCES tbl_staff (staff_id),
	changed_at 				TIMESTAMP(0) NOT NULL DEFAULT current_timestamp,
	is_deleted 				BOOLEAN DEFAULT false,
	deleted_at				TIMESTAMP(0),
	deleted_by				INTEGER REFERENCES tbl_staff (staff_id),
	deleted_comment			TEXT,
	sys_log				TEXT

	CONSTRAINT date_sanity CHECK (residence_date_end IS NULL OR residence_date <= residence_date_end )
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
	CONSTRAINT scattered_site_only CHECK (
		(housing_project_code = 'SCATTERED' AND lease_on_file IS NOT NULL)
		OR (housing_project_code<>'SCATTERED' AND lease_on_file IS NULL) )
);


CREATE OR REPLACE FUNCTION tbl_residence_own_validate_modify() RETURNS TRIGGER AS '
	BEGIN
		IF NOT new.housing_project_code = old.housing_project_code THEN
			RAISE EXCEPTION ''Cannot change project for existing DESC housing record'';
		ELSIF NOT new.housing_unit_code = old.housing_unit_code THEN
			RAISE EXCEPTION ''Cannot change unit for existing DESC housing record'';
		END IF;
		RETURN NEW;
	END;
	' LANGUAGE 'plpgsql';

CREATE TRIGGER 
	tbl_residence_own_no_unit_or_project_change
BEFORE UPDATE ON 
	tbl_residence_own
FOR EACH ROW EXECUTE PROCEDURE tbl_residence_own_validate_modify();

CREATE TRIGGER check_max_occupant AFTER INSERT OR UPDATE ON tbl_residence_own
    FOR EACH ROW EXECUTE PROCEDURE enforce_max_occupant();
