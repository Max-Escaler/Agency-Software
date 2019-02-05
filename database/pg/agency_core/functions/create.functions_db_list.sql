CREATE OR REPLACE FUNCTION is_test_db() RETURNS BOOLEAN AS $$
DECLARE
	istdb BOOLEAN;
BEGIN
	SELECT INTO istdb is_test_db FROM db_list WHERE db_name=current_database();
	RETURN COALESCE(istdb,FALSE);
END;$$ LANGUAGE plpgsql STABLE;

CREATE OR REPLACE FUNCTION get_db_name() RETURNS TEXT AS $$
DECLARE
	dbname TEXT;
BEGIN
	SELECT INTO dbname description FROM db_list WHERE db_name=current_database();
	RETURN COALESCE(dbname,current_database()||' (Unknown DB)');
END;$$ LANGUAGE plpgsql STABLE;

CREATE OR REPLACE FUNCTION agency_base_url() RETURNS text AS $$
DECLARE
	dburl TEXT;
BEGIN
	SELECT INTO dburl primary_url FROM db_list WHERE db_name=current_database();
	RETURN COALESCE(dburl,'');
END;$$ LANGUAGE plpgsql STABLE;

CREATE OR REPLACE FUNCTION is_internal_access( cidr ) RETURNS BOOLEAN AS $$
BEGIN
	RETURN COALESCE((SELECT is_internal FROM db_access WHERE COALESCE($1, inet_client_addr(),'127.0.0.1'::cidr) <<= access_ip),false);
END;$$ LANGUAGE plpgsql STABLE;

CREATE OR REPLACE FUNCTION is_internal_access() RETURNS BOOLEAN AS $$

	SELECT is_internal_access( NULL::cidr);

$$ LANGUAGE sql STABLE;

