
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
		'CLIENT_GENDER_PRONOUNS',
		'Add preferred gender pronouns to client record (needs custom recreate.view.client.sql) (db_mod.76, out of order)',
		'CLIENT', /* Which flavor of AGENCY.  AGENCY_CORE applies to all installations */
		'', /* git SHA ID, if applicable */
		'db_mod.76', /* git tag, if applicable */
		current_timestamp, /* Applied at */
		'', /* comment */
		sys_user(),
		sys_user()
	 );

\cd ../client
ALTER TABLE tbl_client ADD COLUMN pronoun_subject     VARCHAR;
ALTER TABLE tbl_client ADD COLUMN pronoun_object      VARCHAR;
ALTER TABLE tbl_client ADD COLUMN pronoun_possessive  VARCHAR;
ALTER TABLE tbl_client ADD COLUMN pronoun_possessive_pronoun      VARCHAR;
ALTER TABLE tbl_client ADD COLUMN pronoun_reflexive       VARCHAR;

ALTER TABLE tbl_client_log ADD COLUMN pronoun_subject     VARCHAR;
ALTER TABLE tbl_client_log ADD COLUMN pronoun_object      VARCHAR;
ALTER TABLE tbl_client_log ADD COLUMN pronoun_possessive  VARCHAR;
ALTER TABLE tbl_client_log ADD COLUMN pronoun_possessive_pronoun      VARCHAR;
ALTER TABLE tbl_client_log ADD COLUMN pronoun_reflexive       VARCHAR;

-- This view needs to be created or customized to work with your DB
-- It needs to drop everything dependent on the client view, then the client view, then recreate it all
\i ../client/recreate.view.client.sql
\cd ../db_mods
COMMIT;

