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
		'tweak_medical_fields', /*UNIQUE_DB_MOD_NAME */
		'Tweaks medical fields.',
		'CLIENT', /* Which flavor of AGENCY.  AGENCY_CORE applies to all installations */
		'', /* git SHA ID, if applicable */
		'db_mod.58', /* git tag, if applicable */
		current_timestamp, /* Applied at */
		'Changes varchar to text, better labels', /* comment */
		sys_user(),
		sys_user()
	 );

DROP VIEW status_eligible;
DROP VIEW client;

ALTER TABLE tbl_client ALTER COLUMN medications TYPE TEXT;
ALTER TABLE tbl_client ALTER COLUMN med_allergies TYPE TEXT;

ALTER TABLE tbl_client_log ALTER COLUMN medications TYPE TEXT;
ALTER TABLE tbl_client_log ALTER COLUMN med_allergies TYPE TEXT;

\i ../client/create.view.client.sql
\i ../client/create.view.status_eligible.sql



COMMIT;

