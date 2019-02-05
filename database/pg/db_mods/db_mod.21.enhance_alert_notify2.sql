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

	 VALUES ('ENHANCE_ALERT_NOTIFY2',
			'More alert notifications enhancement.',
			'AGENCY_CORE',
			'',
			'db_mod.21',
			current_timestamp,
			'', 
			sys_user(),
			sys_user()
		  );

SELECT * INTO TABLE alert_notify_backup2 FROM tbl_alert_notify;
SELECT * INTO TABLE alert_notify_log_backup2 FROM tbl_alert_notify_log;
DROP VIEW alert_notify_current;
DROP VIEW alert_notify;
DROP TABLE tbl_alert_notify;
\i ../agency_core/create.tbl_alert_notify.sql
\i ../agency_core/functions/create.alert_notify.sql

/* Restore data */
INSERT INTO tbl_alert_notify (match_program_field,match_project_field,match_position_field,match_facility_field,match_shift_field,match_supervisor_field,match_supervisees_field,match_assignments_field,alert_notify_field2,alert_notify_value2,alert_notify_field3,alert_notify_value3,alert_notify_field4,alert_notify_value4,alert_notify_id,staff_id,alert_object,alert_notify_action_code,alert_notify_date,alert_notify_date_end,alert_notify_field,alert_notify_value,alert_notify_reason,comment,added_by,added_at,changed_by,changed_at,deleted_by,deleted_at,deleted_comment,sys_log) SELECT match_program_field,match_project_field,match_position_field,match_facility_field,match_shift_field,match_supervisor_field,match_supervisees_field,match_assignments_field,alert_notify_field2,alert_notify_value2,alert_notify_field3,alert_notify_value3,alert_notify_field4,alert_notify_value4,alert_notify_id,staff_id,alert_object,alert_notify_action_code,alert_notify_date,alert_notify_date_end,alert_notify_field,alert_notify_value,alert_notify_reason,comment,added_by,added_at,changed_by,changed_at,deleted_by,deleted_at,deleted_comment,sys_log FROM alert_notify_backup2;

/* Enable table logging */
DROP TABLE tbl_alert_notify_log;
DROP SEQUENCE tbl_alert_notify_log_id;
SELECT * INTO tbl_alert_notify_log FROM tbl_alert_notify LIMIT 0;
ALTER TABLE tbl_alert_notify_log ADD COLUMN trigger_mode VARCHAR(10);
ALTER TABLE tbl_alert_notify_log ADD COLUMN trigger_tuple VARCHAR(5);
ALTER TABLE tbl_alert_notify_log ADD COLUMN trigger_changed TIMESTAMP;
ALTER TABLE tbl_alert_notify_log ADD COLUMN trigger_id BIGINT;
CREATE SEQUENCE tbl_alert_notify_log_id;
ALTER TABLE tbl_alert_notify_log ALTER COLUMN trigger_id SET DEFAULT NEXTVAL('tbl_alert_notify_log_id');

-- create trigger
CREATE TRIGGER tbl_alert_notify_log_chg 
--	AFTER UPDATE OR INSERT OR DELETE ON tbl_alert_notify 
	AFTER UPDATE OR INSERT OR DELETE ON tbl_alert_notify 
	FOR EACH ROW EXECUTE PROCEDURE table_log();
-- Disable updates & deletes of log table
CREATE RULE tbl_alert_notify_log_nodelete AS
	ON DELETE TO tbl_alert_notify_log DO INSTEAD NOTHING;
CREATE RULE tbl_alert_notify_log_noupdate AS
	ON UPDATE TO tbl_alert_notify_log DO INSTEAD NOTHING;
/* Restore revision history */
INSERT INTO tbl_alert_notify_log (match_program_field,match_project_field,match_position_field,match_facility_field,match_shift_field,match_supervisor_field,match_supervisees_field,match_assignments_field,alert_notify_field2,alert_notify_value2,alert_notify_field3,alert_notify_value3,alert_notify_field4,alert_notify_value4,trigger_mode,trigger_tuple,trigger_changed,trigger_id,alert_notify_id,staff_id,alert_object,alert_notify_action_code,alert_notify_date,alert_notify_date_end,alert_notify_field,alert_notify_value,alert_notify_reason,comment,added_by,added_at,changed_by,changed_at,deleted_by,deleted_at,deleted_comment,sys_log) SELECT match_program_field,match_project_field,match_position_field,match_facility_field,match_shift_field,match_supervisor_field,match_supervisees_field,match_assignments_field,alert_notify_field2,alert_notify_value2,alert_notify_field3,alert_notify_value3,alert_notify_field4,alert_notify_value4,trigger_mode,trigger_tuple,trigger_changed,trigger_id,alert_notify_id,staff_id,alert_object,alert_notify_action_code,alert_notify_date,alert_notify_date_end,alert_notify_field,alert_notify_value,alert_notify_reason,comment,added_by,added_at,changed_by,changed_at,deleted_by,deleted_at,deleted_comment,sys_log FROM alert_notify_log_backup2;

/* Sequence */
SELECT SETVAL('tbl_alert_notify_alert_notify_id_seq',(SELECT max(alert_notify_id) FROM tbl_alert_notify));

SELECT SETVAL('tbl_alert_notify_log_id', (SELECT max(trigger_id) FROM tbl_alert_notify_log));

/* Friendly user reminder */
SELECT 'A backup of the original alert_notify and alert_notify_log tables hav been left behind in alert_notify_backup2 and alert_notify_log_backup2.  Remove it if you are satisfied with the new data.';

COMMIT; 
