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

	 VALUES ('SAFE_HARBORS_DATA_ENTRY_ID_FIELD', /*UNIQUE_DB_MOD_NAME */
			'Adds serial primary key field, which should have been there in the first place.', /* DESCRIPTION */
			'AGENCY_CORE', /* Which flavor of AGENCY.  AGENCY_CORE applies to all installations */
			'', /* git SHA ID, if applicable */
			'db_mod.39', /* git tag, if applicable */
			current_timestamp, /* Applied at */
			'', /* comment */
			sys_user(),
			sys_user()
		  );

DROP VIEW safe_harbors_data_entry;

ALTER TABLE tbl_safe_harbors_data_entry_log ADD COLUMN safe_harbors_data_entry_id INTEGER;
ALTER TABLE tbl_safe_harbors_data_entry ADD COLUMN safe_harbors_data_entry_id SERIAL PRIMARY KEY;

ALTER TABLE tbl_safe_harbors_data_entry_log DISABLE RULE tbl_safe_harbors_data_entry_log_noupdate;

/*
 * This update of revision history depends on unique added_at and added_by,
 * and could fail if multiple records had been batch entered (in Postgres)
 * in the same transaction.  This is not the case w/ PHG data.
 */

UPDATE tbl_safe_harbors_data_entry_log AS log
SET safe_harbors_data_entry_id=tbl_safe_harbors_data_entry.safe_harbors_data_entry_id
FROM tbl_safe_harbors_data_entry
WHERE
        tbl_safe_harbors_data_entry.added_by=log.added_by
AND     tbl_safe_harbors_data_entry.added_at=log.added_at;


ALTER TABLE tbl_safe_harbors_data_entry_log ENABLE RULE tbl_safe_harbors_data_entry_log_noupdate;

CREATE VIEW safe_harbors_data_entry AS (SELECT * FROM tbl_safe_harbors_data_entry WHERE NOT is_deleted);


COMMIT;

