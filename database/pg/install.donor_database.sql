/*
<LICENSE>
</LICENSE>
*/


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

BEGIN;

\cd agency_core
\i install.agency_core.sql
\cd ..

\cd donor
\i install.donor.sql
\i install.calendar.sql
\cd ..

/* Make sure the wrong db_mods are not applied. */
ALTER TABLE tbl_db_revision_history
    ADD CONSTRAINT reject_wrong_flavor_mods CHECK
        (agency_flavor_code IS NULL OR
        agency_flavor_code IN ('AGENCY_CORE','DONOR',''));

SELECT enable_table_logging_all();
COMMIT;
