/*
 * Some client functions
 */

CREATE OR REPLACE FUNCTION client_link( cid int4 ) RETURNS text AS $$
DECLARE
     client_link     text;
BEGIN
     client_link := link(client_url(cid),client_name(cid));
     RETURN client_link;
END; $$ LANGUAGE plpgsql STABLE;


CREATE OR REPLACE FUNCTION client_url( cid int4 ) RETURNS text AS $$
DECLARE
     client_url     text;
     base_url     text;
     client_name client.name_full%TYPE;
BEGIN
     SELECT INTO client_name name_full FROM client WHERE client_id=cid;
     SELECT INTO base_url agency_base_url();
     client_url := base_url || 'client_display.php?id=' || text(cid);
     RETURN client_url;
END; $$ LANGUAGE plpgsql STABLE;


CREATE OR REPLACE FUNCTION client_name( cid int4 ) RETURNS text AS $$

     SELECT name_full FROM client WHERE client_id=$1;

$$ LANGUAGE sql STABLE;


CREATE OR REPLACE FUNCTION client_age( cid int4, ddate date ) RETURNS interval AS $$

     SELECT age($2,dob) FROM client WHERE client_id = $1;

$$ LANGUAGE sql STABLE;


CREATE OR REPLACE FUNCTION client_age( int4 ) RETURNS interval AS $$

      SELECT client_age($1,CURRENT_DATE);

$$ LANGUAGE sql STABLE;


CREATE OR REPLACE FUNCTION client_salutation( cid int4 ) RETURNS text AS $$
DECLARE
     client_sal text;
     lastname     text;
BEGIN
     SELECT INTO lastname name_last FROM client WHERE client_id=cid;
     client_sal := CASE client_gender_simple(cid) 
          WHEN 'Female' THEN 'Ms. '
          WHEN 'Male' THEN 'Mr. '
          ELSE '' END || initcap(lastname);
     RETURN client_sal;
END; $$ LANGUAGE plpgsql STABLE;


CREATE OR REPLACE FUNCTION client_gender ( cid int4 ) RETURNS text AS $$

      SELECT TRIM(l.description) FROM tbl_client LEFT JOIN l_gender l USING (gender_code) WHERE client_id=$1;

$$ LANGUAGE sql STABLE;

CREATE OR REPLACE FUNCTION client_gender_simple ( cid int4 ) RETURNS text AS $$
DECLARE
     code   VARCHAR(10);
      gender TEXT;
BEGIN
      SELECT INTO code gender_code FROM tbl_client WHERE client_id=cid;
     gender := CASE 
               WHEN code IN ('1','4','6') THEN 'Female'
               WHEN code IN ('2','3','7') THEN 'Male'
               ELSE 'Unknown'
              END;
      RETURN gender;
END; $$ LANGUAGE plpgsql STABLE;

CREATE OR REPLACE FUNCTION client_gender_hipaa ( cid int4 ) RETURNS text AS $$
DECLARE
     gender TEXT;
     code VARCHAR(10);
BEGIN
     SELECT INTO code gender_code FROM tbl_client WHERE client_id = cid;
     gender := CASE WHEN code IN  ('1','4','6') THEN 'F'
               WHEN code IN ('2','3','7') THEN 'M'
               ELSE 'U'
          END;
     RETURN gender;
END; $$ LANGUAGE plpgsql STABLE;

CREATE OR REPLACE FUNCTION client_ethnicity( cid INT ) RETURNS TEXT AS $$
     SELECT
		CASE WHEN
			(SELECT COUNT(*) 
			FROM ethnicity
			WHERE client_id=$1
			AND COALESCE(ethnicity_date_end,current_date) >= current_date
			) > 1 THEN 'Multi-ethnic'
		ELSE
			(SELECT l.description::text
			FROM ethnicity LEFT JOIN l_ethnicity l USING (ethnicity_code)
			WHERE client_id = $1
			AND COALESCE(ethnicity_date_end,current_date) >= current_date)
		END;

$$ LANGUAGE sql STABLE;

CREATE OR REPLACE FUNCTION client_ethnicity_simple( cid INT ) RETURNS TEXT AS $$

     SELECT
		CASE WHEN
			(SELECT COUNT(distinct les.description) 
			FROM ethnicity
          	LEFT JOIN l_ethnicity l USING (ethnicity_code)
          	LEFT JOIN l_ethnicity_simple les USING (ethnicity_simple_code)
			WHERE client_id=$1
			AND COALESCE(ethnicity_date_end,current_date) >= current_date
			) > 1 THEN 'Multi-ethnic'
		ELSE
			(SELECT les.description::text
			FROM ethnicity
          	LEFT JOIN l_ethnicity l USING (ethnicity_code)
          	LEFT JOIN l_ethnicity_simple les USING (ethnicity_simple_code)
			WHERE client_id=$1
			AND COALESCE(ethnicity_date_end,current_date) >= current_date)
		END;

$$ LANGUAGE sql STABLE;

CREATE OR REPLACE FUNCTION ethnicity_simple ( eth VARCHAR) RETURNS TEXT AS $$

     SELECT les.description FROM l_ethnicity
          LEFT JOIN l_ethnicity_simple les USING (ethnicity_simple_code)
     WHERE ethnicity_code = $1;

$$ LANGUAGE sql STABLE;

/*
 * Unit number for housing residents
 */

CREATE OR REPLACE FUNCTION unit_no( cid int4, asof date ) RETURNS text AS $$
DECLARE
     unit  text;
BEGIN
     SELECT INTO unit 
                housing_unit_code
                FROM residence_own
                WHERE (client_id=cid)
                AND (residence_date <= asof)
                AND ( (residence_date_end IS NULL) OR (residence_date_end >= asof));
     RETURN unit;
END; $$ LANGUAGE plpgsql STABLE;

-- SET_NIGHFACTOR()...deteremines the night_factor for a bed_reg record  w/ valid client_id
/* Here's the quick run through
    - determine night end (same day as night start?)
    - determine night start (same day as night end?)
    - determine true removed_at (can't be null or greater than night_end)
    - determine true added_at (can't be before night_start)
    - night_amount is the amount of time the client actually stayed
    - total_night is the total amount of time a client could stay
    - night_factor is night_amount in minutes divided by total_night in minutes
*/
CREATE OR REPLACE FUNCTION set_nightfactor( timestamp, timestamp, text, text ) 
RETURNS numeric(4,2) AS $$
DECLARE
    added ALIAS FOR $1;
     removed ALIAS FOR $2;
     starting ALIAS FOR $3;
    ending ALIAS FOR $4; -- end is a keyword
    start_time time;
    end_time time;
    night_start timestamp;
    night_end timestamp;
    removed_at timestamp;
    added_at timestamp;
    night_amount interval;
    total_night interval;
    night_factor numeric(4,2);   
    test text; -- for testing purposes only
       
BEGIN
    start_time := starting; -- convert to text to time
    end_time := ending;

    IF to_char(added, 'HH24:MI:SS') < end_time THEN
        -- night starts and ends on the same day
        night_end := date(added) + end_time;
    ELSE
        -- night starts one day and ends the next
        night_end := date(added) + interval '1 day' + end_time;
    END IF;

    IF end_time < start_time THEN
        -- night starts one day and ends the next
        night_start := (date(night_end) - interval '1 day') + start_time;
    ELSE
        -- night starts and ends on the same day
        night_start = date(night_end) + start_time;
    END IF;

    IF removed > night_end THEN
        removed_at := night_end;
    ELSE
        removed_at := removed;
    END IF; 
    IF removed IS NULL THEN
        removed_at := night_end;
        test := removed_at;
    END IF;

    IF added > night_start THEN
        added_at := added;
    ELSE
        added_at := night_start;
    END IF;
            
    night_amount := removed_at - added_at;

    IF night_amount < interval '0 hour 0 min' THEN
        night_amount := 0; --client left before night started
    END IF;
    
    total_night := night_end - night_start;
    night_factor := ((EXTRACT(HOUR FROM night_amount)*60 + EXTRACT(MINUTE FROM night_amount)) / (EXTRACT(HOUR FROM total_night)*60 + EXTRACT(MINUTE FROM total_night)));

/*
     --for testing--

     test := 'start is ' || night_start ||  E'\n end is ' || night_end
        || E'\n removed_at is ' || removed_at || E'\n added_at is ' 
        || added_at || E'\n night_amount is ' || night_amount
        || E'\n total_night is ' || total_night
        || E'\n night_factor is ' || night_factor || E'\n';
     RAISE NOTICE '%', test; 
*/
     RETURN night_factor;
END;$$ LANGUAGE plpgsql;

