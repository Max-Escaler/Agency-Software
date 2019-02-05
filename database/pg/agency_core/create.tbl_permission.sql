CREATE TABLE tbl_permission (
	permission_id		SERIAL PRIMARY KEY,
	permission_type_code	VARCHAR(20) NOT NULL REFERENCES tbl_l_permission_type (permission_type_code),
	staff_id			INTEGER REFERENCES tbl_staff (staff_id),
	agency_program_code	VARCHAR(10) REFERENCES tbl_l_agency_program (agency_program_code),
	agency_project_code VARCHAR(10) REFERENCES tbl_l_agency_project (agency_project_code),
	staff_position_code	VARCHAR(10) REFERENCES tbl_l_staff_position (staff_position_code),
	permission_date		DATE NOT NULL,
	permission_read		BOOLEAN,
	permission_write 		BOOLEAN,
	permission_super 		BOOLEAN,
	permission_date_end	DATE,
	comment			TEXT,
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

CREATE VIEW permission AS
	SELECT *,
		CASE WHEN coalesce(agency_program_code,agency_project_code,staff_position_code,staff_id::varchar) IS NULL THEN 'BLANKET'
		ELSE TRIM(
			CASE WHEN agency_program_code IS NOT NULL THEN 'PROG ' ELSE '' END
			|| CASE WHEN agency_project_code IS NOT NULL THEN 'PROJ ' ELSE '' END
			|| CASE WHEN staff_position_code IS NOT NULL THEN 'POS ' ELSE '' END
			|| CASE WHEN staff_id IS NOT NULL THEN 'ID' ELSE '' END)
		END AS permission_basis
	FROM tbl_permission
	WHERE NOT is_deleted;

CREATE VIEW permission_current AS 
	SELECT * FROM permission 
	WHERE permission_date <= CURRENT_DATE 
	AND (permission_date_end IS NULL OR permission_date_end > CURRENT_DATE);

CREATE INDEX index_tbl_permission_staff_id ON tbl_permission ( staff_id );
CREATE INDEX index_tbl_permission_staff_id_dates ON tbl_permission ( staff_id,permission_date,permission_date_end );
CREATE INDEX index_tbl_permission_permission_dates ON tbl_permission ( permission_date,permission_date_end );
CREATE INDEX index_tbl_permission_permission_date ON tbl_permission ( permission_date );
CREATE INDEX index_tbl_permission_permission_date_end ON tbl_permission ( permission_date_end );
