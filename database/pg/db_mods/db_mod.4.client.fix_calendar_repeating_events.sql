BEGIN;

INSERT INTO tbl_db_revision_history 
	(db_revision_code,
	db_revision_description,
	agency_flavor_code,
	git_sha,
	git_tag,
	applied_at,
	comment,
	added_by,
	changed_by)

	 VALUES ('FIX_CALENDAR_REPEATING_EVENTS',
			'Create the l_event_repeat type lookup table, and fix the repeating events function',
			'CLIENT',
			'',
			'db_mod.4',
			current_timestamp,
			'', 
			sys_user(),
			sys_user()
		  );

\i ../agency_core/create.l_event_repeat_type.sql
DROP TRIGGER tbl_calendar_appointment_insert_update ON tbl_calendar_appointment;
\i ../client/functions/create.functions_calendar.sql

COMMIT;
