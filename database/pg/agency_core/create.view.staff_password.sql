CREATE OR REPLACE VIEW staff_password AS (
	SELECT *,
			staff_password_date_end-current_date AS expires_day_count,
			staff_password_date_end-(SELECT expiration_warning_days FROM config_staff_password_current) AS expiration_warn_on
	FROM tbl_staff_password
	WHERE NOT is_deleted
);

CREATE OR REPLACE VIEW staff_password_current AS SELECT DISTINCT ON (staff_id) * FROM staff_password WHERE current_date BETWEEN staff_password_date AND COALESCE(staff_password_date_end,current_date) ORDER BY staff_id,staff_password_id DESC;

