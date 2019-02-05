CREATE OR REPLACE FUNCTION continuous_shelter_stay ( integer, date ) RETURNS integer AS $$
DECLARE
	cid ALIAS FOR $1;
	days INTEGER := 0;
	start ALIAS FOR $2;
	current_day date;
BEGIN
	SELECT INTO current_day bed_date FROM bed WHERE client_id=cid AND bed_date=start;
	WHILE current_day IS NOT NULL LOOP
		days := days+1;
		SELECT INTO current_day bed_date FROM bed WHERE client_id=cid AND bed_date=current_day-1;
	END LOOP;

	RETURN days;
END;$$ LANGUAGE plpgsql STABLE;


