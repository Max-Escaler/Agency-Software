CREATE OR REPLACE VIEW staff_cm AS
SELECT * FROM staff WHERE staff_position_code = 'CASE_MGR';