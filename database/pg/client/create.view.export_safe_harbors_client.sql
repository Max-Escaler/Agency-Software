CREATE OR REPLACE VIEW export_safe_harbors_client AS

SELECT --distinct x.client_id,
distinct x.client_id,
	x.export_id AS safe_harbors_id,
	c.name_last,
	c.name_first,
	c.name_middle,
	c.name_suffix,
	c.ssn,
	c.dob,
	safe_harbors_value('l_gender',c.gender_code) AS gender,
	safe_harbors_value('l_ethnicity',c.ethnicity_code) AS ethnicity,
--	safe_harbors_value(c.needs_interpreter) AS needs_interpreter,
--	safe_harbors_value('l_language',c.language_code) AS language,
	safe_harbors_value('l_veteran_status',c.veteran_status_code) AS veteran_status,
	safe_harbors_value('l_chronic_homeless_status', (SELECT chronic_homeless_status_code from chronic_homeless_status_asked where client_id = c.client_id order by added_at DESC limit 1)) AS chronic_homeless_status,
	safe_harbors_value('l_highest_education', sh_add.highest_education_code) as highest_education,
	safe_harbors_value('l_yes_no_client', sh_add.immigrant_status_code) as immigrant_status,
	safe_harbors_value('l_sh_school_status', sh_add.sh_school_status_code) as school_status,
	c.changed_at 
FROM client_export_id x
	LEFT JOIN client c USING (client_id)
	LEFT JOIN sh_additional_data sh_add using (client_id)

WHERE export_organization_code='SAFE_HARB'
--	AND sh_add.added_at = (SELECT added_at FROM sh_additional_data WHERE client_id = sh_add.client_id order by added_at DESC limit 1)
	AND c.client_id IS NOT NULL; --needed in case of missed unduplications

