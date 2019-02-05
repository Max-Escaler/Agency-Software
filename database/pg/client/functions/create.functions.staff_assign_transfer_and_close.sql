CREATE OR REPLACE FUNCTION staff_assign_close( staff integer, by_staff integer, t_date date ) RETURNS VOID AS $$

BEGIN
IF NOT EXISTS (SELECT * FROM staff_employment_current WHERE staff_id=by_staff) THEN
  RAISE EXCEPTION 'Transfer maker (%) is not a current staff member',by_staff;
END IF;

UPDATE tbl_staff_assign SET
	staff_assign_date_end=t_date,
	sys_log=COALESCE(sys_log||E'\n','')
	|| 'Assignment closed by staff_assign_close('
	|| staff::text || ','
	|| by_staff::text || ','
	|| t_date::text || ')',
	changed_by=by_staff,
	changed_at=current_timestamp
WHERE
	staff_assign_date_end IS NULL
	AND staff_id=staff
;

END; $$ LANGUAGE plpgsql VOLATILE;



CREATE OR REPLACE FUNCTION staff_assign_transfer( from_staff integer, to_staff integer, by_staff integer, t_date date ) RETURNS VOID AS $$
/*
DECLARE
	pnum text;
*/
BEGIN

IF NOT EXISTS (SELECT * FROM staff_employment_current WHERE staff_id=by_staff) THEN
  RAISE EXCEPTION 'Transfer maker (%) is not a current staff member',by_staff;
END IF;

IF NOT EXISTS (SELECT * FROM staff_employment WHERE to_staff=staff_id AND t_date BETWEEN hired_on AND COALESCE(terminated_on,t_date) ) THEN
  RAISE EXCEPTION 'Failed to transfer assignments.  Assignment receiver (%) was not an active staff member on %',to_staff,t_date;
END IF;

INSERT INTO tbl_staff_assign (
	client_id,
	staff_id,
	staff_assign_type_code,
	staff_assign_date,
	staff_assign_date_end,
	send_alert,
	comment,
	added_by,
	changed_by,
	sys_log
)

SELECT
	client_id,
	to_staff AS staff_id,
	staff_assign_type_code,
	t_date AS staff_assign_date,
	NULL AS staff_assign_date_end,
	send_alert,
	comment,
	by_staff AS added_by,
	by_staff AS changed_by,
	'Assignment by staff_assign_transfer('
		|| from_staff::text || ',' 
		|| to_staff::text || ',' 
		|| by_staff::text || ','
		|| t_date::text || ')' AS  sys_log
	FROM staff_assign_current
	WHERE staff_id=from_staff
;

PERFORM staff_assign_close(from_staff,by_staff,t_date-1);
END; $$ LANGUAGE plpgsql VOLATILE;
