CREATE OR REPLACE VIEW elevated_concern_note AS

SELECT 
	elevated_concern_note_id,
	erl.client_id,
	elevated_concern_note_date,
	note_by,
	note_text,
	additional_information,
	erln.added_at,
	erln.added_by

FROM elevated_concern erl

	LEFT JOIN

( /* this is the complete view, which is joined on to filter out events/notes not in the time period */
SELECT
	log_id||'::log' AS elevated_concern_note_id,
	cref.client_id  AS client_id,
	COALESCE(occurred_at,log.added_at) AS elevated_concern_note_date,
	log.added_by    AS note_by,
	log_text        AS note_text,
	subject::text         AS additional_information,
	log.added_at        AS added_at,
	log.added_by	AS added_by
FROM log
	LEFT JOIN client_ref cref ON (cref.ref_table='LOG' AND cref.ref_id = log_id)


UNION ALL

SELECT client_note_id||'::client_note' AS elevated_concern_note_id,
	client_id,
	added_at AS elevated_concern_note_date,
	added_by AS note_by,
	note     AS note_text,
	NULL::text AS additional_information,
	added_at,
	added_by
FROM client_note


UNION ALL

/*
SELECT dal_id||'::dal' AS elevated_concern_note_id,
	client_id,
	dal_date     AS elevated_concern_note_date,
	performed_by AS note_by,
	progress_note AS note_text,
	dal_code      AS additional_information,
	added_at      AS added_at,
	added_by      AS added_by
FROM dal WHERE dal_progress_note_id IS NULL

UNION ALL
*/

SELECT service_id||'::service_'||
	lower(service_project_code) AS elevated_concern_note_id,
	client_id,
	service_date AS elevated_concern_note_date,
	service_by AS note_by,
	progress_note AS note_text,
	service_code AS additional_information,
	added_at,
	added_by
FROM tbl_service WHERE NOT is_deleted AND service_progress_note_id IS NULL

UNION ALL

SELECT jail_id||'::jail' AS elevated_concern_note_id,
	client_id,
	jail_date AS elevated_concern_note_date,
	added_by AS note_by,
	'Went to jail'
		||COALESCE(' (released on '||to_char(jail_date_end,'mm/dd/yyyy hh:MIpm')||')','') AS note_text,
	NULL AS additional_information,
	added_at,
	added_by
FROM jail

UNION ALL

SELECT hospital_id||'::hospital' AS elevated_concern_note_id,
	client_id,
	hospital_date AS elevated_concern_note_date,
	added_by AS note_by,
	'Went to hospital'||COALESCE(' ('||facility||')','')
		||COALESCE(' (released on '||to_char(hospital_date_end,'mm/dd/yyyy')||')','') AS note_text,
	CASE WHEN is_voluntary THEN 'Voluntary' ELSE 'Involuntary' END AS additional_information,
	added_at,
	added_by
FROM hospital

UNION ALL

SELECT entry_id||'::entry' AS elevated_concern_note_id,
	client_id,
	entered_at AS elevated_concern_note_date,
	761 AS note_by,
	'Entered '||sl.description AS note_text,
	NULL AS additional_information,
	entered_at AS added_at,
	761 AS added_by
FROM entry
	LEFT JOIN l_entry_location sl USING (entry_location_code)

UNION ALL

SELECT bed_id||'::bed' AS elevated_concern_note_id,
	client_id,
	bed_date AS elevated_concern_note_date,
	bed.added_by AS note_by,
	'Bed night: '||bg.description AS note_text,
	NULL AS additional_information,
	bed.added_at,
	bed.added_by
FROM bed
	LEFT JOIN l_bed_group bg ON (bed.bed_group_code = bg.bed_group_code)

) /* end of unioned complete list */

erln ON (erl.client_id = erln.client_id
	AND erln.elevated_concern_note_date BETWEEN erl.elevated_concern_date AND COALESCE (erl.elevated_concern_date_end,CURRENT_DATE + 1))
;
