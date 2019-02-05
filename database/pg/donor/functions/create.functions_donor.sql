CREATE OR REPLACE FUNCTION address( did int ) RETURNS address AS $$

    SELECT * FROM address WHERE donor_id = $1 AND COALESCE(address_date_end,CURRENT_DATE + 1) > CURRENT_DATE;

$$ LANGUAGE sql STABLE;

CREATE OR REPLACE FUNCTION address_id( int ) RETURNS int AS '
        SELECT address_id  FROM address( $1 );
' LANGUAGE 'sql';

CREATE OR REPLACE FUNCTION donor_name( int4 ) RETURNS text AS $$

        SELECT donor_name FROM donor WHERE donor_id=$1;

$$ LANGUAGE sql STABLE;

CREATE OR REPLACE FUNCTION set_preferred_address() RETURNS TRIGGER AS $$
BEGIN
        IF NEW.preferred_address_code IS NULL THEN
                NEW.preferred_address_code := CASE WHEN NEW.donor_type_code = 'INDI' THEN 'HOME'
                                ELSE 'BUSINESS' END;
        END IF;
        RETURN NEW;
END; $$ LANGUAGE plpgsql STABLE;

CREATE TRIGGER set_donor_preferred_address
    BEFORE INSERT ON tbl_donor FOR EACH ROW
    EXECUTE PROCEDURE set_preferred_address();

