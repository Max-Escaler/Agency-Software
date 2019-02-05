/*
 * Our address function schema is as follows:
 *    - any new address function should take an address_id as a parameter
 *    - to obtain the preferred address, address_id(donor_id) should be modified
 *        to meet current requirements for what constitutes a preferred address.
 */


CREATE OR REPLACE FUNCTION address_mail( addr_id int ) RETURNS text AS $$

     SELECT

          COALESCE(don.name_prefix || ' ','') ||
          COALESCE(don.name_first || ' ','') ||
             COALESCE(don.name_middle || ' ','') ||
             COALESCE(don.name_last,'') ||
             COALESCE(', '||don.name_suffix,'') ||
             CASE WHEN COALESCE(don.name_prefix,don.name_first,don.name_middle,don.name_last,don.name_suffix)
                      IS NOT NULL THEN E'\n' ELSE '' END ||

             COALESCE(don.name2_prefix || ' ','') ||
             COALESCE(don.name2_first || ' ','') ||
             COALESCE(don.name2_middle || ' ','') ||
             COALESCE(don.name2_last,'') ||
             COALESCE(', '||don.name2_suffix,'') ||
             CASE WHEN COALESCE(don.name2_prefix,don.name2_first,don.name2_middle,don.name2_last,don.name2_suffix)
                      IS NOT NULL THEN E'\n' ELSE '' END ||

             COALESCE(don.title || E'\n','') ||
             COALESCE(don.organization || E'\n','') ||
             COALESCE(don.address1 || E'\n','') ||
             COALESCE(don.address2 || E'\n','') ||
             COALESCE(don.city || ', ','') ||
             COALESCE(don.state_code || '  ','') ||
             COALESCE(don.zipcode,'') ||
             COALESCE(E'\n' || don.country,'')

    FROM tbl_address don WHERE address_id = $1 AND NOT is_deleted;

$$ LANGUAGE sql STABLE;

CREATE OR REPLACE FUNCTION address_names( add_id int ) RETURNS text AS $$
DECLARE

        name1   TEXT;
        name2   TEXT;
        names   TEXT;
        don             RECORD;
        sep             TEXT;

BEGIN
        sep := ' & ';

        SELECT INTO don * FROM tbl_address WHERE address_id = add_id AND NOT is_deleted;

        name1 :=

                COALESCE(TRIM(don.name_prefix) || ' ','') ||
                COALESCE(TRIM(don.name_first) || ' ','') ||
                COALESCE(TRIM(don.name_middle) || ' ','') ||
                COALESCE(TRIM(don.name_last),'') ||
                COALESCE(', '||TRIM(don.name_suffix),'');

        name2 :=

                COALESCE(TRIM(don.name2_prefix) || ' ','') ||
                COALESCE(TRIM(don.name2_first) || ' ','') ||
                COALESCE(TRIM(don.name2_middle) || ' ','') ||
                COALESCE(TRIM(don.name2_last),'') ||
                COALESCE(', '||TRIM(don.name2_suffix),'');

        names := name1 || CASE WHEN name2 <> '' THEN sep || name2 ELSE '' END;

        RETURN names;

END; $$ LANGUAGE plpgsql STABLE;

CREATE OR REPLACE FUNCTION address_names_short( add_id int ) RETURNS text AS $$
DECLARE

        name1   TEXT;
        name2   TEXT;
        names   TEXT;
        don             RECORD;
        sep             TEXT;

BEGIN
        sep := ' & ';

        SELECT INTO don * FROM tbl_address WHERE address_id = add_id AND NOT is_deleted;

        name1 :=
                COALESCE(TRIM(don.name_first) || ' ','') ||
                COALESCE(TRIM(don.name_last),'');

        name2 :=
                COALESCE(TRIM(don.name2_first) || ' ','') ||
                COALESCE(TRIM(don.name2_last),'');

        names := name1 || CASE WHEN name2 <> '' THEN sep || name2 ELSE '' END;

        RETURN names;

END; $$ LANGUAGE plpgsql STABLE;

CREATE OR REPLACE FUNCTION address_emails( add_id int ) RETURNS text AS $$
DECLARE

        email1  TEXT;
        email2  TEXT;
        emails  TEXT;
        don             RECORD;
        sep             TEXT;

BEGIN
        sep := ' & ';

        SELECT INTO don * FROM tbl_address WHERE address_id = add_id AND NOT is_deleted;

        email1 := COALESCE(don.name_email,'');
        email2 := COALESCE(don.name2_email,'');

        emails := email1 || CASE WHEN email2 <> '' THEN sep || email2 ELSE '' END;

        RETURN emails;

END; $$ LANGUAGE plpgsql STABLE;

CREATE OR REPLACE FUNCTION address_salutation( add_id int ) RETURNS text AS $$

     SELECT salutation FROM tbl_address WHERE address_id = $1 AND NOT is_deleted;

$$ LANGUAGE sql STABLE;

