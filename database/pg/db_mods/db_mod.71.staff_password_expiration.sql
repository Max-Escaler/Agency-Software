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
		'STAFF_PASSWORD_EXPIRATION', /*UNIQUE_DB_MOD_NAME */
		'End dates for passwords, retention of old passwords, and configurable options',
		'AGENCY_CORE', /* Which flavor of AGENCY.  AGENCY_CORE applies to all installations */
		'', /* git SHA ID, if applicable */
		'db_mod.71', /* git tag, if applicable */
		current_timestamp, /* Applied at */
		'', /* comment */
		sys_user(),
		sys_user()
	 );


DROP FUNCTION IF EXISTS flipbits(text);

\i ../agency_core/create.tbl_config_staff_password.sql
INSERT INTO tbl_config_staff_password (added_by,changed_by) VALUES (sys_user(),sys_user());

DROP VIEW IF EXISTS export_staff_bugzilla;
DROP VIEW IF EXISTS staff_password; 
ALTER TABLE tbl_staff_password ADD COLUMN    staff_password_date DATE NOT NULL DEFAULT current_date;
ALTER TABLE tbl_staff_password ADD COLUMN    staff_password_date_end DATE;
\i ../agency_core/create.view.staff_password.sql
ALTER TABLE tbl_staff_password ADD EXCLUDE USING gist (daterange(staff_password_date,staff_password_date_end,'()') WITH &&,  staff_id WITH =);
CREATE INDEX unique_tbl_staff_password_staff_id_staff_password_md5 ON tbl_staff_password ( staff_id,staff_password_md5);

\i ../agency_core/functions/create.trigger.process_staff_password.sql
--\i ../agency_core/create.view.export_staff_bugzilla.sql


COMMIT;

