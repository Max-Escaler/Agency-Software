CREATE OR REPLACE FUNCTION donor_note_insert() RETURNS trigger AS $$
BEGIN
	IF (NEW.agency_project_code IS NULL) 
		THEN NEW.agency_project_code=staff_project(NEW.staff_id);
	END IF;
	IF (NEW.staff_position_code IS NULL)
		THEN NEW.staff_position_code=staff_position(NEW.staff_id);
	END IF;
	IF (NEW.agency_program_code IS NULL)
		THEN NEW.agency_program_code=staff_program(NEW.staff_id);
	END IF;
	RETURN NEW;
END; $$ language plpgsql STABLE;

CREATE OR REPLACE FUNCTION donor_note_update() RETURNS trigger AS $$
BEGIN
	IF NOT (NEW.staff_id=OLD.staff_id)
		THEN 
			NEW.agency_project_code=staff_project(NEW.staff_id);
        	NEW.staff_position_code=staff_position(NEW.staff_id);
			NEW.agency_program_code=staff_program(NEW.staff_id);
    END IF;
    RETURN NEW;
END; $$ language plpgsql STABLE;	

