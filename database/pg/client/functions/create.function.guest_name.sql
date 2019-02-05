CREATE FUNCTION guest_name( integer ) RETURNS TEXT AS $$

	SELECT name_full FROM guest WHERE guest_id=$1;

$$ LANGUAGE SQL STABLE;
