/*
CREATE TABLE      tbl_hiv (
	hiv_id			SERIAL PRIMARY KEY NOT NULL,
	client_id		    	INTEGER NOT NULL,
	hiv_positive		BOOLEAN NOT NULL,
	hiv_positive_date		DATE,
	hiv_positive_date_accuracy VARCHAR(10) REFERENCES tbl_l_accuracy (accuracy_code),
	aids_diagnosed		BOOLEAN NOT NULL,
	aids_diagnosed_date	DATE,
	aids_diagnosed_date_accuracy VARCHAR(10) REFERENCES tbl_l_accuracy (accuracy_code),
	aids_disabled		BOOLEAN NOT NULL,
	aids_disabled_date	DATE,
	aids_disabled_date_accuracy VARCHAR(10) REFERENCES tbl_l_accuracy (accuracy_code),
	comment			TEXT,
	--sys fields
	added_at			TIMESTAMP(0) NOT NULL DEFAULT current_timestamp,
	added_by			INTEGER NOT NULL REFERENCES tbl_staff (staff_id),
	changed_at			TIMESTAMP(0) NOT NULL DEFAULT current_timestamp,
	changed_by			INTEGER NOT NULL REFERENCES tbl_staff (staff_id),
	is_deleted			BOOLEAN NOT NULL DEFAULT FALSE,
	deleted_at			TIMESTAMP(0),
	deleted_by			INTEGER REFERENCES tbl_staff (staff_id),
	deleted_comment		TEXT,
	sys_log			TEXT
);


*/

CREATE TABLE tbl_hiv (
	hiv_id			SERIAL PRIMARY KEY NOT NULL,
	client_id		    	INTEGER NOT NULL,
	hiv_status_code		VARCHAR(10) NOT NULL REFERENCES tbl_l_hiv_status (hiv_status_code),
	comment			TEXT,
	--sys fields
	added_at			TIMESTAMP(0) NOT NULL DEFAULT current_timestamp,
	added_by			INTEGER NOT NULL REFERENCES tbl_staff (staff_id),
	changed_at			TIMESTAMP(0) NOT NULL DEFAULT current_timestamp,
	changed_by			INTEGER NOT NULL REFERENCES tbl_staff (staff_id),
	is_deleted			BOOLEAN NOT NULL DEFAULT FALSE,
	deleted_at			TIMESTAMP(0),
	deleted_by			INTEGER REFERENCES tbl_staff (staff_id),
	deleted_comment		TEXT,
	sys_log			TEXT
);
CREATE OR REPLACE VIEW hiv AS
SELECT * FROM tbl_hiv WHERE NOT is_deleted;
