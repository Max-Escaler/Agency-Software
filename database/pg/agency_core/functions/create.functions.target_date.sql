CREATE OR REPLACE FUNCTION target_date() RETURNS date AS $$
        SELECT target_date FROM target_date_current;
$$ LANGUAGE sql STABLE;

CREATE OR REPLACE FUNCTION target_date_effective_at() RETURNS timestamp AS $$
        SELECT effective_at FROM target_date_current;
$$ LANGUAGE sql STABLE;
