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

	 VALUES ('ALLOW_GUEST_BARS', /*UNIQUE_DB_MOD_NAME */
			'Allow guests to be barred via existing bar system', /* DESCRIPTION */
			'CLIENT', /* Which flavor of AGENCY.  AGENCY_CORE applies to all installations */
			'', /* git SHA ID, if applicable */
			'db_mod.54', /* git tag, if applicable */
			current_timestamp, /* Applied at */
			'', /* comment */
			sys_user(),
			sys_user()
		  );

ALTER TABLE tbl_bar ADD COLUMN guest_id INTEGER REFERENCES tbl_guest (guest_id);
ALTER TABLE tbl_bar_log ADD COLUMN guest_id INTEGER;

ALTER TABLE tbl_bar DROP CONSTRAINT barred_client_or_non_client;
ALTER TABLE tbl_bar ADD CONSTRAINT barred_client_or_guest_or_non_client CHECK (
	(client_id IS NOT NULL AND guest_id IS NULL AND non_client_name_last IS NULL AND non_client_name_first IS NULL AND non_client_description IS NULL)
	OR
	(client_id IS NULL AND guest_id IS NULL AND non_client_name_last IS NOT NULL AND non_client_name_first IS NOT NULL AND non_client_description IS NOT NULL)
	OR
	(client_id IS NULL AND guest_id IS NOT NULL AND non_client_name_last IS NULL AND non_client_name_first IS NULL AND non_client_description IS NULL)
);

CREATE INDEX index_tbl_bar_guest_id ON tbl_bar ( guest_id );
CREATE INDEX index_tbl_bar_guest_id_bar_date ON tbl_bar ( guest_id,bar_date );

DROP VIEW guest_visit_authorized;
DROP VIEW client_guest_ineligible;
DROP VIEW bar_current;
DROP VIEW bar;

\i ../client/create.view.bar.sql
\i ../client/create.view.bar_guest.sql
\i ../client/create.view.client_guest_ineligible.sql
\i ../client/create.view.guest_visit_authorized.sql

COMMIT;
