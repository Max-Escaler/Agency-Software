CREATE TABLE tbl_shelter_reg (
	shelter_reg_id		SERIAL PRIMARY KEY,
	client_id			INTEGER NOT NULL REFERENCES tbl_client(client_id),
	shelter_reg_date		DATE NOT NULL,
	shelter_reg_date_end 	DATE,
	bed_rereg_code		VARCHAR(10) NOT NULL REFERENCES tbl_l_bed_rereg(bed_rereg_code),
	overnight_eligible	BOOLEAN NOT NULL DEFAULT FALSE,

	priority_cd			BOOLEAN NOT NULL DEFAULT FALSE,
 	priority_dd			BOOLEAN NOT NULL DEFAULT FALSE,
 	priority_disabled		BOOLEAN NOT NULL DEFAULT FALSE,
 	priority_med     		BOOLEAN NOT NULL DEFAULT FALSE,
 	priority_mh     		BOOLEAN NOT NULL DEFAULT FALSE,
 	priority_new    		BOOLEAN NOT NULL DEFAULT FALSE,
 	priority_ksh    		BOOLEAN NOT NULL DEFAULT FALSE,
	priority_queen_anne	BOOLEAN NOT NULL DEFAULT FALSE,
	chronic_homeless_status_code	VARCHAR(10) NOT NULL REFERENCES tbl_l_chronic_homeless_status,
	last_residence_code	VARCHAR(10) NOT NULL REFERENCES tbl_l_last_residence(last_residence_code),
	last_residence_ownr	VARCHAR(100),
	svc_need_code		VARCHAR(10) NOT NULL REFERENCES tbl_l_svc_need(svc_need_code),
	comments			TEXT,
	added_at			TIMESTAMP(0) NOT NULL DEFAULT CURRENT_TIMESTAMP,
	added_by			INTEGER NOT NULL REFERENCES tbl_staff (staff_id),
	changed_at			TIMESTAMP(0) NOT NULL DEFAULT CURRENT_TIMESTAMP,
	changed_by			INTEGER NOT NULL REFERENCES tbl_staff (staff_id),
	is_deleted			BOOLEAN NOT NULL DEFAULT FALSE,
	deleted_at			TIMESTAMP(0),
	deleted_by			INTEGER REFERENCES tbl_staff (staff_id),
	deleted_comment		TEXT,
	sys_log			TEXT
);

CREATE INDEX tbl_shelter_reg_index_client_id ON tbl_shelter_reg (client_id);
CREATE INDEX tbl_shelter_reg_index_shelter_reg_date_end ON tbl_shelter_reg (shelter_reg_date_end);
CREATE INDEX tbl_shelter_reg_index_is_deleted ON tbl_shelter_reg (is_deleted);
CREATE INDEX tbl_shelter_reg_index_date_client_deleted ON tbl_shelter_reg (client_id,shelter_reg_date_end,is_deleted);

