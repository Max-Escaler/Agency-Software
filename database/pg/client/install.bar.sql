/* BAR SYSTEM */

/*
DROP VIEW bar_current;
DROP VIEW bar;
DROP TABLE tbl_bar;
DROP VIEW l_brc_resolution;
DROP TABLE tbl_l_brc_resolution;
DROP VIEW l_bar_reason;
DROP TABLE tbl_l_bar_reason;
DROP VIEW l_barred_from;
DROP TABLE tbl_l_barred_from;
DROP VIEW l_bar_incident_location;
DROP VIEW l_bar_resolution_location;
DROP VIEW l_bar_location;
DROP TABLE tbl_l_bar_location;
*/

\i create.l_bar_location.sql
\i create.l_barred_from.sql
\i create.l_bar_reason.sql
\i create.l_brc_resolution.sql

\i create.tbl_bar.sql
\i create.view.bar.sql

