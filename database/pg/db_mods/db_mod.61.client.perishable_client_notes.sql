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

	 VALUES ('perishable_client_notes', /*UNIQUE_DB_MOD_NAME */
			'Client notes can expire from front page and entry flagging', /* DESCRIPTION */
			'CLIENT', /* Which flavor of AGENCY.  AGENCY_CORE applies to all installations */
			'', /* git SHA ID, if applicable */
			'db_mod.61', /* git tag, if applicable */
			current_timestamp, /* Applied at */
			'', /* comment */
			sys_user(),
			sys_user()
		  );


ALTER TABLE tbl_client_note ADD COLUMN front_page_until	TIMESTAMP(0);
ALTER TABLE tbl_client_note ADD COLUMN flag_entry_until TIMESTAMP(0);
ALTER TABLE tbl_client_note ADD COLUMN is_entry_dismissable	BOOLEAN NOT NULL DEFAULT TRUE;
ALTER TABLE tbl_client_note ADD COLUMN is_dismissed		BOOLEAN;
ALTER TABLE tbl_client_note ADD COLUMN dismissed_by		INTEGER REFERENCES tbl_staff (staff_id);
ALTER TABLE tbl_client_note ADD COLUMN dismissed_at		TIMESTAMP(0);
ALTER TABLE tbl_client_note ADD COLUMN dismissed_comment	TEXT;

ALTER TABLE tbl_client_note_log ADD COLUMN front_page_until	TIMESTAMP(0);
ALTER TABLE tbl_client_note_log ADD COLUMN flag_entry_until TIMESTAMP(0);
ALTER TABLE tbl_client_note_log ADD COLUMN is_entry_dismissable	BOOLEAN;
ALTER TABLE tbl_client_note_log ADD COLUMN is_dismissed		BOOLEAN;
ALTER TABLE tbl_client_note_log ADD COLUMN dismissed_by		INTEGER;
ALTER TABLE tbl_client_note_log ADD COLUMN dismissed_at		TIMESTAMP(0);
ALTER TABLE tbl_client_note_log ADD COLUMN dismissed_comment	TEXT;

ALTER TABLE tbl_client_note ADD 
	CONSTRAINT dismissed_check CHECK (COALESCE(is_dismissed::text,dismissed_at::text,dismissed_by::text,dismissed_comment) IS NULL
		OR ( ( (is_dismissed::text || dismissed_at::text || dismissed_by::text) IS NOT NULL) AND is_entry_dismissable));

DROP VIEW elevated_concern_note;
DROP VIEW client_note;

CREATE VIEW client_note  AS (
SELECT
	client_note_id,
	client_id,
	is_front_page,
	front_page_until,
	flag_entry_codes,
	flag_entry_until,
	is_entry_dismissable,
	note,
	is_dismissed,
	dismissed_by,
	dismissed_at,
	dismissed_comment,
	--system fields
	added_by,
	added_at,
	changed_by,
	changed_at,
	is_deleted,
	deleted_at,
	deleted_by,
	deleted_comment,
	sys_log

FROM tbl_client_note WHERE NOT is_deleted

);

CREATE OR REPLACE VIEW client_note_flag_entry AS
SELECT * FROM client_note 
	WHERE (flag_entry_codes IS NOT NULL)
	AND NOT  ( is_entry_dismissable AND (  is_dismissed IS NOT NULL ) AND is_dismissed)
	AND (COALESCE(flag_entry_until,current_timestamp)>=current_timestamp);

CREATE OR REPLACE VIEW client_note_front_page AS
SELECT * FROM client_note WHERE is_front_page
	AND COALESCE(front_page_until,current_timestamp) >= current_timestamp;

\i ../client/create.view.elevated_concern_note.sql

COMMIT;

