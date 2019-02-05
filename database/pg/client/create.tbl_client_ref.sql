CREATE TABLE tbl_client_ref (
	client_ref_id	SERIAL PRIMARY KEY,
	client_id		INTEGER NOT NULL REFERENCES tbl_client ( client_id ),
	ref_table		VARCHAR(30) NOT NULL,
	ref_id		INTEGER NOT NULL,
	--system fields
	added_by			INTEGER NOT NULL REFERENCES tbl_staff (staff_id),
	added_at			TIMESTAMP(0)     NOT NULL DEFAULT CURRENT_TIMESTAMP,
	changed_by			INTEGER NOT NULL  REFERENCES tbl_staff (staff_id),
	changed_at			TIMESTAMP(0)     NOT NULL DEFAULT CURRENT_TIMESTAMP,
	sys_log			TEXT
);

CREATE VIEW client_ref AS SELECT * FROM tbl_client_ref;

CREATE RULE client_ref_delete AS
ON DELETE TO tbl_client_ref
DO INSTEAD NOTHING;
