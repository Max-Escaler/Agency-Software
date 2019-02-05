CREATE OR REPLACE FUNCTION client_phone( cid integer, pdate date ) RETURNS text AS $$
DECLARE
	pnum text;
BEGIN
	SELECT INTO pnum number FROM phone 
		WHERE client_id = cid AND phone_date <= pdate AND (phone_date_end > pdate OR phone_date_end IS NULL)
			ORDER BY phone_date DESC LIMIT 1;

	IF pnum IS NULL THEN --check address_clinical	
		SELECT INTO pnum phone FROM address_clinical WHERE client_id = cid AND address_clinical_date <= pdate
			ORDER BY address_clinical_date DESC LIMIT 1;
	END IF;

	RETURN pnum;

END; $$ LANGUAGE plpgsql STABLE;

CREATE OR REPLACE FUNCTION client_phone( int ) RETURNS text AS $$

	SELECT client_phone($1,CURRENT_DATE);
$$ LANGUAGE sql STABLE;
