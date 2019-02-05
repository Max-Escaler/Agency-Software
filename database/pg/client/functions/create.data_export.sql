CREATE OR REPLACE FUNCTION export_value ( text, text, text ) RETURNS text AS $$
DECLARE
	lu_table ALIAS FOR $1;
	lu_value ALIAS FOR $2;
	ex_to_org ALIAS FOR $3;
	ex_value TEXT;
BEGIN
	SELECT INTO ex_value export_value FROM export_value_current
			WHERE lookup_table=lu_table AND lookup_value=lu_value AND export_organization_code=ex_to_org;
	RETURN ex_value;
END;$$ LANGUAGE plpgsql STABLE;

CREATE OR REPLACE FUNCTION export_value ( boolean, text ) RETURNS text AS $$
DECLARE
	bool_type TEXT;
BEGIN
	bool_type := CASE WHEN $1 IS TRUE THEN 'TRUE'
				WHEN $1 IS FALSE THEN 'FALSE'
				WHEN $1 IS NULL THEN 'NULL'
			END;
	RETURN export_value('BOOLEAN',bool_type,$2);
END;$$ LANGUAGE plpgsql STABLE;

CREATE OR REPLACE FUNCTION safe_harbors_value ( text, text ) RETURNS text AS $$
BEGIN
	RETURN export_value($1,$2,'SAFE_HARB');
END;$$ LANGUAGE plpgsql STABLE;

CREATE OR REPLACE FUNCTION safe_harbors_value ( boolean ) RETURNS text AS $$
BEGIN
	RETURN export_value($1,'SAFE_HARB');
END;$$ LANGUAGE plpgsql STABLE;