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

	 VALUES ('ENABLE_ATTACHMENTS', /*UNIQUE_DB_MOD_NAME */
			'Create attachment and attachment_link tables for attachments', /* DESCRIPTION */
			'AGENCY_CORE', /* Which flavor of AGENCY.  AGENCY_CORE applies to all installations */
			'', /* git SHA ID, if applicable */
			'db_mod.14', /* git tag, if applicable */
			current_timestamp, /* Applied at */
			'', /* comment */
			sys_user(),
			sys_user()
		  );

/*
DROP VIEW attachment_link;
DROP VIEW attachment;
DROP TABLE tbl_attachment_link;
DROP TABLE tbl_attachment;
*/

\i ../agency_core/create.tbl_attachment.sql
\i ../agency_core/create.tbl_attachment_link.sql

COMMIT;
