CREATE OR REPLACE FUNCTION address_client( int ) RETURNS text AS $$
BEGIN
	RETURN address_client($1,CURRENT_DATE);
END$$ LANGUAGE plpgsql STABLE;

CREATE OR REPLACE FUNCTION address_client( int, date ) RETURNS text AS $$
DECLARE
	id	ALIAS FOR $1;
	adate		ALIAS FOR $2;
	short_unit	text;
	address		text;
BEGIN
    SELECT INTO address
        address_1 || COALESCE(E'\n'||address_2,'')
            || E'\n'||city||', '||state||' '||zipcode
    FROM address_client
	WHERE client_id = id
	AND address_date <= adate
	AND address_date_end >= adate;
    RETURN address;
END;$$ LANGUAGE plpgsql STABLE;

CREATE OR REPLACE FUNCTION landlord_contact(varchar(6)) RETURNS text AS $$
DECLARE unit ALIAS FOR $1;
	address text;
BEGIN
	SELECT INTO address landlord_contact FROM housing_unit WHERE housing_unit_code = unit;
	RETURN address;
END;$$ LANGUAGE plpgsql STABLE;

CREATE OR REPLACE FUNCTION client_clinical_address( int ) RETURNS text AS $$
BEGIN
	RETURN client_clinical_address($1,CURRENT_DATE);
END;$$ LANGUAGE plpgsql STABLE;

CREATE OR REPLACE FUNCTION client_clinical_address( id int, adate date ) RETURNS text AS $$
DECLARE
	aaddress text;
BEGIN
	SELECT INTO aaddress
	       address_summary
	FROM address
	WHERE	client_id = id
		AND address_date <= adate
	ORDER BY address_date DESC
	LIMIT 1;

	RETURN aaddress;
END;$$ LANGUAGE plpgsql STABLE;
