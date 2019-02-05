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

	 VALUES ('RESTRUCTURE_BAR_SYSTEM', /*UNIQUE_DB_MOD_NAME */
			'Changes bar system to be more table-driven, and more generic.', /* DESCRIPTION */
			'CLIENT', /* Which flavor of AGENCY.  AGENCY_CORE applies to all installations */
			'', /* git SHA ID, if applicable */
			'db_mod.35', /* git tag, if applicable */
			current_timestamp, /* Applied at */
			'', /* comment */
			sys_user(),
			sys_user()
		  );

SELECT * INTO bar_backup_dbmod_35 FROM tbl_bar;
SELECT * INTO l_brc_resolution_backup_dbmod_35 FROM tbl_l_bar_resolution;
SELECT * INTO l_bar_reason_backup_dbmod_35 FROM tbl_l_bar_reason;
SELECT * INTO l_bar_location_backup_dbmod_35 FROM tbl_l_bar_location;

DROP VIEW bar_current;
DROP VIEW bar;
DROP TABLE tbl_bar;
DROP VIEW l_brc_resolution;
DROP TABLE tbl_l_brc_resolution;
DROP VIEW l_bar_reason;
DROP TABLE tbl_l_bar_reason;
--DROP VIEW l_barred_from;
--DROP TABLE tbl_l_barred_from;
DROP VIEW l_bar_incident_location;
DROP VIEW l_bar_resolution_location;
DROP VIEW l_bar_location;
DROP TABLE tbl_l_bar_location;


\cd ../client
\i install.bar.sql
\cd ../db_mods

SELECT 'WARNING:  This db_mod will backup your bar data, but you will need to restore it yourself.';
SELECT 'Please proceed with caution.  If you would like to continue, you will need to uncomment';
SELECT 'the COMMIT line in the db_mod.35 file by yourself.  By default, this transaction will be aborted.';

/* REMOVE THIS ABORT LINE, AND UNCOMMENT THE COMMIT, TO ENABLE THIS DB_MOD */
ABORT;

/*
COMMIT;
*/

