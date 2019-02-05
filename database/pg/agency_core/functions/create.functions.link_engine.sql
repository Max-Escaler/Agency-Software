-- Object, ID, label, action [, rec_init]
-- rec_init currently handled as comma separated field, value
-- e.g., 'client_id,4,ethnicity_code,UNKNOWN'

CREATE OR REPLACE FUNCTION link_engine( varchar, varchar, varchar, varchar ) returns varchar AS $$

SELECT 'link_engine(' || $1 || '||' || $2 || '||' || COALESCE($3,'NULL') || '||' || $4 || ')';

$$ LANGUAGE sql STABLE;

CREATE OR REPLACE FUNCTION link_engine( varchar, varchar, varchar, varchar, text ) returns varchar AS $$

SELECT 'link_engine(' || $1 || '||' || $2 || '||' || COALESCE($3,'NULL') || '||' || $4 || '||' || $5::text || ')';

$$ LANGUAGE sql STABLE;

