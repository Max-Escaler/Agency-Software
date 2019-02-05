--
-- Data for Name: tbl_report; Type: TABLE DATA; Schema: public; Owner: agency_testing
--

INSERT INTO tbl_report (report_id, report_code, report_title, report_category_code, report_header, report_footer, report_comment, client_page, suppress_output_codes, override_sql_security, rows_per_page, output_template_codes, permission_type_codes, variables, css_class, css_id, block_merge_force_count, block_merge_specific, added_by, added_at, changed_by, changed_at, is_deleted, deleted_at, deleted_by, deleted_comment, sys_log) VALUES (DEFAULT, 'PROFILE_REPORT', 'Profile Report', NULL, NULL, NULL, NULL, NULL, NULL, false, NULL, NULL, NULL, 'DATE startdate "Enter Start Date"
DATE enddate "Enter End Date"', NULL, NULL, NULL, NULL, sys_user(), '2012-05-14 17:30:43', sys_user(), '2012-05-28 14:58:49', false, NULL, NULL, NULL, NULL);

INSERT INTO tbl_report_block (report_block_id, report_code, is_enabled, report_block_sql, report_block_title, report_block_header, report_block_footer, report_block_comment, message_if_empty, message_if_error, suppress_output_codes, suppress_header_row, suppress_row_numbers, execution_required, override_sql_security, permission_type_codes, sort_order_id_manual, report_block_type_code, css_class, css_id, block_merge_name, sql_library_id, added_by, added_at, changed_by, changed_at, is_deleted, deleted_at, deleted_by, deleted_comment, sys_log) VALUES (DEFAULT, 'PROFILE_REPORT', true, 'CREATE TEMPORARY TABLE hold AS (SELECT current_date AS date,client_id,''All''::text AS description FROM client);', 'Initial universe creation', NULL, NULL, 'Adjust this SQL to fit who you are reporting on', NULL, NULL, '{O_TEMPLATE,O_SCREEN}', false, false, false, false, NULL, -2, 'TABLE', NULL, NULL, NULL, NULL, sys_user(), current_timestamp, sys_user(), current_timestamp, false, NULL, NULL, NULL, NULL);

INSERT INTO tbl_report_block (report_block_id, report_code, is_enabled, report_block_sql, report_block_title, report_block_header, report_block_footer, report_block_comment, message_if_empty, message_if_error, suppress_output_codes, suppress_header_row, suppress_row_numbers, execution_required, override_sql_security, permission_type_codes, sort_order_id_manual, report_block_type_code, css_class, css_id, block_merge_name, sql_library_id, added_by, added_at, changed_by, changed_at, is_deleted, deleted_at, deleted_by, deleted_comment, sys_log) VALUES (DEFAULT, 'PROFILE_REPORT', true, 'CREATE TEMPORARY TABLE id_list AS
         SELECT DISTINCT client_id FROM hold', '1', NULL, NULL, NULL, NULL, NULL, '{O_TEMPLATE,O_SCREEN}', false, false, false, false, NULL, -1, 'TABLE', NULL, NULL, NULL, NULL, sys_user(), current_timestamp, sys_user(), current_timestamp, false, NULL, NULL, NULL, NULL);
INSERT INTO tbl_report_block (report_block_id, report_code, is_enabled, report_block_sql, report_block_title, report_block_header, report_block_footer, report_block_comment, message_if_empty, message_if_error, suppress_output_codes, suppress_header_row, suppress_row_numbers, execution_required, override_sql_security, permission_type_codes, sort_order_id_manual, report_block_type_code, css_class, css_id, block_merge_name, sql_library_id, added_by, added_at, changed_by, changed_at, is_deleted, deleted_at, deleted_by, deleted_comment, sys_log) VALUES (DEFAULT, 'PROFILE_REPORT', true, 'SELECT ''Total'' AS description , COUNT(client_id)
                              FROM id_list', 'Unduplicated Clients', NULL, NULL, NULL, NULL, NULL, NULL, false, false, false, false, NULL, NULL, 'TABLE', NULL, NULL, NULL, NULL, sys_user(), current_timestamp, sys_user(), current_timestamp, false, NULL, NULL, NULL, NULL);
INSERT INTO tbl_report_block (report_block_id, report_code, is_enabled, report_block_sql, report_block_title, report_block_header, report_block_footer, report_block_comment, message_if_empty, message_if_error, suppress_output_codes, suppress_header_row, suppress_row_numbers, execution_required, override_sql_security, permission_type_codes, sort_order_id_manual, report_block_type_code, css_class, css_id, block_merge_name, sql_library_id, added_by, added_at, changed_by, changed_at, is_deleted, deleted_at, deleted_by, deleted_comment, sys_log) VALUES (DEFAULT, 'PROFILE_REPORT', true, 'SELECT COUNT(id.client_id), eth.description
         FROM id_list id
              LEFT JOIN ethnicity c ON (c.client_id=id.client_id)
              LEFT JOIN l_ethnicity eth ON (c.ethnicity_code=eth.ethnicity_code)
              GROUP BY eth.description
              ORDER BY eth.description', 'ethnicity', NULL, NULL, NULL, NULL, NULL, NULL, false, false, false, false, NULL, NULL, 'TABLE', NULL, NULL, NULL, NULL, sys_user(), current_timestamp, sys_user(), current_timestamp, false, NULL, NULL, NULL, NULL);
INSERT INTO tbl_report_block (report_block_id, report_code, is_enabled, report_block_sql, report_block_title, report_block_header, report_block_footer, report_block_comment, message_if_empty, message_if_error, suppress_output_codes, suppress_header_row, suppress_row_numbers, execution_required, override_sql_security, permission_type_codes, sort_order_id_manual, report_block_type_code, css_class, css_id, block_merge_name, sql_library_id, added_by, added_at, changed_by, changed_at, is_deleted, deleted_at, deleted_by, deleted_comment, sys_log) VALUES (DEFAULT, 'PROFILE_REPORT', true, 'SELECT COUNT(id.client_id), ethnicity_simple(ethnicity_code) AS description
                          FROM id_list id
                                LEFT JOIN ethnicity c USING (client_id)
                          GROUP BY 2 ORDER BY 2', 'Ethnicity (Simple)', NULL, NULL, NULL, NULL, NULL, NULL, false, false, false, false, NULL, NULL, 'TABLE', NULL, NULL, NULL, NULL, sys_user(), current_timestamp, sys_user(), current_timestamp, false, NULL, NULL, NULL, NULL);
INSERT INTO tbl_report_block (report_block_id, report_code, is_enabled, report_block_sql, report_block_title, report_block_header, report_block_footer, report_block_comment, message_if_empty, message_if_error, suppress_output_codes, suppress_header_row, suppress_row_numbers, execution_required, override_sql_security, permission_type_codes, sort_order_id_manual, report_block_type_code, css_class, css_id, block_merge_name, sql_library_id, added_by, added_at, changed_by, changed_at, is_deleted, deleted_at, deleted_by, deleted_comment, sys_log) VALUES (DEFAULT, 'PROFILE_REPORT', true, 'SELECT COUNT(id.client_id), g.description
         FROM id_list id
               LEFT JOIN client c ON (c.client_id=id.client_id)
               LEFT JOIN l_gender g ON (c.gender_code=g.gender_code)
         GROUP BY g.description
         ORDER BY g.description', 'gender', NULL, NULL, NULL, NULL, NULL, NULL, false, false, false, false, NULL, NULL, 'TABLE', NULL, NULL, NULL, NULL, sys_user(), current_timestamp, sys_user(), current_timestamp, false, NULL, NULL, NULL, NULL);
INSERT INTO tbl_report_block (report_block_id, report_code, is_enabled, report_block_sql, report_block_title, report_block_header, report_block_footer, report_block_comment, message_if_empty, message_if_error, suppress_output_codes, suppress_header_row, suppress_row_numbers, execution_required, override_sql_security, permission_type_codes, sort_order_id_manual, report_block_type_code, css_class, css_id, block_merge_name, sql_library_id, added_by, added_at, changed_by, changed_at, is_deleted, deleted_at, deleted_by, deleted_comment, sys_log) VALUES (DEFAULT, 'PROFILE_REPORT', true, 'SELECT COUNT(id.client_id), l.description,
         needs_interpreter_code AS "Needs Interpreter"
         FROM id_list id
               LEFT JOIN client c ON (c.client_id=id.client_id)
               LEFT JOIN l_language l ON (c.language_code=l.language_code)
         GROUP BY l.description, "Needs Interpreter"
         ORDER BY l.description', 'language', NULL, NULL, NULL, NULL, NULL, NULL, false, false, false, false, NULL, NULL, 'TABLE', NULL, NULL, NULL, NULL, sys_user(), current_timestamp, sys_user(), current_timestamp, false, NULL, NULL, NULL, NULL);
INSERT INTO tbl_report_block (report_block_id, report_code, is_enabled, report_block_sql, report_block_title, report_block_header, report_block_footer, report_block_comment, message_if_empty, message_if_error, suppress_output_codes, suppress_header_row, suppress_row_numbers, execution_required, override_sql_security, permission_type_codes, sort_order_id_manual, report_block_type_code, css_class, css_id, block_merge_name, sql_library_id, added_by, added_at, changed_by, changed_at, is_deleted, deleted_at, deleted_by, deleted_comment, sys_log) VALUES (DEFAULT, 'PROFILE_REPORT', true, 'SELECT COUNT(id.client_id),
         CASE
                 WHEN (SELECT annual_income FROM income WHERE income.client_id=id.client_id AND income_date <= ''$enddate'' AND (income_date_end IS NULL OR income_date_end >=''$startdate'') ORDER BY income_date DESC LIMIT 1) < 16351 THEN ''< 30%''
                 WHEN (SELECT annual_income FROM income WHERE income.client_id=id.client_id AND income_date <= ''$enddate'' AND (income_date_end IS NULL OR income_date_end >=''$startdate'') ORDER BY income_date DESC LIMIT 1) BETWEEN 16351 AND 27250 THEN ''< 50%''
                 WHEN (SELECT annual_income FROM income WHERE income.client_id=id.client_id AND income_date <= ''$enddate'' AND (income_date_end IS NULL OR income_date_end >=''$startdate'') ORDER BY income_date DESC LIMIT 1) BETWEEN 27251 AND 38100 THEN ''< 80%''
                 WHEN (SELECT annual_income FROM income WHERE income.client_id=id.client_id AND income_date <= ''$enddate'' AND (income_date_end IS NULL OR income_date_end >=''$startdate'') ORDER BY income_date DESC LIMIT 1) >= 38100 THEN ''> 80%''
                 ELSE ''Unknown''
         END AS description
         FROM id_list id
         GROUP BY description
         ORDER BY description', 'income (HUD Guidlines)', NULL, NULL, NULL, NULL, NULL, NULL, false, false, false, false, NULL, NULL, 'TABLE', NULL, NULL, NULL, NULL, sys_user(), current_timestamp, sys_user(), current_timestamp, false, NULL, NULL, NULL, NULL);
INSERT INTO tbl_report_block (report_block_id, report_code, is_enabled, report_block_sql, report_block_title, report_block_header, report_block_footer, report_block_comment, message_if_empty, message_if_error, suppress_output_codes, suppress_header_row, suppress_row_numbers, execution_required, override_sql_security, permission_type_codes, sort_order_id_manual, report_block_type_code, css_class, css_id, block_merge_name, sql_library_id, added_by, added_at, changed_by, changed_at, is_deleted, deleted_at, deleted_by, deleted_comment, sys_log) VALUES (DEFAULT, 'PROFILE_REPORT', true, 'SELECT COUNT(id.client_id), 
         (SELECT i.description FROM income LEFT JOIN l_income i ON (i.income_code=income.income_primary_code)
          WHERE income.client_id=id.client_id AND income_date <= ''$enddate'' AND (income_date_end IS NULL OR income_date_end >= ''$startdate'')
          ORDER BY income_date DESC LIMIT 1) AS description
         FROM id_list id
         GROUP BY description
         ORDER BY description', 'primary income source', NULL, NULL, NULL, NULL, NULL, NULL, false, false, false, false, NULL, NULL, 'TABLE', NULL, NULL, NULL, NULL, sys_user(), current_timestamp, sys_user(), current_timestamp, false, NULL, NULL, NULL, NULL);
INSERT INTO tbl_report_block (report_block_id, report_code, is_enabled, report_block_sql, report_block_title, report_block_header, report_block_footer, report_block_comment, message_if_empty, message_if_error, suppress_output_codes, suppress_header_row, suppress_row_numbers, execution_required, override_sql_security, permission_type_codes, sort_order_id_manual, report_block_type_code, css_class, css_id, block_merge_name, sql_library_id, added_by, added_at, changed_by, changed_at, is_deleted, deleted_at, deleted_by, deleted_comment, sys_log) VALUES (DEFAULT, 'PROFILE_REPORT', true, 'SELECT (SUM(annual_income)::numeric/(select count(distinct client_id) from id_list)::numeric)::numeric(10,2) as count,
                      ''average income'' as description
	FROM (
             SELECT DISTINCT ON (client_id) client_id, annual_income 
             FROM (SELECT client_id,annual_income FROM income 
                     WHERE client_id IN (SELECT client_id FROM id_list) AND (income_date_end >= ''$startdate'' 
                      OR income_date_end IS NULL) AND income_date <= ''$enddate'' ORDER BY income_date DESC) as inc1) as inc2', 'average_income', NULL, NULL, NULL, NULL, NULL, NULL, false, false, false, false, NULL, NULL, 'TABLE', NULL, NULL, NULL, NULL, sys_user(), current_timestamp, sys_user(), current_timestamp, false, NULL, NULL, NULL, NULL);
INSERT INTO tbl_report_block (report_block_id, report_code, is_enabled, report_block_sql, report_block_title, report_block_header, report_block_footer, report_block_comment, message_if_empty, message_if_error, suppress_output_codes, suppress_header_row, suppress_row_numbers, execution_required, override_sql_security, permission_type_codes, sort_order_id_manual, report_block_type_code, css_class, css_id, block_merge_name, sql_library_id, added_by, added_at, changed_by, changed_at, is_deleted, deleted_at, deleted_by, deleted_comment, sys_log) VALUES (DEFAULT, 'PROFILE_REPORT', true, 'SELECT COUNT(id.client_id), d.description
         FROM id_list id
              LEFT JOIN 
                   (SELECT DISTINCT ON (client_id,disability_code) * 
                    FROM disability 
                    WHERE disability_date <= ''$enddate'' AND (disability_date_end IS NULL OR disability_date_end >= ''$startdate'')
                    ORDER BY client_id, disability_code) dis ON (id.client_id=dis.client_id)
              LEFT JOIN l_disability d ON (dis.disability_code=d.disability_code)
         
         GROUP BY d.description
         ORDER BY d.description', 'disability', NULL, NULL, NULL, NULL, NULL, NULL, false, false, false, false, NULL, NULL, 'TABLE', NULL, NULL, NULL, NULL, sys_user(), current_timestamp, sys_user(), current_timestamp, false, NULL, NULL, NULL, NULL);
INSERT INTO tbl_report_block (report_block_id, report_code, is_enabled, report_block_sql, report_block_title, report_block_header, report_block_footer, report_block_comment, message_if_empty, message_if_error, suppress_output_codes, suppress_header_row, suppress_row_numbers, execution_required, override_sql_security, permission_type_codes, sort_order_id_manual, report_block_type_code, css_class, css_id, block_merge_name, sql_library_id, added_by, added_at, changed_by, changed_at, is_deleted, deleted_at, deleted_by, deleted_comment, sys_log) VALUES (DEFAULT, 'PROFILE_REPORT', true, 'SELECT COUNT(DISTINCT d.client_id),''substance abuse'' AS description
	FROM id_list id LEFT JOIN disability d USING (client_id) WHERE disability_code IN (''2'',''3'')
         AND  disability_date <= ''$enddate'' AND (disability_date_end IS NULL OR disability_date_end >= ''$startdate'')
UNION
	SELECT COUNT(DISTINCT d.client_id),''physical impairment'' AS description
	FROM id_list id LEFT JOIN disability d USING (client_id) WHERE disability_code IN (''4'',''6'',''5'',''45'',''44'',''8'',''7'')
         AND  disability_date <= ''$enddate'' AND (disability_date_end IS NULL OR disability_date_end >= ''$startdate'')
UNION
	SELECT COUNT(DISTINCT d.client_id),''any disability'' AS description
	FROM id_list id LEFT JOIN disability d USING (client_id) WHERE disability_date <= ''$enddate'' 
         AND (disability_date_end IS NULL OR disability_date_end >= ''$startdate'')', 'disability_grouped', NULL, NULL, NULL, NULL, NULL, NULL, false, false, false, false, NULL, NULL, 'TABLE', NULL, NULL, NULL, NULL, sys_user(), current_timestamp, sys_user(), current_timestamp, false, NULL, NULL, NULL, NULL);
INSERT INTO tbl_report_block (report_block_id, report_code, is_enabled, report_block_sql, report_block_title, report_block_header, report_block_footer, report_block_comment, message_if_empty, message_if_error, suppress_output_codes, suppress_header_row, suppress_row_numbers, execution_required, override_sql_security, permission_type_codes, sort_order_id_manual, report_block_type_code, css_class, css_id, block_merge_name, sql_library_id, added_by, added_at, changed_by, changed_at, is_deleted, deleted_at, deleted_by, deleted_comment, sys_log) VALUES (DEFAULT, 'PROFILE_REPORT', true, 'SELECT COUNT(id.client_id),
         CASE
                 WHEN ((DATE(current_timestamp) - dob)/365.24)::integer < 18 THEN ''< 18''
                 WHEN ((DATE(current_timestamp) - dob)/365.24)::integer BETWEEN 18 AND 34 THEN ''18 - 34''
                 WHEN ((DATE(current_timestamp) - dob)/365.24)::integer BETWEEN 35 AND 59 THEN ''35 - 59''
                 WHEN ((DATE(current_timestamp) - dob)/365.24)::integer BETWEEN 60 AND 74 THEN ''60 - 74''
                 WHEN ((DATE(current_timestamp) - dob)/365.24)::integer BETWEEN 75 AND 84 THEN ''75 - 84''
                 WHEN ((DATE(current_timestamp) - dob)/365.24)::integer >= 85 THEN ''85+''
         END AS description
         FROM id_list id
               LEFT JOIN client c ON (c.client_id=id.client_id)
         GROUP BY description
         ORDER BY description', 'age', NULL, NULL, NULL, NULL, NULL, NULL, false, false, false, false, NULL, NULL, 'TABLE', NULL, NULL, NULL, NULL, sys_user(), current_timestamp, sys_user(), current_timestamp, false, NULL, NULL, NULL, NULL);
INSERT INTO tbl_report_block (report_block_id, report_code, is_enabled, report_block_sql, report_block_title, report_block_header, report_block_footer, report_block_comment, message_if_empty, message_if_error, suppress_output_codes, suppress_header_row, suppress_row_numbers, execution_required, override_sql_security, permission_type_codes, sort_order_id_manual, report_block_type_code, css_class, css_id, block_merge_name, sql_library_id, added_by, added_at, changed_by, changed_at, is_deleted, deleted_at, deleted_by, deleted_comment, sys_log) VALUES (DEFAULT, 'PROFILE_REPORT', true, 'SELECT description,
                        COUNT(client_id) AS count
                   FROM hold
                   GROUP BY description', 'bednights', NULL, NULL, NULL, NULL, NULL, NULL, false, false, false, false, NULL, NULL, 'TABLE', NULL, NULL, NULL, NULL, sys_user(), current_timestamp, sys_user(), current_timestamp, false, NULL, NULL, NULL, NULL);
INSERT INTO tbl_report_block (report_block_id, report_code, is_enabled, report_block_sql, report_block_title, report_block_header, report_block_footer, report_block_comment, message_if_empty, message_if_error, suppress_output_codes, suppress_header_row, suppress_row_numbers, execution_required, override_sql_security, permission_type_codes, sort_order_id_manual, report_block_type_code, css_class, css_id, block_merge_name, sql_library_id, added_by, added_at, changed_by, changed_at, is_deleted, deleted_at, deleted_by, deleted_comment, sys_log) VALUES (DEFAULT, 'PROFILE_REPORT', true, 'SELECT COUNT(id.client_id),
         CASE
                 WHEN ((DATE(current_timestamp) - dob)/365.24)::integer < 5 THEN ''< 5''
                 WHEN ((DATE(current_timestamp) - dob)/365.24)::integer BETWEEN 6 AND 11 THEN ''6 - 11''
                 WHEN ((DATE(current_timestamp) - dob)/365.24)::integer BETWEEN 12 AND 17 THEN ''12 - 17''
                 WHEN ((DATE(current_timestamp) - dob)/365.24)::integer BETWEEN 18 AND 21 THEN ''18 - 21''
                 WHEN ((DATE(current_timestamp) - dob)/365.24)::integer BETWEEN 22 AND 44 THEN ''22 - 44''
                 WHEN ((DATE(current_timestamp) - dob)/365.24)::integer BETWEEN 45 AND 54 THEN ''45 - 54''
                 WHEN ((DATE(current_timestamp) - dob)/365.24)::integer BETWEEN 55 AND 69 THEN ''55 - 69''
                 WHEN ((DATE(current_timestamp) - dob)/365.24)::integer >= 70 THEN ''70+''
         END AS description
         FROM id_list id
               LEFT JOIN client c ON (c.client_id=id.client_id)
         GROUP BY description
         ORDER BY description', 'age_alternate_ranges', NULL, NULL, NULL, NULL, NULL, NULL, false, false, false, false, NULL, NULL, 'TABLE', NULL, NULL, NULL, NULL, sys_user(), current_timestamp, sys_user(), current_timestamp, false, NULL, NULL, NULL, NULL);
INSERT INTO tbl_report_block (report_block_id, report_code, is_enabled, report_block_sql, report_block_title, report_block_header, report_block_footer, report_block_comment, message_if_empty, message_if_error, suppress_output_codes, suppress_header_row, suppress_row_numbers, execution_required, override_sql_security, permission_type_codes, sort_order_id_manual, report_block_type_code, css_class, css_id, block_merge_name, sql_library_id, added_by, added_at, changed_by, changed_at, is_deleted, deleted_at, deleted_by, deleted_comment, sys_log) VALUES (DEFAULT, 'PROFILE_REPORT', true, '
		  SELECT (SUM((''$enddate'' - c.dob)/365.24)/COUNT(client_id))::numeric(3,1) AS count,h.description
		  FROM hold h
		  LEFT JOIN tbl_client c USING(client_id)
		  GROUP BY h.description
		  ORDER BY h.description', 'average age', NULL, NULL, NULL, NULL, NULL, NULL, false, false, false, false, NULL, NULL, 'TABLE', NULL, NULL, NULL, NULL, sys_user(), current_timestamp, sys_user(), current_timestamp, false, NULL, NULL, NULL, NULL);
INSERT INTO tbl_report_block (report_block_id, report_code, is_enabled, report_block_sql, report_block_title, report_block_header, report_block_footer, report_block_comment, message_if_empty, message_if_error, suppress_output_codes, suppress_header_row, suppress_row_numbers, execution_required, override_sql_security, permission_type_codes, sort_order_id_manual, report_block_type_code, css_class, css_id, block_merge_name, sql_library_id, added_by, added_at, changed_by, changed_at, is_deleted, deleted_at, deleted_by, deleted_comment, sys_log) VALUES (DEFAULT, 'PROFILE_REPORT', true, 'SELECT COUNT(id.client_id), v.description
   FROM id_list id
       LEFT JOIN client c ON (c.client_id=id.client_id)
       LEFT JOIN l_veteran_status v ON (v.veteran_status_code=c.veteran_status_code)
   GROUP BY v.description
   ORDER BY v.description', 'veteran status', NULL, NULL, NULL, NULL, NULL, NULL, false, false, false, false, NULL, NULL, 'TABLE', NULL, NULL, NULL, NULL, sys_user(), current_timestamp, sys_user(), current_timestamp, false, NULL, NULL, NULL, NULL);
INSERT INTO tbl_report_block (report_block_id, report_code, is_enabled, report_block_sql, report_block_title, report_block_header, report_block_footer, report_block_comment, message_if_empty, message_if_error, suppress_output_codes, suppress_header_row, suppress_row_numbers, execution_required, override_sql_security, permission_type_codes, sort_order_id_manual, report_block_type_code, css_class, css_id, block_merge_name, sql_library_id, added_by, added_at, changed_by, changed_at, is_deleted, deleted_at, deleted_by, deleted_comment, sys_log) VALUES (DEFAULT, 'PROFILE_REPORT', false, 'SELECT COUNT(id.client_id), ''Documented as YES'' as description
FROM id_list id 
LEFT JOIN residence_own r_d ON (r_d.client_id=id.client_id)  WHERE chronic_homeless_status_code LIKE ''YES%''
AND residence_date = (SELECT min(residence_date) from residence_own rd2 where rd2.client_id = id.client_id AND 
(residence_date <= ''$enddate'' AND (residence_date_end >= ''$startdate'' OR residence_date_end IS NULL)))
UNION 
SELECT COUNT(id.client_id), ''Self-Reported as YES'' as description
FROM id_list id 
LEFT JOIN chronic_homeless_status_asked ch ON (ch.client_id=id.client_id)  WHERE chronic_homeless_status_code LIKE ''YES%''
AND ch.client_id NOT IN 
  (SELECT client_id FROM residence_own WHERE (chronic_homeless_status_code LIKE ''YES%'' or chronic_homeless_status_code = ''NO'') AND residence_date = (SELECT min(residence_date) from residence_own rd2 where rd2.client_id = id.client_id AND 
(residence_date <= ''$enddate'' AND (residence_date_end >= ''$startdate'' OR residence_date_end IS NULL))))
UNION
SELECT COUNT(id.client_id), ''Documented as NO'' as description
FROM id_list id 
LEFT JOIN residence_own r_d ON (r_d.client_id=id.client_id)  WHERE chronic_homeless_status_code = ''NO''
AND residence_date = (SELECT min(residence_date) from residence_own rd2 where rd2.client_id = id.client_id AND 
(residence_date <= ''$enddate'' AND (residence_date_end >= ''$startdate'' OR residence_date_end IS NULL)))
UNION
SELECT COUNT(id.client_id), ''Self-Reported as NO'' as description
FROM id_list id 
LEFT JOIN chronic_homeless_status_asked ch ON (ch.client_id=id.client_id)  WHERE chronic_homeless_status_code = ''NO''
AND ch.client_id NOT IN 
   (SELECT client_id FROM residence_own WHERE (chronic_homeless_status_code LIKE ''YES%'' or chronic_homeless_status_code = ''NO'') AND residence_date = (SELECT min(residence_date) from residence_own rd2 where rd2.client_id = id.client_id AND 
(residence_date <= ''$enddate'' AND (residence_date_end >= ''$startdate'' OR residence_date_end IS NULL))))
UNION 
SELECT COUNT(id.client_id), null as description
FROM id_list id 
WHERE client_id NOT IN 
  (SELECT client_id FROM residence_own WHERE (chronic_homeless_status_code LIKE ''YES%'' or chronic_homeless_status_code = ''NO'') AND residence_date = (SELECT min(residence_date) from residence_own rd2 where rd2.client_id = id.client_id AND 
(residence_date <= ''$enddate'' AND (residence_date_end >= ''$startdate'' OR residence_date_end IS NULL))) UNION SELECT client_id FROM chronic_homeless_status_asked WHERE chronic_homeless_status_code LIKE ''YES%'' or chronic_homeless_status_code = ''NO'')
GROUP BY description
	ORDER BY description', 'chronic homeless status', NULL, NULL, NULL, NULL, NULL, NULL, false, false, false, false, NULL, NULL, 'TABLE', NULL, NULL, NULL, NULL, sys_user(), current_timestamp, sys_user(), current_timestamp, false, NULL, NULL, NULL, NULL);



