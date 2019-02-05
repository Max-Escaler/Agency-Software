BEGIN;

/*
 * db_mod.TEMPLATE
 */
 
/*
NOTE:  I believe it is not possible to know the
git SHA ID before actually making a commit.

It is possible to know a git tag, and include
that in the commit.
*/

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

	 VALUES ('NEW_REPORT_SYSTEM', /*UNIQUE_DB_MOD_NAME */
			'New Report System', /* DESCRIPTION */
			'AGENCY_CORE', /* Which flavor of AGENCY.  AGENCY_CORE applies to all installations */
			'', /* git SHA ID, if applicable */
			'db_mod.48', /* git tag, if applicable */
			current_timestamp, /* Applied at */
			'', /* comment */
			sys_user(),
			sys_user()
		  );

CREATE TABLE report_old AS (SELECT * FROM report);
CREATE TABLE report_old_log AS (SELECT * FROM tbl_report_log);



DROP VIEW report;
DROP VIEW report_usage;

ALTER TABLE tbl_report_usage DROP CONSTRAINT tbl_report_usage_report_id_fkey;


--DROP VIEW report_block;
--DROP VIEW l_report_block_type;
--DROP VIEW l_output;
--DROP VIEW l_report_block_type;
--DROP TABLE tbl_report_block;
--DROP TABLE tbl_l_report_block_type;
DROP TABLE  tbl_report;
DROP TABLE tbl_report_log;
DROP SEQUENCE tbl_report_log_id;

--DROP VIEW l_report_category;
--DROP TABLE tbl_l_report_category;
--DROP TABLE tbl_l_output;
--DROP TABLE tbl_l_report_block_type;

\cd ../agency_core

\i create.l_output.sql
--\i create.l_report_category.sql
\i create.l_report_block_type.sql

ALTER TABLE tbl_report_usage ADD COLUMN report_code VARCHAR(40);
ALTER TABLE tbl_report_usage_log ADD COLUMN report_code VARCHAR(40);

CREATE VIEW report_usage AS 
	SELECT	*
	FROM	tbl_report_usage
	WHERE	NOT is_deleted;


\i create.tbl_report.sql
--\i create.tbl_report_usage.sql
\i create.tbl_report_block.sql
\i create.view.report.sql
\cd ../db_mods

ALTER TABLE tbl_report_usage ADD CONSTRAINT tbl_report_usage_report_code_fkey FOREIGN KEY (report_code) REFERENCES tbl_report (report_code);



SELECT enable_table_logging('tbl_report','');

INSERT INTO tbl_l_report_category VALUES ('HIDDEN','Hidden/System reports',sys_user(),current_timestamp,sys_user(),current_timestamp);

\i ../agency_core/add.report.ad-hoc_query.sql
\i ../agency_core/add.report.export_system.sql
\i convert.old_reports.48.sql

COMMIT;
