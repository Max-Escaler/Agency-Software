CREATE TABLE tbl_housing_notice (
	housing_notice_id			SERIAL PRIMARY KEY,
	client_id				INTEGER NOT NULL REFERENCES tbl_client (client_id),
	housing_notice_date		DATE NOT NULL,
--	housing_notice_date_end		DATE,
--	housing_project_code			VARCHAR(10) NOT NULL REFERENCES tbl_l_agency_project(agency_project_code),
	housing_notice_type_code	VARCHAR(10) NOT NULL REFERENCES tbl_l_housing_notice_type (housing_notice_type_code),
	housing_notice_reason_code	VARCHAR(10) NOT NULL REFERENCES tbl_l_housing_notice_reason (housing_notice_reason_code),
	response_due_date			DATE,
	response_received_date		DATE,
	housing_notice_compliance_status_code	VARCHAR(10) REFERENCES tbl_l_housing_notice_compliance_status(housing_notice_compliance_status_code),
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

CREATE OR REPLACE VIEW housing_notice AS
SELECT * FROM tbl_housing_notice WHERE NOT is_deleted;

CREATE INDEX index_tbl_housing_notice_client_id_housing_notice_date ON tbl_housing_notice ( client_id,housing_notice_date );
CREATE INDEX index_tbl_housing_notice_housing_notice_date_client_id ON tbl_housing_notice ( housing_notice_date,client_id );
CREATE INDEX index_tbl_housing_notice_housing_notice_date ON tbl_housing_notice ( housing_notice_date );
