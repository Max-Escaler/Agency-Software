/*
 * 
 * This script should be run through the psql interface, as it makes heavy use
 * of the \i command to run sub-scripts
 *
 * IMPORTANT NOTES: 
 *
 *   * install.db.sql needs to be run before this script
 *
 *   * AGENCY requires all elements of this script up to the "Child Records" section
 *
 *   * Don't forget to edit add.initial_user.sql to create the basic AGENCY admin user
 */

/************************
 *        staff         *
 ************************/

/* 
 * staff table has to be created first,
 * since all system fields reference it
 */

\i create.tbl_staff.sql
\i create.tbl_staff_password.sql
\i create.tbl_config_staff_password.sql
\i create.view.staff_password.sql
\i create.tbl_user_option.sql
\i add.initial_user.sql

/* Data Dictionary */
\i create.tbl_data_dictionary.sql

/* basic lookups */
/* 
 * NOTE: these create scripts generally insert data, so edit them prior to installation for
 * custom values.
 */

/* states */
\i create.l_state.sql

/* staff positions */
\i create.l_staff_position.sql
\i functions/create.make_staff_title.sql

/* buildings and physical locations */
\i create.l_agency_facility.sql

\i create.l_staff_shift.sql
\i create.l_agency_staff_type.sql
\i create.l_staff_employment_status.sql
\i create.l_agency_program.sql             
\i create.l_agency_project.sql
\i create.l_gender.sql

ALTER TABLE tbl_staff ADD FOREIGN KEY (gender_code) REFERENCES tbl_l_gender (gender_code);

/* staff phone #s */
\i create.l_phone_type.sql
\i create.tbl_staff_phone.sql

/* data accuracy flag -- used by many tables */
\i create.l_accuracy.sql

\i create.l_yes_no.sql

\i create.l_day_of_week.sql

\i create.tbl_staff_employment.sql

/* Remote access table */
\i create.tbl_staff_remote_login.sql

\i create.view.staff_employment.sql
\i create.view.staff.sql

\i create.l_permission_type.sql
\i create.tbl_permission.sql
\i add.initial_permission.sql

/* alerts */
\i functions/create.alert_notify.sql
\i create.l_alert_notify_action.sql
\i create.tbl_alert.sql
\i create.tbl_alert_notify.sql
\i create.view.alert_consolidated.sql
\i create.view.alert_notify_enabled_objects.sql

/* unduplication capability */
\i create.tbl_duplication_staff.sql

/* must be created after sys_user() function is created */
\i create.tbl_user_login.sql

/*********************************************
 *    Core AGENCY Tables and functions      *
 *********************************************/


/* db_revision_history */
\i create.l_agency_flavor.sql
\i create.tbl_db_revision_history.sql

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

	 VALUES ('INITIAL_INSTALL', /*UNIQUE_DB_MOD_NAME */
			'Initial install of AGENCY', /* DESCRIPTION */
			'AGENCY_CORE', /* Which flavor of AGENCY.  AGENCY_CORE applies to all installations */
			'', /* git SHA ID, if applicable */
			'', /* git tag, if applicable */
			current_timestamp, /* Applied at */
			'', /* comment */
			sys_user(),
			sys_user()
		  );

/* known db list */
\i create.db_list.sql

INSERT INTO db_list
	VALUES (current_database(),
	'AGENCY'/* description */,
	FALSE    /* test db? */,
	''       /* primary url */
	);

/* if there will be a dedicated test db, enter an additional record */
/* INSERT INTO db_list
	VALUES (current_database()||'_test',
	'AGENCY Test DB',
	TRUE,
	''
	);
*/

/* system log */
\i create.tbl_system_log.sql

--Record when system was installed.  Later accessible as system_live_at()
INSERT INTO tbl_system_log (event_type,message,added_at,added_by,changed_at,changed_by) VALUES ('SYSTEM_LIVE',current_timestamp::text,current_timestamp,sys_user(),current_timestamp,sys_user());

/* internal vs remote access */
\i create.tbl_db_access.sql

/* target_date */
\i create.tbl_target_date.sql
\i functions/create.functions.target_date.sql

/* this file should be edited for access rules. The default is only to allow local access */
\i add.initial_access.sql

\i create.tbl_engine_config.sql

\i create.tbl_help.sql
\i add.help.sql

\i create.view.db_agency_relations.sql
\i create.view.db_agency_functions.sql

\i create.info_additional.sql

\i create.view.table_log_enabled_tables.sql

/* core functions */

\i functions/create.functions_agency_core.sql
\i functions/create.functions_array.sql
\i functions/create.rank_client_search_results.sql
\i functions/create.functions_changed_at_trigger.sql
\i functions/create.functions_db_list.sql
\i functions/create.functions.link_engine.sql

/* staff functions */
\i functions/create.functions_staff.sql

/* log */
\i functions/create.functions_log.sql
\i create.tbl_reference.sql
\i create.l_log_type.sql
\i create.tbl_log.sql

/* feedback */
\i create.tbl_feedback.sql

/* reports */
\i create.reports.sql

/* news */
\i create.l_news_priority.sql
\i create.tbl_news.sql

/* email config */
\i create.tbl_config_email.sql
\i functions/create.functions.email.sql

/* Enable alert notifications on objects */
select
	alert_notify_enable('tbl_staff'),
	alert_notify_enable('tbl_news'),
	alert_notify_enable('tbl_feedback'),
	alert_notify_enable('tbl_report'),
	alert_notify_enable('tbl_staff_employment'),
	alert_notify_enable('tbl_permission')
	;

/* attachments */
\i create.tbl_attachment.sql
\i create.tbl_attachment_link.sql

/* auth tokens, for resetting passwords */
\i create.tbl_auth_token.sql
