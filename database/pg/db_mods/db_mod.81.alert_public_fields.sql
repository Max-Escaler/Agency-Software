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
	(
	db_revision_code,
	db_revision_description,
	agency_flavor_code,
	git_sha,
	git_tag,
	applied_at,
	comment,
	added_by,
	changed_by
	)

	 VALUES (
		'ALERT_PUBLIC_FIELDS', /*UNIQUE_DB_MOD_NAME */
		'Add public fields to alerts (e.g., non-protected, OK to email)',
		'AGENCY_CORE', /* Which flavor of AGENCY.  AGENCY_CORE applies to all installations */
		'', /* git SHA ID, if applicable */
		'db_mod.81', /* git tag, if applicable */
		current_timestamp, /* Applied at */
		'', /* comment */
		sys_user(),
		sys_user()
	 );

DROP VIEW IF EXISTS alert_consolidated;
DROP VIEW alert;
ALTER TABLE tbl_alert ALTER COLUMN alert_subject TYPE varchar;
ALTER TABLE IF EXISTS tbl_alert_log ALTER COLUMN alert_subject TYPE varchar;
ALTER TABLE tbl_alert ADD COLUMN alert_subject_public VARCHAR;
ALTER TABLE tbl_alert ADD COLUMN alert_text_public VARCHAR;
ALTER TABLE IF EXISTS tbl_alert_log ADD COLUMN alert_subject_public VARCHAR;
ALTER TABLE IF EXISTS tbl_alert_log ADD COLUMN alert_text_public VARCHAR;


CREATE VIEW alert AS
SELECT * FROM tbl_alert WHERE NOT is_deleted;

\i ../agency_core/create.view.alert_consolidated.sql
\i ../agency_core/functions/create.alert_notify.sql

COMMIT;

