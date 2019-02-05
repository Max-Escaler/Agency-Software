-------------------------------------
--
--     staff assignment functions
--
-------------------------------------

CREATE OR REPLACE FUNCTION staff_alert_client_assign(INTEGER) RETURNS INTEGER[] AS $$
BEGIN
     RETURN staff_alert_client_assign($1,CURRENT_DATE);
END; $$ LANGUAGE plpgsql STABLE;


CREATE OR REPLACE FUNCTION staff_alert_client_assign(client INTEGER, date DATE) RETURNS INTEGER[] AS $$
DECLARE
     /*
      * Function returning staff_ids for alerts
      */
     t integer[];
     assign RECORD;
BEGIN
    FOR assign IN SELECT distinct staff_id
          FROM staff_assign
          WHERE client_id=client AND staff_id IS NOT NULL
               AND staff_assign_date <= date
               AND (staff_assign_date_end IS NULL OR staff_assign_date_end >= date)
               AND send_alert
     LOOP
     t := COALESCE(array_append(t,assign.staff_id),ARRAY[assign.staff_id]);
    END LOOP;
    RETURN t;
END; $$ LANGUAGE plpgsql STABLE;


CREATE OR REPLACE FUNCTION client_staff_assign(client INTEGER, date DATE, assign_type TEXT) RETURNS TEXT[] AS $$
DECLARE
	tmp text;
	t text[];
	assign RECORD;
BEGIN
    FOR assign IN SELECT distinct staff_id,COALESCE(name_first,'')||' '||COALESCE(name_last,'')||' ('||l.description||')' as cm_name
		FROM staff_assign 
		LEFT JOIN l_agency l USING (agency_code)
		WHERE client_id=client AND staff_assign_date <= date 
			AND (staff_assign_date_end IS NULL OR staff_assign_date_end >= date)
			AND (CASE WHEN assign_type='ALL' THEN TRUE ELSE staff_assign_type_code ~ assign_type END)
	LOOP
	tmp := COALESCE(assign.staff_id::text,assign.cm_name);
	t := COALESCE(array_append(t,tmp),ARRAY[tmp]);
    END LOOP;
    RETURN t;
END; $$ LANGUAGE plpgsql STABLE;

CREATE OR REPLACE FUNCTION client_staff_assign(INTEGER) RETURNS TEXT[] AS $$
	SELECT client_staff_assign($1,CURRENT_DATE,'ALL');
$$ LANGUAGE sql STABLE;

CREATE OR REPLACE FUNCTION client_staff_assign(INTEGER,TEXT) RETURNS TEXT[] AS $$
	SELECT client_staff_assign($1,CURRENT_DATE,$2);
$$ LANGUAGE sql STABLE;

CREATE OR REPLACE FUNCTION client_staff_assign(INTEGER,DATE) RETURNS TEXT[] AS $$
	SELECT client_staff_assign($1,$2,'ALL');
$$ LANGUAGE sql STABLE;

CREATE OR REPLACE FUNCTION client_staff_assign_f (INTEGER,DATE,TEXT) RETURNS TEXT AS $$
DECLARE
	cid ALIAS FOR $1;
	date ALIAS FOR $2;
	assign_type ALIAS FOR $3;
	assigns TEXT[];
	snames TEXT;
	count	INTEGER;
	tmp TEXT;
BEGIN
	assigns := client_staff_assign(cid,date,assign_type);
	count := array_count(assigns);
	IF (count > 0) THEN
		FOR i IN 1..count LOOP
			tmp := COALESCE(staff_name(assigns[i]::text),assigns[i]::text);
			snames := COALESCE(snames||E'\n','')||tmp;
		END LOOP;
	END IF;
	RETURN snames;
END; $$ LANGUAGE plpgsql STABLE;

CREATE OR REPLACE FUNCTION client_staff_assign_f (INTEGER) RETURNS TEXT AS $$
	SELECT client_staff_assign_f($1,CURRENT_DATE,'ALL');
$$ LANGUAGE sql STABLE;

CREATE OR REPLACE FUNCTION client_staff_assign_f (INTEGER,TEXT) RETURNS TEXT AS $$
	SELECT client_staff_assign_f($1,CURRENT_DATE,$2);
$$ LANGUAGE sql STABLE;

CREATE OR REPLACE FUNCTION client_staff_assign_f (INTEGER,DATE) RETURNS TEXT AS $$
	SELECT client_staff_assign_f($1,$2,'ALL');
$$ LANGUAGE sql STABLE;

CREATE OR REPLACE FUNCTION client_staff_assign_own(cid INT, adate DATE, atype TEXT) RETURNS int[] AS $$

	SELECT array(SELECT staff_id FROM staff_assign
			WHERE client_id = $1
				AND staff_id IS NOT NULL
				AND staff_assign_date <= $2
				AND COALESCE(staff_assign_date_end,$2+1) > $2
				AND CASE WHEN $3 = 'ALL' THEN true ELSE staff_assign_type_code ~ $3 END
			);

$$ LANGUAGE sql STABLE;

CREATE OR REPLACE FUNCTION client_staff_assign_own(cid INT) RETURNS int[] AS $$

	SELECT client_staff_assign_own($1,CURRENT_DATE,'ALL');

$$ LANGUAGE sql STABLE;

CREATE OR REPLACE FUNCTION client_staff_assign_own(cid INT, adate DATE) RETURNS int[] AS $$

	SELECT client_staff_assign_own($1,$2,'ALL');

$$ LANGUAGE sql STABLE;

CREATE OR REPLACE FUNCTION client_staff_assign_own(cid INT, atype TEXT) RETURNS int[] AS $$

	SELECT client_staff_assign_own($1,CURRENT_DATE,$2);

$$ LANGUAGE sql STABLE;

CREATE OR REPLACE FUNCTION client_staff_assign_own_f ( cid int, adate date, atype text) RETURNS TEXT AS $$
DECLARE
	assigns int[];
	snames TEXT;
	scount int;
	tmp TEXT;
BEGIN
	assigns := client_staff_assign_own(cid,adate,atype);
	scount  := array_count(assigns);

	IF (scount > 0) THEN
		FOR i in 1..scount LOOP
			tmp := staff_name(assigns[i]);
			snames := COALESCE(snames||E'\n','')||tmp;
		END LOOP;
	END IF;

	RETURN snames;
END; $$ LANGUAGE plpgsql STABLE;

CREATE OR REPLACE FUNCTION client_staff_assign_own_f ( cid int ) RETURNS TEXT AS $$

	SELECT client_staff_assign_own_f($1,CURRENT_DATE,'ALL');

$$ LANGUAGE sql STABLE;

CREATE OR REPLACE FUNCTION client_staff_assign_own_f ( cid int, atype TEXT ) RETURNS TEXT AS $$

	SELECT client_staff_assign_own_f($1,CURRENT_DATE,$2);

$$ LANGUAGE sql STABLE;

CREATE OR REPLACE FUNCTION client_staff_assign_own_f ( cid int, adate DATE ) RETURNS TEXT AS $$

	SELECT client_staff_assign_own_f($1,$2,'ALL');

$$ LANGUAGE sql STABLE;

