CREATE TABLE tbl_housing_rsp (
	housing_rsp_id			SERIAL PRIMARY KEY,
	client_id				INTEGER NOT NULL REFERENCES tbl_client (client_id),
	housing_rsp_date			DATE NOT NULL,
	housing_rsp_date_end		DATE,
--	housing_project_code	VARCHAR(10) NOT NULL REFERENCES tbl_l_agency_project(agency_project_code),
	housing_rsp_status_code		VARCHAR(10) NOT NULL REFERENCES tbl_l_housing_rsp_status (housing_rsp_status_code),
	resident_participation_code	VARCHAR(10) NOT NULL REFERENCES tbl_l_resident_participation (resident_participation_code),
	comments				TEXT,
	--system fields
	added_by     		INTEGER NOT NULL REFERENCES tbl_staff (staff_id),
	added_at     		TIMESTAMP(0)     NOT NULL DEFAULT CURRENT_TIMESTAMP,
	changed_by     		INTEGER NOT NULL  REFERENCES tbl_staff (staff_id),
	changed_at     		TIMESTAMP(0)     NOT NULL DEFAULT CURRENT_TIMESTAMP,
	is_deleted    		BOOLEAN NOT NULL DEFAULT FALSE,
	deleted_at   		TIMESTAMP(0),
	deleted_by   		INTEGER REFERENCES tbl_staff(staff_id),
	deleted_comment 		TEXT,
	sys_log 			TEXT
);

CREATE OR REPLACE VIEW housing_rsp AS
SELECT * FROM tbl_housing_rsp WHERE NOT is_deleted;

CREATE INDEX index_tbl_housing_rsp_client_id_housing_rsp_date ON tbl_housing_rsp ( client_id,housing_rsp_date );
CREATE INDEX index_tbl_housing_rsp_housing_rsp_date_client_id ON tbl_housing_rsp ( housing_rsp_date,client_id );
CREATE INDEX index_tbl_housing_rsp_housing_rsp_date ON tbl_housing_rsp ( housing_rsp_date );
