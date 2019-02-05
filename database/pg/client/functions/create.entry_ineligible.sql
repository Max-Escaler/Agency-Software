CREATE OR REPLACE FUNCTION entry_ineligible( integer) RETURNS boolean AS $$
DECLARE
    cid ALIAS for $1; 
BEGIN
    RETURN (SELECT is_ineligible FROM
		status_eligible WHERE client_id=cid);
END;$$ LANGUAGE plpgsql;

