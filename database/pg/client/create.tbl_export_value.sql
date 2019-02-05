CREATE TABLE tbl_export_value (
	export_value_id			SERIAL PRIMARY KEY,
	lookup_table			VARCHAR(100),
	lookup_value			VARCHAR(30),
	export_value			TEXT,
	export_organization_code	VARCHAR(10) NOT NULL REFERENCES tbl_l_export_organization ( export_organization_code ),
	effective_date			DATE NOT NULL DEFAULT CURRENT_DATE,
	--system fields
	added_by				INTEGER NOT NULL REFERENCES tbl_staff (staff_id),
	added_at				TIMESTAMP(0)     NOT NULL DEFAULT CURRENT_TIMESTAMP,
	changed_by				INTEGER NOT NULL  REFERENCES tbl_staff (staff_id),
	changed_at				TIMESTAMP(0)     NOT NULL DEFAULT CURRENT_TIMESTAMP,
	is_deleted				BOOLEAN NOT NULL DEFAULT FALSE,
	deleted_at				TIMESTAMP(0),
	deleted_by				INTEGER REFERENCES tbl_staff(staff_id),
	deleted_comment			TEXT,
	sys_log				TEXT
);

CREATE VIEW export_value AS SELECT * FROM tbl_export_value WHERE NOT is_deleted;
CREATE VIEW export_value_current AS
	SELECT DISTINCT ON (lookup_table,lookup_value,export_organization_code)
	* FROM export_value ORDER BY lookup_table,lookup_value,export_organization_code,effective_date DESC;