CREATE TABLE tbl_shelter_count (
	shelter_count_id		SERIAL PRIMARY KEY,
	shelter_count_date		DATE_PAST NOT NULL UNIQUE,

	breakfast_served			BOOLEAN NOT NULL,
	breakfast_time			TIME,
	breakfast_temperature		INTEGER CHECK (breakfast_temperature >= 0),
	breakfast_firsts		INTEGER CHECK (breakfast_firsts >= 0),
	breakfast_seconds		INTEGER CHECK (breakfast_seconds >= 0),
	breakfast_menu_description	TEXT,

	dinner_served			BOOLEAN NOT NULL,
	dinner_time			TIME,
	dinner_temperature		INTEGER CHECK (dinner_temperature >= 0),
	dinner_firsts			INTEGER CHECK (dinner_firsts >= 0),
	dinner_seconds			INTEGER CHECK (dinner_seconds >= 0),
	dinner_menu_description		TEXT,

	towels				INTEGER NOT NULL CHECK (towels >= 0),
	comments			TEXT,
--system fields
	added_by			INTEGER NOT NULL REFERENCES tbl_staff (staff_id),
	added_at			TIMESTAMP(0)     NOT NULL DEFAULT CURRENT_TIMESTAMP,
	changed_by			INTEGER NOT NULL  REFERENCES tbl_staff (staff_id),
	changed_at			TIMESTAMP(0)     NOT NULL DEFAULT CURRENT_TIMESTAMP,
	is_deleted			BOOLEAN NOT NULL DEFAULT FALSE,
	deleted_at			TIMESTAMP(0),
	deleted_by			INTEGER REFERENCES tbl_staff(staff_id),
	deleted_comment		TEXT,
	sys_log			TEXT
);

CREATE INDEX index_tbl_shelter_count_client_id ON tbl_shelter_count ( client_id );
