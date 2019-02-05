CREATE OR REPLACE FUNCTION email_sender() RETURNS VARCHAR AS $$
        SELECT email_sender FROM config_email ORDER BY config_email_id DESC LIMIT 1;
$$ LANGUAGE sql STABLE;
