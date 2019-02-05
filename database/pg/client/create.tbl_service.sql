CREATE TABLE tbl_service (
	service_id				SERIAL PRIMARY KEY,
	client_id				INTEGER NOT NULL REFERENCES tbl_client (client_id),
	contact_type_code			VARCHAR(10) NOT NULL DEFAULT 'FACE2FACE' REFERENCES tbl_l_contact_type(contact_type_code),
	service_code			VARCHAR(10) NOT NULL REFERENCES tbl_l_service (service_code), 
	service_minutes 			INTEGER NOT NULL CHECK (service_minutes >= 0),
	service_by				INTEGER NOT NULL REFERENCES tbl_staff (staff_id),
	service_date			TIMESTAMP(0) NOT NULL CHECK (service_date::date <= CURRENT_DATE),
	asam_dimension_1			BOOLEAN NOT NULL DEFAULT FALSE,
	asam_dimension_2			BOOLEAN NOT NULL DEFAULT FALSE,
	asam_dimension_3			BOOLEAN NOT NULL DEFAULT FALSE,
	asam_dimension_4			BOOLEAN NOT NULL DEFAULT FALSE,
	asam_dimension_5			BOOLEAN NOT NULL DEFAULT FALSE,
	asam_dimension_6			BOOLEAN NOT NULL DEFAULT FALSE,
	progress_note			TEXT,
	service_project_code		VARCHAR(10) NOT NULL REFERENCES tbl_l_service_project ( service_project_code ),
	service_progress_note_id	INTEGER REFERENCES tbl_service ( service_id ),
	added_by     			INTEGER NOT NULL REFERENCES tbl_staff (staff_id),
	added_at     			TIMESTAMP(0)     NOT NULL DEFAULT CURRENT_TIMESTAMP,
	changed_by     			INTEGER NOT NULL  REFERENCES tbl_staff (staff_id),
	changed_at     			TIMESTAMP(0)     NOT NULL DEFAULT CURRENT_TIMESTAMP,
	is_deleted    			BOOLEAN NOT NULL DEFAULT FALSE,
	deleted_at   			TIMESTAMP(0),
	deleted_by   			INTEGER REFERENCES tbl_staff (staff_id),
	deleted_comment 			TEXT,
	sys_log 				TEXT

	CONSTRAINT asam_for_cd_only CHECK ((service_project_code='CD' AND
								(asam_dimension_1 OR asam_dimension_2 OR asam_dimension_3
									OR asam_dimension_4 OR asam_dimension_5 OR asam_dimension_6
									OR service_code='CD_TPR'))
							OR 
							(service_project_code<>'CD' AND NOT
								(asam_dimension_1 OR asam_dimension_2 OR asam_dimension_3
									OR asam_dimension_4 OR asam_dimension_5 OR asam_dimension_6
									OR service_code='CD_TPR')))

	CONSTRAINT progress_note_or_reference
		CHECK ( (progress_note IS NOT NULL AND service_progress_note_id IS NULL)
			OR (progress_note IS NULL AND service_progress_note_id IS NOT NULL) )
);

CREATE VIEW service AS SELECT * FROM tbl_service WHERE NOT is_deleted;

CREATE INDEX index_tbl_service_client_id ON tbl_service ( client_id );
CREATE INDEX index_tbl_service_service_project_code ON tbl_service ( service_project_code );
CREATE INDEX index_tbl_service_optomized_client_id_project ON tbl_service ( service_project_code, client_id );
CREATE INDEX index_tbl_service_service_date ON tbl_service ( service_date );
CREATE INDEX index_tbl_service_service_progress_note_id ON tbl_service ( service_progress_note_id );
