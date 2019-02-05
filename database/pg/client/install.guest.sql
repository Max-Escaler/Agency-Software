/*
 * Install tables for guest functionality
 */

/*

DROP FUNCTION guest_name(integer);

DROP VIEW guest_visit_authorized;
DROP VIEW client_guest_ineligible;
DROP VIEW guest_visit_current;
DROP VIEW guest;
DROP VIEW guest_identification_current;
DROP VIEW guest_identification;
DROP TABLE tbl_guest_identification;

DROP VIEW guest_visit;
DROP TABLE tbl_guest_visit;

DROP VIEW guest_authorization_current;
DROP VIEW guest_authorization;
DROP TABLE tbl_guest_authorization;

DROP TABLE tbl_guest;

--DROP VIEW l_connection_type;
--DROP TABLE tbl_l_connection_type;

DROP VIEW l_identification_type;
DROP TABLE tbl_l_identification_type;



*/

--\i create.l_connection_type.sql
\i create.l_identification_type.sql

\i create.tbl_guest.sql
\i create.tbl_guest_identification.sql
\i create.tbl_guest_authorization.sql
\i create.view.guest.sql 
\i create.tbl_guest_visit.sql
\i create.view.guest_visit_current.sql;

-- Moved these to install.client.sql
--\i create.view.bar_guest.sql;
--\i create.view.client_guest_ineligible.sql
--\i create.view.guest_visit_authorized.sql;

\i functions/create.function.guest_name.sql

/*
-- Sample data for testing
\i populate.guest.sample_data.sql
*/

