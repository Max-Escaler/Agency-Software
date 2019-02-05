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

	 VALUES ('REJECT_WRONG_FLAVOR_DB_MODS',
			'Ensure that db_mods for wrong flavor of AGENCY are not applied.',
			'DONOR',
			'', /* git SHA ID, if applicable */
			'db_mod.3',
			current_timestamp, /* Applied at */
			'', /* comment */
			sys_user(),
			sys_user()
		  );

ALTER TABLE tbl_db_revision_history
    ADD CONSTRAINT reject_wrong_flavor_mods CHECK
        (agency_flavor_code IS NULL OR
        agency_flavor_code IN ('AGENCY_CORE','DONOR',''));

SELECT E'IMPORTANT:  This db_mod is for the DONOR version of AGENCY only!\n'
		|| E'This transaction has not been comitted.  If this is the DONOR\n'
		|| E'version of AGENCY, please type "commit;" to complete the transaction\n';

/*
COMMIT;
*/
