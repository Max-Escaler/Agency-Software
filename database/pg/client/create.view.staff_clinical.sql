CREATE OR REPLACE VIEW staff_clinical AS
SELECT * FROM staff WHERE agency_program_code = 'CLINICAL';
