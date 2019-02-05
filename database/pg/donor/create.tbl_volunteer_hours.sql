CREATE TABLE tbl_volunteer_hours (
	volunteer_hours_id	SERIAL PRIMARY KEY,
	donor_id			INTEGER NOT NULL REFERENCES tbl_donor ( donor_id ),
	volunteer_date		DATE NOT NULL,
	volunteer_date_end	DATE,
	volunteer_hours		NUMERIC(12,2) NOT NULL,
	is_one_time			BOOLEAN NOT NULL,
	volunteer_activity_code VARCHAR(10) NOT NULL REFERENCES tbl_l_volunteer_activity ( volunteer_activity_code ),
	volunteer_location_code	VARCHAR(10) NOT NULL REFERENCES tbl_l_volunteer_location ( volunteer_location_code ),
--system fields--
	added_by     INTEGER NOT NULL REFERENCES tbl_staff (staff_id),
	added_at     TIMESTAMP(0)     NOT NULL DEFAULT CURRENT_TIMESTAMP,
	changed_by     INTEGER NOT NULL  REFERENCES tbl_staff (staff_id),
	changed_at     TIMESTAMP(0)     NOT NULL DEFAULT CURRENT_TIMESTAMP,
	is_deleted    BOOLEAN NOT NULL DEFAULT FALSE,
	deleted_at   TIMESTAMP(0),
	deleted_by   INTEGER REFERENCES tbl_staff(staff_id),
	deleted_comment TEXT,
	sys_log TEXT
);

CREATE VIEW volunteer_hours AS SELECT * FROM tbl_volunteer_hours WHERE NOT is_deleted;

CREATE INDEX index_tbl_volunteer_hours_donor_id ON tbl_volunteer_hours ( donor_id );
CREATE INDEX index_tbl_volunteer_hours_volunteer_date ON tbl_volunteer_hours ( volunteer_date );
CREATE INDEX index_tbl_volunteer_hours_volunteer_date_end ON tbl_volunteer_hours ( volunteer_date_end );
