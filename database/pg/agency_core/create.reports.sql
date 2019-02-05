/*
 * Create report & report usage tables & views
 *
 * The report view needs to be created
 * after report_usage, since
 * it references it.
 */

/*
DROP VIEW report;
DROP VIEW report_usage;
DROP VIEW report_block;
DROP VIEW l_report_block_type;
DROP VIEW l_output;
DROP VIEW l_report_block_type;
DROP TABLE tbl_report_usage;
DROP TABLE tbl_report_block;
DROP TABLE tbl_l_report_block_type;
DROP TABLE  tbl_report;
DROP VIEW l_report_category;
DROP TABLE tbl_l_report_category;
DROP TABLE tbl_l_output;
DROP TABLE tbl_l_report_block_type;
*/

\i create.l_output.sql
\i create.l_report_category.sql
\i create.l_report_block_type.sql
\i create.tbl_report.sql
\i create.tbl_report_usage.sql
\i create.tbl_report_block.sql
\i create.view.report_block.sql
\i create.view.report.sql

/* Add core/sample reports */
\i add.report.agency_core.sql
\i add.report.ad-hoc_query.sql
\i add.report.export_system.sql
/*
 * This code could be used to delete the same items
 */

/*
DROP VIEW report;
DROP VIEW report_usage;
DROP TABLE tbl_report_usage;
DROP TABLE  tbl_report;
DROP TABLE l_report_category;
*/
