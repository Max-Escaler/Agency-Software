INSERT INTO tbl_report
(report_code,
report_title,
report_category_code,
report_header,
report_footer,
report_comment,
client_page,
suppress_output_codes,
override_sql_security,
rows_per_page,
output_template_codes,
permission_type_codes,
variables,
added_by,
added_at,
changed_by,
changed_at,
is_deleted,
deleted_at,
deleted_by,
deleted_comment,
sys_log
)

SELECT
translate(TRIM(UPPER(SUBSTR(report_title,0,30))),' ','_'),
report_title,
report_category_code,
report_header,
report_footer,
report_comment,
client_page,
CASE WHEN NOT allow_output_screen THEN array['O_SCREEN'] ELSE array[]::varchar[] END
 || CASE WHEN NOT allow_output_spreadsheet THEN array['O_TEMPLATE'] ELSE array[]::varchar[] END,
override_sql_security,
rows_per_page,
output,
CASE WHEN COALESCE(report_permission,'')='' THEN array[]::varchar[]
ELSE string_to_array(UPPER(report_permission),',') END,
variables,
added_by,
added_at,
changed_by,
changed_at,
is_deleted,
deleted_at,
deleted_by,
deleted_comment,
sys_log

FROM report_old;

INSERT INTO tbl_report_block
(report_code,
report_block_sql,
added_by,
added_at,
changed_by,
changed_at,
is_deleted,
deleted_at,
deleted_by,
deleted_comment,
sys_log
)
SELECT
translate(TRIM(UPPER(SUBSTR(report_title,0,30))),' ','_'),
sql,
added_by,
added_at,
changed_by,
changed_at,
is_deleted,
deleted_at,
deleted_by,
deleted_comment,
sys_log
FROM report_old;


