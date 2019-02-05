CREATE TABLE tbl_chronic_homeless_status_asked (

	chronic_homeless_status_asked_id	SERIAL PRIMARY KEY,
	client_id			INTEGER NOT NULL REFERENCES tbl_client ( client_id ),
	chronic_homeless_status_code	VARCHAR(10) NOT NULL REFERENCES tbl_l_chronic_homeless_status,
	comments			TEXT, CHECK( (chronic_homeless_status_code NOT IN ('UNKNOWN','NOT_ASKED')) OR comments IS NOT NULL), 

	--system fields
	added_by			INTEGER NOT NULL REFERENCES tbl_staff (staff_id),
	added_at			TIMESTAMP(0)     NOT NULL DEFAULT CURRENT_TIMESTAMP,
	changed_by			INTEGER NOT NULL  REFERENCES tbl_staff (staff_id),
	changed_at			TIMESTAMP(0)     NOT NULL DEFAULT CURRENT_TIMESTAMP,
	is_deleted			BOOLEAN NOT NULL DEFAULT FALSE,
	deleted_at			TIMESTAMP(0) CHECK ((NOT is_deleted AND deleted_at IS NULL) OR (is_deleted AND deleted_at IS NOT NULL)),
	deleted_by			INTEGER REFERENCES tbl_staff(staff_id)
						  CHECK ((NOT is_deleted AND deleted_by IS NULL) OR (is_deleted AND deleted_by IS NOT NULL)),
	deleted_comment		TEXT,
	sys_log			TEXT

);

CREATE INDEX index_tbl_chronic_homeless_status_asked_client_id ON tbl_chronic_homeless_status_asked ( client_id );

CREATE OR REPLACE VIEW chronic_homeless_status_asked AS SELECT * FROM tbl_chronic_homeless_status_asked WHERE NOT is_deleted;

/*
If you had a limited permission user, you would want something like this:

GRANT SELECT ON chronic_homeless_status_asked to gate;
GRANT SELECT ON tbl_chronic_homeless_status_asked to gate;
*/

