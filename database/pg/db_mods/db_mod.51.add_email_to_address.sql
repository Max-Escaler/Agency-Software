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

	 VALUES ('ADD_EMAIL_TO_ADDRESS', /*UNIQUE_DB_MOD_NAME */
			'Add email fields to address table', /* DESCRIPTION */
			'CLIENT', /* Which flavor of AGENCY.  AGENCY_CORE applies to all installations */
			'', /* git SHA ID, if applicable */
			'db_mod.51', /* git tag, if applicable */
			current_timestamp, /* Applied at */
			'', /* comment */
			sys_user(),
			sys_user()
		  );


DROP VIEW address_client_current;
DROP VIEW address_staff_current;
DROP VIEW address_client;
DROP VIEW address_staff;
DROP VIEW address_current;
DROP VIEW address;

ALTER TABLE tbl_address ADD COLUMN address_email VARCHAR(80);
ALTER TABLE tbl_address_log ADD COLUMN address_email VARCHAR(80);
ALTER TABLE tbl_address ADD COLUMN address_email2 VARCHAR(80);
ALTER TABLE tbl_address_log ADD COLUMN address_email2 VARCHAR(80);

\i ../client/create.view.address.sql

COMMIT;
