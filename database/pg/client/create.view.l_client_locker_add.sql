CREATE OR REPLACE VIEW l_client_locker_add AS
SELECT * FROM l_client_locker
WHERE client_locker_code NOT IN (SELECT client_locker_code FROM client_locker_assignment_recent)
ORDER BY client_locker_code;
