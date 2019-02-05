CREATE OR REPLACE FUNCTION housing_project_from_unit (unit varchar(10)) RETURNS varchar(10) AS $$

	SELECT a.housing_project_code FROM tbl_housing_unit a WHERE LOWER(a.housing_unit_code)=LOWER($1) LIMIT 1;

$$ LANGUAGE sql STABLE;

CREATE OR REPLACE FUNCTION current_residence_own (cid int4) RETURNS varchar(10) AS $$

   SELECT a.housing_project_code FROM residence_own a WHERE a.client_id=$1 AND a.residence_date_end IS NULL LIMIT 1;

$$ LANGUAGE sql STABLE;

CREATE OR REPLACE FUNCTION current_unit_own (cid int4, dateo date) RETURNS varchar(10) AS $$

   SELECT a.housing_unit_code FROM residence_own a WHERE a.client_id=$1
	AND (a.residence_date <= $2 AND (a.residence_date_end >= $2 OR a.residence_date_end IS NULL)) LIMIT 1;

$$ LANGUAGE sql STABLE;

CREATE OR REPLACE FUNCTION last_residence_own (cid int4) RETURNS varchar(10) AS $$

   SELECT a.housing_project_code FROM residence_own a WHERE a.client_id=$1 ORDER BY a.residence_date DESC LIMIT 1;

$$ LANGUAGE sql STABLE;

CREATE OR REPLACE FUNCTION last_residence_own_unit (cid int4) RETURNS varchar AS $$

   SELECT a.housing_unit_code FROM residence_own a WHERE a.client_id=$1 ORDER BY a.residence_date DESC LIMIT 1;

$$ LANGUAGE sql STABLE;

CREATE OR REPLACE FUNCTION time_in_housing_project_own (cid int4,project varchar(10)) RETURNS int4 AS $$
DECLARE
   cur RECORD;
   st_date DATE;
   end_date DATE;
   time INT4;
BEGIN
   SELECT MAX(residence_date) AS max_date,COALESCE(residence_date_end,CURRENT_DATE) AS max_end INTO cur 
      FROM residence_own a WHERE a.client_id=cid AND housing_project_code=project GROUP BY max_end LIMIT 1;
   end_date := cur.max_end;
   st_date := last_residence_own_project_start(cid,cur.max_date,project);
   RETURN end_date-st_date;
END $$ LANGUAGE plpgsql STABLE;

CREATE OR REPLACE FUNCTION continuous_housing_project_own( cid int4 ) RETURNS date AS $$
DECLARE
   cur RECORD;
   st_date DATE;
   project VARCHAR(10);
BEGIN
   SELECT a.* INTO cur FROM residence_own a WHERE a.client_id=cid AND residence_date_end IS NULL LIMIT 1;
   st_date := cur.residence_date;
   project := cur.housing_project_code;
   RETURN last_residence_own_project_start (cid,st_date,project);
END $$ LANGUAGE plpgsql STABLE;

CREATE OR REPLACE FUNCTION last_residence_own_project_start( cid int4, start_date date, cur_project varchar(10) ) returns date AS $$
DECLARE
   prev_record RECORD;
   prev_start_date DATE;
BEGIN
   SELECT a.* INTO prev_record FROM residence_own a
      WHERE a.client_id=cid AND (a.residence_date_end = start_date - 1 OR (a.residence_date_end = start_date AND a.residence_date != a.residence_date_end))
      AND a.housing_project_code=cur_project LIMIT 1;
      prev_start_date := prev_record.residence_date;
      IF prev_start_date IS NOT NULL THEN
          prev_start_date := last_residence_own_project_start(cid,prev_start_date,cur_project);
          RETURN prev_start_date;
      ELSE
          RETURN start_date;
      END IF;
END $$ LANGUAGE plpgsql STABLE;

CREATE OR REPLACE FUNCTION continuous_housing_own( cid int4 ) RETURNS date AS $$
DECLARE
      cur   RECORD;
      st_date DATE;
BEGIN
   SELECT a.* INTO cur FROM residence_own a WHERE a.client_id=cid AND residence_date_end IS NULL LIMIT 1;
   st_date := cur.residence_date;
   RETURN last_residence_own_start(cid,st_date);
END $$ LANGUAGE plpgsql STABLE;

CREATE OR REPLACE FUNCTION continuous_housing_own( int4, date ) RETURNS date AS $$
DECLARE
	cid	ALIAS FOR $1;
	date ALIAS FOR $2;
      cur   RECORD;
      st_date DATE;
BEGIN
   SELECT a.* INTO cur FROM residence_own a WHERE a.client_id=cid AND (residence_date_end IS NULL OR residence_date_end >= date) LIMIT 1;
   st_date := cur.residence_date;
   RETURN last_residence_own_start(cid,st_date);
END $$ LANGUAGE plpgsql STABLE;



CREATE OR REPLACE FUNCTION last_residence_own_start( cid int4, start_date date ) returns date AS $$
DECLARE
   prev_record RECORD;
   prev_start_date DATE;
BEGIN
   SELECT a.* INTO prev_record FROM residence_own a
      WHERE a.client_id=cid AND (a.residence_date_end = start_date - 1 OR 
		(a.residence_date_end = start_date AND a.residence_date != a.residence_date_end)) LIMIT 1;
      prev_start_date := prev_record.residence_date;
      IF prev_start_date IS NOT NULL THEN
          prev_start_date := last_residence_own_start(cid,prev_start_date);
          RETURN prev_start_date;
      ELSE
          RETURN start_date;
      END IF;
END $$ LANGUAGE plpgsql STABLE;
