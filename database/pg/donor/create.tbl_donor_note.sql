CREATE TABLE tbl_donor_note (
	donor_note_id		SERIAL PRIMARY KEY,
	is_front_page		BOOLEAN NOT NULL DEFAULT TRUE,
	donor_id			INTEGER NOT NULL REFERENCES tbl_donor(donor_id),
	note				TEXT NOT NULL,
	staff_id			INTEGER NOT NULL REFERENCES tbl_staff (staff_id),
	agency_program_code	VARCHAR(10) REFERENCES tbl_l_agency_program (agency_program_code),
	agency_project_code	VARCHAR(10) REFERENCES tbl_l_agency_project (agency_project_code),
	staff_position_code	VARCHAR(10) REFERENCES tbl_l_staff_position(staff_position_code),
--system fields
	added_by     INTEGER NOT NULL REFERENCES tbl_staff (staff_id),
	added_at     TIMESTAMP(0)     NOT NULL DEFAULT CURRENT_TIMESTAMP,
	changed_by     INTEGER NOT NULL  REFERENCES tbl_staff (staff_id),
	changed_at    TIMESTAMP(0)     NOT NULL DEFAULT CURRENT_TIMESTAMP,
	is_deleted    BOOLEAN NOT NULL DEFAULT FALSE,
	deleted_at   TIMESTAMP(0),
	deleted_by   INTEGER REFERENCES tbl_staff(staff_id),
	deleted_comment TEXT,
	sys_log TEXT
);

CREATE OR REPLACE VIEW donor_note AS
SELECT * FROM tbl_donor_note WHERE NOT is_deleted;

CREATE TRIGGER tbl_donor_note_insert
BEFORE INSERT ON tbl_donor_note
FOR EACH ROW EXECUTE PROCEDURE donor_note_insert();

CREATE TRIGGER tbl_donor_note_update
BEFORE UPDATE ON tbl_donor_note
FOR EACH ROW EXECUTE PROCEDURE donor_note_update();
