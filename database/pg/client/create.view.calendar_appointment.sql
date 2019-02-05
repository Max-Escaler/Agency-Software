CREATE OR REPLACE VIEW calendar_appointment AS
SELECT ca.*,
	ca.event_end-ca.event_start AS event_length,
	c.calendar_type_code,
	c.staff_id,
	c.calendar_title
FROM tbl_calendar_appointment ca
	LEFT JOIN calendar c USING ( calendar_id )
WHERE NOT ca.is_deleted;

CREATE OR REPLACE VIEW calendar_appointment_current AS
SELECT * FROM calendar_appointment WHERE (calendar_appointment_resolution_code<>'CANCELLED' OR calendar_appointment_resolution_code IS NULL);

CREATE VIEW calendar_appointment_cancelled AS
SELECT * FROM calendar_appointment WHERE calendar_appointment_resolution_code='CANCELLED';

