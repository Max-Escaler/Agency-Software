CREATE OR REPLACE FUNCTION is_human_staff ( sid integer ) RETURNS boolean AS $$

	SELECT CASE WHEN staff_position_code<>'SYSUSER' THEN true ELSE false END FROM staff WHERE staff_id=$1;

$$ LANGUAGE sql STABLE;

CREATE OR REPLACE FUNCTION staff_language_f ( integer ) RETURNS text AS $$
DECLARE
	sid ALIAS FOR $1;
	slangs TEXT[];
BEGIN
	SELECT INTO slangs (SELECT ARRAY(SELECT l.description||' ('||lp.description||')' FROM staff_language sl 
					LEFT JOIN l_language l USING (language_code)  
					LEFT JOIN l_language_proficiency lp USING (language_proficiency_code)  
					WHERE staff_id = sid));
	RETURN array_format(slangs,E'\n');
END;$$ LANGUAGE plpgsql STABLE;


/*
 * staff program functions
 */

CREATE OR REPLACE FUNCTION staff_program( sid int4, sdate date ) RETURNS text AS $$

        SELECT agency_program_code FROM staff_employment WHERE staff_id=$1
		AND $2 BETWEEN hired_on AND COALESCE(terminated_on,CURRENT_DATE)
		ORDER BY hired_on DESC LIMIT 1; --this should account for bad data

$$ LANGUAGE sql STABLE;

CREATE OR REPLACE FUNCTION staff_program( sid int4 ) RETURNS text AS $$

        SELECT agency_program_code FROM staff WHERE staff_id=$1;

$$ LANGUAGE sql STABLE;

/*
 * staff position functions
 */

CREATE OR REPLACE FUNCTION staff_position( sid int4, sdate date ) RETURNS text AS $$

        SELECT staff_position_code FROM staff_employment WHERE staff_id=$1
		AND $2 BETWEEN hired_on AND COALESCE(terminated_on,CURRENT_DATE)
		ORDER BY hired_on DESC LIMIT 1; --this should account for bad data

$$ LANGUAGE sql STABLE;

CREATE OR REPLACE FUNCTION staff_position( sid int4 ) RETURNS text AS $$

        SELECT staff_position_code FROM staff WHERE staff_id=$1;

$$ LANGUAGE sql STABLE;

/*
 * staff project functions
 */

CREATE OR REPLACE FUNCTION staff_project( sid int4, sdate date ) RETURNS text AS $$

        SELECT agency_project_code FROM staff_employment WHERE staff_id=$1
		AND $2 BETWEEN hired_on AND COALESCE(terminated_on,CURRENT_DATE)
		ORDER BY hired_on DESC LIMIT 1; --this should account for bad data

$$ LANGUAGE sql STABLE;

CREATE OR REPLACE FUNCTION staff_project( sid int4 ) RETURNS text AS $$

        SELECT agency_project_code FROM staff WHERE staff_id=$1;

$$ LANGUAGE sql STABLE;

/*
 * Staff Facility functions
 */

CREATE OR REPLACE FUNCTION staff_facility ( sid int, asofdate date ) RETURNS varchar AS $$

	SELECT agency_facility_code FROM staff_employment WHERE staff_id = $1
		AND $2 BETWEEN hired_on AND COALESCE(terminated_on, CURRENT_DATE)

	ORDER BY hired_on DESC LIMIT 1;

$$ LANGUAGE sql STABLE;

CREATE OR REPLACE FUNCTION staff_facility( sid int ) RETURNS varchar AS $$

	SELECT staff_facility($1,CURRENT_DATE);

$$ LANGUAGE sql STABLE;

/*
 * Remote access
 */

CREATE OR REPLACE FUNCTION staff_remote_login_allowed( sid int ) RETURNS boolean AS $$

	SELECT COALESCE((SELECT TRUE FROM staff_remote_login_now WHERE staff_id = $1),FALSE);

$$ LANGUAGE sql STABLE;

CREATE OR REPLACE FUNCTION staff_login_allowed( sid int ) RETURNS boolean AS $$

	/*
	 * Determines first if internal, then no further check is necessary
	 * if not, checks staff record to see if remote login is allowed
	 */

	SELECT is_internal_access() OR staff_remote_login_allowed( $1 );

$$ LANGUAGE sql STABLE;

/*
 * Staff hire date functions
 */

CREATE OR REPLACE FUNCTION staff_hired_on ( sid int4 ) RETURNS date AS $$

	SELECT hired_on FROM staff_employment WHERE staff_id=$1 
		AND terminated_on IS NULL;

$$ LANGUAGE sql STABLE;


CREATE OR REPLACE FUNCTION staff_hired_on ( sid int4, date ) RETURNS date AS $$

	SELECT hired_on FROM staff_employment WHERE staff_id=$1 
		AND $2 BETWEEN hired_on AND COALESCE(terminated_on, CURRENT_DATE)
		ORDER BY hired_on DESC LIMIT 1;

$$ LANGUAGE sql STABLE;


CREATE OR REPLACE FUNCTION staff_hired_on_continuous ( sid int4, date ) RETURNS date AS $$

DECLARE
	sid ALIAS for $1;
	cdate ALIAS for $2;
	s_date DATE;
	p_date DATE;
BEGIN
	SELECT INTO s_date staff_hired_on ( sid, cdate );
	SELECT INTO p_date staff_hired_on ( sid, s_date-1 );
	RETURN CASE WHEN p_date IS NOT NULL
		THEN staff_hired_on_continuous( sid, p_date )
		ELSE s_date END;

END;$$ LANGUAGE plpgsql STABLE;
