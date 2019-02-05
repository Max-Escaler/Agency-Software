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

/**********************************************************\
 *                         Client                         *
 *                                                        *
 * Structure this table as desired. It will serve as the  *
 * key 'parent' record for all subsequent 'child'         *
 * records.                                               *
\**********************************************************/

/* basic lookups */
\i create.l_language.sql
\i create.l_veteran_status.sql
\i create.l_name_suffix.sql
\i create.l_staff_assign_type.sql
\i create.l_agency.sql
\i create.l_hispanic_origin.sql
\i create.l_sexual_minority_status.sql

\i create.tbl_client.sql
\i create.tbl_client_protected.sql
\i create.view.client.sql
\i create.tbl_client_ref.sql

\i create.tbl_staff_assign.sql

/*
 * Ethnicity:  Although child records, I had to
 * move them up because they are referenced by
 * createa.functions_client.sql
 */ 

\i create.l_ethnicity_simple.sql
\i create.l_ethnicity.sql
\i create.tbl_ethnicity.sql

/* additional core functions referencing client and staff */
\i functions/create.functions_client.sql
\i functions/create.functions_staff_assign_trigger.sql
\i functions/create.functions_staff_client_assign.sql
\i functions/create.trigger_staff_assign.sql

/* unduplication capability */
\i create.tbl_duplication.sql

/* client-specific permissions */
\i add.client_permission_type.sql

/* Staff assign (caseload) transfers and closes */
\i functions/create.functions.staff_assign_transfer_and_close.sql

/**********************************************************\
 *                       Child Records                    *
 *                                                        *
 * At this point, the most basic install is complete.     *
 * Staff and Client child records can now be added.       *
 *                                                        *
\**********************************************************/
/* used by housing and CD */
\i create.l_exit_status.sql

\i create.l_disability.sql
\i create.tbl_disability.sql

\i create.tbl_client_note.sql

\i create.l_contact_type.sql

/* Client Death */
\i create.l_client_death_data_source.sql
\i create.l_client_death_location.sql
\i create.l_client_death_type.sql
\i create.tbl_client_death.sql


/* Jail table */
\i create.l_washington_county.sql
\i create.l_jail_facility.sql 
\i create.l_data_source.sql
\i create.tbl_jail.sql

/* Hospital table */
\i create.tbl_hospital.sql
\i create.l_hospital.sql

/* Entry Table */
\i create.l_entry_location.sql
\i create.tbl_entry.sql

\i create.view.status_eligible.sql
\i functions/create.entry_ineligible.sql

/* Address table and views */
\i create.tbl_address.sql
\i create.view.address.sql

/* Bed table */
\i create.l_volunteer.sql
\i create.l_bed_group.sql
\i create.sys_flag.sql
\i create.bed_reg.sql
\i create.l_bed_rereg.sql
\i create.tbl_bed.sql


/* Service Table */


\i create.l_service_project.sql
\i create.l_service.sql
\i create.tbl_service.sql 

/* Elevated Concern List (ECL) */
\i create.domain.dates.sql
\i functions/create.elevated_concern.sql

\i create.l_elevated_concern_reason.sql
\i create.l_elevated_concern_status.sql
\i create.tbl_elevated_concern.sql
\i create.view.elevated_concern_note.sql

/* HOUSING-SPECIFIC TABLES */
\i install.housing.sql

/* Since income and guest records reference housing units, they need to
 * exist. If you don't use the install.housing.sql script,
 * and want the income, guest or bar tables, run these lines instead.
 */

/*
\i housing/create.l_unit_type.sql
\i housing/create.tbl_housing_unit.sql
\i housing/create.l_fund_type.sql
*/

/* GUEST SYSTEM */
/* Currently Guest is required for Bar */
\i install.guest.sql

/* BAR SYSTEM */
\i install.bar.sql


/* These were part of install.guest.sql,
 * but I moved them here because they depend on bars
 */ 
\i create.view.bar_guest.sql;
\i create.view.client_guest_ineligible.sql
\i create.view.guest_visit_authorized.sql

/* SHELTER-SPECIFIC TABLES */
/*
\i install.shelter.sql
*/

/* MAIL SYSTEM */
/*
\i create.l_mail_type.sql
\i create.l_mail_delivery.sql

\i create.tbl_mail.sql
*/

/* Income Table */
\i housing/create.l_income_certification_type.sql
\i create.l_income.sql
\i create.l_other_assistance.sql
\i create.tbl_income.sql
\i create.view.income.sql
\i functions/create.functions_income.sql

/* CALENDAR SYSTEM */
\i install.calendar.sql

/* Education Level */

\i create.l_grade_level.sql
\i create.l_highest_education.sql
\i create.tbl_education_level.sql

/* Add sample/provided client report(s) */
\i add.report.client.sql
\i add.report.profile.sql

/* Enable Alert Notification for client objects */
select
    alert_notify_enable('tbl_client'),
    alert_notify_enable('tbl_bar'),
    alert_notify_enable('tbl_jail'),
    alert_notify_enable('tbl_hospital'),
    alert_notify_enable('tbl_client_death'),
    alert_notify_enable('tbl_client_note')
    ;

