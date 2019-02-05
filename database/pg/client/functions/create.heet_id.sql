CREATE OR REPLACE FUNCTION heet_id( int ) RETURNS text AS '
DECLARE	
	cid ALIAS FOR $1;
	hid text;
BEGIN
	SELECT INTO hid heet_id FROM heet_ids WHERE client_id=cid;
	RETURN hid;
END;' LANGUAGE 'plpgsql';

