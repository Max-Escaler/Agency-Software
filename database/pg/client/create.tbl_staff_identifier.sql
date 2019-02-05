CREATE TABLE tbl_staff_identifier (
	staff_identifier_id		SERIAL PRIMARY KEY,
	staff_id			INTEGER NOT NULL REFERENCES tbl_staff ( staff_id ),
	staff_identifier_type_code	VARCHAR(10) NOT NULL REFERENCES tbl_l_export_organization ( export_organization_code ),
	staff_identifier_value		VARCHAR(20) NOT NULL,
	--system fields
	added_by			INTEGER NOT NULL REFERENCES tbl_staff (staff_id),
	added_at			TIMESTAMP(0)     NOT NULL DEFAULT CURRENT_TIMESTAMP,
	changed_by			INTEGER NOT NULL  REFERENCES tbl_staff (staff_id),
	changed_at			TIMESTAMP(0)     NOT NULL DEFAULT CURRENT_TIMESTAMP,
	is_deleted			BOOLEAN NOT NULL DEFAULT FALSE,
	deleted_at			TIMESTAMP(0),
	deleted_by			INTEGER REFERENCES tbl_staff(staff_id),
	deleted_comment		TEXT,
	sys_log			TEXT,

	UNIQUE ( staff_id,staff_identifier_type_code )	,
	UNIQUE ( staff_identifier_type_code, staff_identifier_value )
);

CREATE VIEW staff_identifier AS SELECT * FROM tbl_staff_identifier WHERE NOT is_deleted;

CREATE INDEX index_tbl_staff_identifier_staff_id ON tbl_staff_identifier ( staff_id );
