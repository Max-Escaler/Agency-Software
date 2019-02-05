CREATE OR REPLACE FUNCTION heet_clients( date, date ) RETURNS setof integer AS '
DECLARE	
	starts ALIAS FOR $1;
	endval   ALIAS FOR $2;
	cid	RECORD;
	ends	date;
BEGIN
	SELECT INTO ends COALESCE(endval,current_date);

FOR cid IN 
	SELECT distinct client_id FROM 
		(SELECT client_id 
			FROM heet_reg 
			WHERE heet_reg_date <= ends
			AND	COALESCE(heet_reg_date_end,current_date) >= starts
	UNION
		SELECT client_id 
			FROM service_heet 
			WHERE service_date BETWEEN starts AND ends
	UNION
		SELECT client_id 
			FROM event 
			WHERE (minimum_date IS NULL AND event_date BETWEEN starts AND ends)
			OR ( (minimum_date <= ends) AND event_date >= starts ) 
	UNION	
		SELECT client_id 
			FROM residence_other 
			WHERE facility_code=''SHEL-HEET''
			AND residence_date <= ends
			AND	COALESCE(residence_date_end,current_date) >= starts) AS whyaliasesareneeded
LOOP
    RETURN NEXT cid.client_id;
END LOOP;
RETURN; 

END;' LANGUAGE 'plpgsql';


