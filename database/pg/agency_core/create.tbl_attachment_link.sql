/*
 * FIXME: should this use reference instead?
 */

CREATE TABLE tbl_attachment_link (
	attachment_link_id		SERIAL PRIMARY KEY,
	parent_object			VARCHAR(20) NOT NULL,
	parent_field_name		VARCHAR(40) NOT NULL,
	attachment_id			INTEGER NOT NULL REFERENCES tbl_attachment (attachment_id),
	parent_id_obsolete		INTEGER,
	obsolete_at				TIMESTAMP(0),
	obsolete_by				INTEGER REFERENCES tbl_staff( staff_id),
	
	--system fields
	added_by                        INTEGER NOT NULL REFERENCES tbl_staff (staff_id),
	added_at                        TIMESTAMP(0)     NOT NULL DEFAULT CURRENT_TIMESTAMP,
	changed_by                      INTEGER NOT NULL  REFERENCES tbl_staff (staff_id),
	changed_at                      TIMESTAMP(0)     NOT NULL DEFAULT CURRENT_TIMESTAMP,
	is_deleted                      BOOLEAN NOT NULL DEFAULT FALSE,
	deleted_at                      TIMESTAMP(0) CHECK ((NOT is_deleted AND deleted_at IS NULL) OR (is_deleted AND deleted_at IS NOT NULL)),
	deleted_by                      INTEGER REFERENCES tbl_staff(staff_id)
		CHECK ((NOT is_deleted AND deleted_by IS NULL) OR (is_deleted AND deleted_by IS NOT NULL)),
	deleted_comment         TEXT,
	sys_log                 TEXT,
	CONSTRAINT no_incomplete_obsoletes CHECK (
		( parent_id_obsolete IS NULL AND obsolete_at IS NULL AND obsolete_by IS NULL)
		OR (parent_id_obsolete IS NOT NULL and obsolete_at IS NOT NULL and obsolete_by IS NOT NULL)
	)
);

CREATE VIEW attachment_link AS (SELECT * FROM tbl_attachment_link WHERE NOT is_deleted);
