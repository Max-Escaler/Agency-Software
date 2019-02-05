/*
 *  Shelter specific lookups and tables
 */

/* shelter registraion */
\i create.l_bed_rereg.sql
\i create.l_last_residence.sql
\i create.l_svc_need.sql

\i create.tbl_shelter_reg.sql

/* gatekeeping */
\i create.l_entry_location.sql

\i create.tbl_entry.sql

/* bed nights */
\i create.l_bed_group.sql
\i create.l_volunteer_status.sql

\i create.tbl_bed.sql

/* assessments */
\i create.tbl_assessment.sql

/* locker system */
\i create.l_locker_type.sql

\i create.tbl_lock.sql 
\i create.tbl_locker.sql
\i create.tbl_client_locker_assignment.sql
\i create.view.l_client_locker_add.sql
