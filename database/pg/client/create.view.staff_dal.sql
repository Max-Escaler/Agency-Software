CREATE OR REPLACE VIEW staff_dal AS
SELECT * FROM staff WHERE is_active AND old_mh_id IS NOT NULL AND kc_staff_id IS NOT NULL;
