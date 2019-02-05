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

	 VALUES ('LENGTHEN_ATTACHMENT_LINK_PARENT_OBJECT', /*UNIQUE_DB_MOD_NAME */
			'Lengthen parent_object to varchar(40) for attachment_link.', /* DESCRIPTION */
			'AGENCY_CORE', /* Which flavor of AGENCY.  AGENCY_CORE applies to all installations */
			'', /* git SHA ID, if applicable */
			'db_mod.38', /* git tag, if applicable */
			current_timestamp, /* Applied at */
			'', /* comment */
			sys_user(),
			sys_user()
		  );

DROP VIEW attachment_link;
ALTER TABLE tbl_attachment_link ALTER parent_object TYPE VARCHAR(40);
ALTER TABLE tbl_attachment_link_log ALTER parent_object TYPE VARCHAR(40);
CREATE VIEW attachment_link AS (SELECT * FROM tbl_attachment_link WHERE NOT is_deleted);

COMMIT;

