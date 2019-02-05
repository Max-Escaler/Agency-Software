BEGIN;
CREATE OR REPLACE FUNCTION bed_episodes( int, date, date ) RETURNS SETOF RECORD AS '
DECLARE
    cid ALIAS FOR $1;
    s_date ALIAS FOR $2;
    e_date ALIAS FOR $3;
    en_date date;
    b_date  RECORD;
    result RECORD;
    quer text;
    start_date  date;
    last_date   date;
    end_date    date;
    last_returned_date date;
    number_of_days integer;
BEGIN
    quer := ''SELECT bed_date AS start_date, NULL AS end_date FROM bed WHERE client_id = '' || cid::text;
    en_date := COALESCE(e_date,current_date);
    IF s_date IS NOT NULL THEN
        quer := quer || '' AND bed_date BETWEEN \\'''' || s_date::text ||
            ''\\'' AND \\'''' || en_date || ''\\'''';
    END IF;
    quer := quer || '' ORDER BY bed_date'';
    FOR b_date IN EXECUTE quer LOOP
        start_date := COALESCE(start_date,b_date.start_date);
        IF last_date IS NOT NULL AND ((b_date.start_date - last_date) <> 1) THEN
            IF start_date IS NOT NULL THEN
		number_of_days := last_date-start_date+1;
		SELECT cid,number_of_days,start_date,last_date INTO result ;	
		RETURN NEXT result;
            last_returned_date := last_date;
            END IF;
            start_date := b_date.start_date;
            end_date := b_date.start_date;
        END IF;
        last_date := b_date.start_date;
    END LOOP;

    IF last_date <> last_returned_date OR (last_date IS NOT NULL AND last_returned_date IS NULL)THEN /* account for stays of only 1 episode */
		number_of_days := last_date-start_date+1;
		SELECT cid,number_of_days,start_date,last_date INTO result ;	
		RETURN NEXT result;
    END IF;
    RETURN;
END;' LANGUAGE 'PLPGSQL';

CREATE OR REPLACE FUNCTION average_shelter_stay ( INTEGER,DATE,DATE ) RETURNS numeric AS '
DECLARE
	cid ALIAS FOR $1;
	s_date ALIAS FOR $2;
	e_date ALIAS FOR $3;
	reslt numeric(8,2);
BEGIN
	SELECT INTO reslt SUM(number_of_days)::numeric/COUNT(*)::numeric FROM bed_episodes(cid,s_date,e_date) AS bed_episodes(client_id integer,number_of_days integer,start_date date,end_date date);
	RETURN reslt;
END;' LANGUAGE 'PLPGSQL';

CREATE FUNCTION average_shelter_stay (integer) RETURNS NUMERIC AS '
BEGIN
	RETURN average_shelter_stay($1,null,null);
END;' LANGUAGE 'PLPGSQL';

SELECT average_shelter_stay(22755);

SELECT * from bed_episodes(22755,null,null) AS bed_episodes(client_id integer,number_of_days int,start_date date,end_date date) order by 2 desc;

SELECT MIN(average_shelter_stay(client_id,'2004-01-01','2004-12-31')),MAX(average_shelter_stay(client_id,'2004-01-01','2004-12-31')),SUM(average_shelter_stay(client_id,'2004-01-01','2004-12-31'))/COUNT(*) FROM (SELECT DISTINCT client_id FROM bed WHERE bed_date BETWEEN '2004-01-01' AND '2004-12-31') as t;


--SELECT * from bed_episodes(43741,null,null) AS bed_episodes(client_id integer,start_date date,end_date date) order by 2 desc;

END;