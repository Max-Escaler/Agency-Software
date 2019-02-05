CREATE OR REPLACE FUNCTION allowed_occupant( varchar, date ) RETURNS int AS $$
DECLARE
	unit ALIAS for $1;
	asof ALIAS for $2;
	result INT;
BEGIN
	SELECT INTO result max_occupant
		FROM housing_unit
		WHERE unit=housing_unit_code
		AND asof BETWEEN housing_unit_date AND COALESCE(housing_unit_date_end,asof);
	RETURN result;
END;$$ LANGUAGE plpgsql;

CREATE OR REPLACE FUNCTION enforce_max_occupant() RETURNS trigger AS $$
DECLARE
	cnt int;
	allowed int;
BEGIN
	SELECT INTO cnt count(*)
		FROM residence_own
		WHERE NEW.housing_project_code=housing_project_code
		AND NEW.housing_unit_code=housing_unit_code
		AND residence_date <= COALESCE(new.residence_date_end,current_date)
		AND COALESCE(residence_date_end,current_date) >= new.residence_date
		AND new.residence_own_id <> residence_own_id
		AND new.client_id <> client_id;
	SELECT INTO allowed allowed_occupant(NEW.housing_unit_code,NEW.residence_date);
    IF cnt >= allowed THEN
		RAISE EXCEPTION 
			'% occupants more than allowed (%) for %, %',cnt+1,allowed,NEW.housing_project_code,NEW.housing_unit_code;
    END IF;
    RETURN NEW;

END;$$ LANGUAGE plpgsql;

CREATE OR REPLACE FUNCTION current_occupant_count( varchar ) RETURNS int AS $$
DECLARE
	unit ALIAS FOR $1;
	cnt int;
BEGIN
	SELECT INTO cnt COUNT(*)
		FROM residence_own
		WHERE COALESCE(residence_date_end,current_date) >= current_date
		AND housing_unit_code = unit;
	RETURN COALESCE(cnt,0);
END;$$ LANGUAGE plpgsql;

