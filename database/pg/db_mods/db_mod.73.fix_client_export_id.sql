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
		'FIX_CLIENT_EXPORT_ID', /*UNIQUE_DB_MOD_NAME */
		'Fix client_export_id table (deleted records)',
		'CLIENT', /* Which flavor of AGENCY.  AGENCY_CORE applies to all installations */
		'', /* git SHA ID, if applicable */
		'db_mod.73', /* git tag, if applicable */
		current_timestamp, /* Applied at */
		'', /* comment */
		sys_user(),
		sys_user()
	 );

ALTER TABLE tbl_client_export_id DROP CONSTRAINT tbl_client_export_id_client_id_export_organization_code_key;
ALTER TABLE tbl_client_export_id DROP CONSTRAINT tbl_client_export_id_export_organization_code_export_id_key;
CREATE UNIQUE INDEX tbl_client_export_id_1_client_per_org ON tbl_client_export_id (client_id,export_organization_code) WHERE NOT is_deleted;
CREATE UNIQUE INDEX tbl_client_export_1_id_per_org ON tbl_client_export_id (export_id,export_organization_code) WHERE NOT is_deleted;

COMMIT;


