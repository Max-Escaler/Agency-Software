BEGIN;

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

	 VALUES ('RESTRUCTURE_ENTRY_TABLE', /*UNIQUE_DB_MOD_NAME */
			'Changes entry table, renames scanner_location to entry_location.', /* DESCRIPTION */
			'CLIENT', /* Which flavor of AGENCY.  AGENCY_CORE applies to all installations */
			'', /* git SHA ID, if applicable */
			'db_mod.36', /* git tag, if applicable */
			current_timestamp, /* Applied at */
			'', /* comment */
			sys_user(),
			sys_user()
		  );

\i ../client/create.l_entry_location.sql
CREATE TEMP TABLE entry_back AS SELECT * FROM tbl_entry;
CREATE TEMP TABLE entry_log_back AS SELECT * FROM tbl_entry_log;
CREATE TEMP TABLE l_scanner_location_back AS SELECT * FROM tbl_l_scanner_location_log;

DELETE FROM tbl_l_entry_location;
INSERT INTO tbl_l_entry_location SELECT * FROM tbl_l_scanner_location;
SELECT enable_table_logging('tbl_l_entry_location','');
INSERT INTO tbl_entry_log SELECT * FROM entry_log_back;

DROP VIEW elevated_concern_note;
DROP VIEW entry;
DROP TABLE tbl_entry;
DROP TABLE tbl_entry_log;
DROP VIEW l_scanner_location;
DROP TABLE tbl_l_scanner_location;
DROP TABLE tbl_l_scanner_location_log;
DROP SEQUENCE tbl_entry_log_id;
DROP SEQUENCE tbl_l_scanner_location_log_id;
\i ../client/create.tbl_entry.sql
INSERT INTO tbl_entry SELECT * FROM entry_back;
SELECT enable_table_logging('tbl_entry','');
INSERT INTO tbl_entry_log SELECT * FROM entry_log_back;

\i ../client/create.view.elevated_concern_note.sql

COMMIT;

