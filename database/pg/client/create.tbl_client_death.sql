CREATE TABLE tbl_client_death (
	client_death_id			SERIAL PRIMARY KEY,
	client_id				INTEGER NOT NULL REFERENCES tbl_client ( client_id ) UNIQUE,
	client_death_date			DATE NOT NULL CHECK (client_death_date <= CURRENT_DATE),
	client_death_date_accuracy	VARCHAR(10) NOT NULL REFERENCES tbl_l_accuracy ( accuracy_code ),
	client_death_data_source_code	VARCHAR(10) NOT NULL REFERENCES tbl_l_client_death_data_source ( client_death_data_source_code ),
	client_death_location_code	VARCHAR(10) NOT NULL REFERENCES tbl_l_client_death_location ( client_death_location_code ),
	client_death_type_code		VARCHAR(10) NOT NULL REFERENCES tbl_l_client_death_type ( client_death_type_code ),
	was_death_at_org			BOOLEAN NOT NULL,
	comments				TEXT NOT NULL,
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

CREATE VIEW client_death AS SELECT * FROM tbl_client_death WHERE NOT is_deleted;
