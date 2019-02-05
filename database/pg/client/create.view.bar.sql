CREATE OR REPLACE VIEW bar AS
SELECT
	bar_id,
	client_id,
	guest_id,
	non_client_name_last,
	non_client_name_first,
	non_client_description,
	bar_date,
	bar_date_end,
	barred_by,
	bar_incident_location_code,
	bar_resolution_location_code,
	barred_from_codes,
	bar_reason_codes,
	description,
	staff_involved,
	gate_mail_date,
	brc_elig_date,
	brc_client_attended_date,
	brc_resolution_code,
	appeal_elig_date,	
	reinstate_condition,
	brc_recommendation,
	comments,
	police_incident_number,
	(bar_date_end - bar_date) + 1 as days_barred,
	non_client_name_last || ', ' || non_client_name_first AS non_client_name_full,
	CASE WHEN bar_date_end IS NOT NULL AND brc_client_attended_date IS NULL
		THEN bar_date_end-bar_date + 1 || (CASE 
											WHEN bar_date_end-bar_date > 0 THEN ' days'
											ELSE ' day'
											END)
		ELSE 'OPEN'
	END AS bar_type,
	added_by,
	added_at,
	changed_by,
	changed_at,
	is_deleted,
	deleted_by,
	deleted_at,
	deleted_comment,
	sys_log

 /*
bar_id                       | integer                        | not null default nextval('tbl_bar_bar_id_seq'::regclass)
 client_id                    | integer                        |
 non_client_name_last         | character varying(40)          |
 non_client_name_first        | character varying(30)          |
 non_client_description       | text                           |
 bar_date                     | date                           | not null
 bar_date_end                 | date                           |
 barred_by                    | integer                        | not null
 bar_incident_location_code   | character varying(10)          | not null
 bar_resolution_location_code | character varying(10)          |
 barred_from_codes            | character varying(10)[]        | not null
 bar_reason_codes             | character varying(10)[]        | not null
 description                  | text                           |
 staff_involved               | integer[]                      |
 gate_mail_date               | date                           |
 brc_elig_date                | date                           |
 brc_client_attended_date     | date                           |
 brc_resolution_code          | character varying(10)          |
 appeal_elig_date             | date                           |
 reinstate_condition          | text                           |
 brc_recommendation           | text                           |
 comments                     | text                           |
 police_incident_number       | character varying(30)          |
 added_by                     | integer                        | not null
 added_at                     | timestamp(0) without time zone | not null default now()
 changed_by                   | integer                        | not null
 changed_at                   | timestamp(0) without time zone | not null default now()
 is_deleted                   | boolean                        | default false
 deleted_by                   | integer                        |
 deleted_at                   | timestamp(0) without time zone |
 deleted_comment              | text                           |
 sys_log                      | text                           |
*/

FROM tbl_bar
WHERE NOT is_deleted;

CREATE VIEW  bar_current AS
SELECT * from bar
WHERE bar_date <= current_date
AND COALESCE(bar_date_end,current_date)>= current_date;

