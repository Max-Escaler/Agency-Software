/*
 * Staff Title
 */

--Moved function make_staff_title to its own file. 


CREATE OR REPLACE FUNCTION staff_title( sid int4 ) RETURNS text AS $$
DECLARE
        stafftitle      text;
BEGIN
        SELECT INTO stafftitle staff_title FROM staff WHERE staff_id=sid;
        RETURN stafftitle;
END; $$ LANGUAGE plpgsql STABLE;

/*
 * Supervision functions
 */

CREATE OR REPLACE FUNCTION is_supervised_by( sid int, q_sid int ) RETURNS boolean AS $$
/*
 * Checks if staff (sid) is supervised by staff (q_sid)
 */
DECLARE
	sup int;
BEGIN

	SELECT INTO sup supervised_by FROM staff WHERE staff_id = sid;

	IF sup = q_sid THEN

		/*
		 * Supervisor = Query? --> true
		 */

		RETURN TRUE;

	ELSIF sup IS NULL OR sup = sid THEN

		/*
		 * No supervisor, or supervised by self
		 */

		RETURN FALSE;

	ELSE

		/*
		 * Check supervisor's supervisor
		 */

		RETURN is_supervised_by(sup,q_sid);

	END IF;

	RETURN FALSE;

END$$ LANGUAGE plpgsql STABLE;

CREATE OR REPLACE VIEW staff_employment AS SELECT

	staff_employment_id,
	staff_id,
	COALESCE(staff_title,make_staff_title(staff_position_code,agency_facility_code,agency_project_code,staff_shift_code)) AS staff_title,
	agency_program_code,
	agency_project_code,
	staff_position_code,
	agency_facility_code,
	staff_shift_code,
	agency_staff_type_code,
	staff_employment_status_code,
	day_off_1_code,
	day_off_2_code,
	hired_on,
	terminated_on,
	supervised_by,
	comment,
	added_by,
	added_at,
	changed_by,
	changed_at,
	is_deleted,
	deleted_at,
	deleted_by,
	deleted_comment,
	sys_log

FROM tbl_staff_employment WHERE NOT is_deleted;

CREATE VIEW staff_employment_current AS
SELECT DISTINCT ON (staff_id) * FROM staff_employment
WHERE hired_on <= CURRENT_DATE AND (terminated_on IS NULL OR terminated_on > CURRENT_DATE)
ORDER BY staff_id, staff_employment_id;

CREATE VIEW staff_employment_latest AS
SELECT DISTINCT ON (staff_id) * FROM staff_employment
WHERE hired_on <= CURRENT_DATE
ORDER BY staff_id, hired_on DESC,staff_employment_id;

