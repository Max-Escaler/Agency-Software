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
		'LENGTHEN_EXPORT_ORGANIZATION_CODE', /*UNIQUE_DB_MOD_NAME */
		'Make export_organization_code 20 chars, not 10.  Also require(!) client_id for client_export_id',
		'CLIENT', /* Which flavor of AGENCY.  AGENCY_CORE applies to all installations */
		'', /* git SHA ID, if applicable */
		'db_mod.73', /* git tag, if applicable */
		current_timestamp, /* Applied at */
		'', /* comment */
		sys_user(),
		sys_user()
	 );



DROP VIEW l_export_organization;
DROP VIEW client_export_id;
ALTER TABLE tbl_l_export_organization ALTER COLUMN export_organization_code TYPE VARCHAR(20);
ALTER TABLE tbl_client_export_id ALTER COLUMN export_organization_code TYPE VARCHAR(20);

ALTER TABLE tbl_l_export_organization_log ALTER COLUMN export_organization_code TYPE VARCHAR(20);
ALTER TABLE tbl_client_export_id_log ALTER COLUMN export_organization_code TYPE VARCHAR(20);

CREATE VIEW client_export_id AS SELECT * FROM tbl_client_export_id WHERE NOT is_deleted;
CREATE VIEW l_export_organization AS (SELECT * FROM tbl_l_export_organization WHERE NOT is_deleted);

ALTER TABLE tbl_client_export_id ALTER COLUMN client_id SET NOT NULL;

COMMIT;

