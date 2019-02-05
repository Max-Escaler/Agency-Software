CREATE TABLE tbl_elevated_concern (
	elevated_concern_id			SERIAL PRIMARY KEY,
	client_id				INTEGER NOT NULL REFERENCES tbl_client ( client_id ),
	elevated_concern_date			DATE_PAST NOT NULL DEFAULT CURRENT_DATE,
	elevated_concern_date_end			DATE CHECK (elevated_concern_date_end >= elevated_concern_date),
	elevated_concern_reason_codes		VARCHAR[] NOT NULL,
	elevated_concern_reason_detail		TEXT NOT NULL,
	current_status_code			VARCHAR(10) NOT NULL REFERENCES tbl_l_elevated_concern_status ( elevated_concern_status_code )
							DEFAULT 'INITIAL',
	point_person				INTEGER NOT NULL REFERENCES tbl_staff ( staff_id ),
	ecl_point_case_manager			INTEGER NOT NULL REFERENCES tbl_staff ( staff_id ),
	other_team_members			INTEGER[],
	next_meeting_date			DATE,
	elevated_concern_plan			TEXT,
	specific_directions_to_all_staff	TEXT,
	expectations				TEXT,
	criteria_for_removal			TEXT,
	reasons_for_removal			TEXT,
	--system fields
	added_by				INTEGER NOT NULL REFERENCES tbl_staff (staff_id),
	added_at				TIMESTAMP(0)     NOT NULL DEFAULT CURRENT_TIMESTAMP,
	changed_by				INTEGER NOT NULL  REFERENCES tbl_staff (staff_id),
	changed_at				TIMESTAMP(0)     NOT NULL DEFAULT CURRENT_TIMESTAMP,
	is_deleted				BOOLEAN NOT NULL DEFAULT FALSE,
	deleted_at				TIMESTAMP(0) CHECK ((NOT is_deleted AND deleted_at IS NULL) OR (is_deleted AND deleted_at IS NOT NULL)),
	deleted_by				INTEGER REFERENCES tbl_staff(staff_id)
						  CHECK ((NOT is_deleted AND deleted_by IS NULL) OR (is_deleted AND deleted_by IS NOT NULL)),
	deleted_comment			TEXT,
	sys_log				TEXT

	CONSTRAINT removal_status_check CHECK ( (current_status_code <> 'REMOVED' AND elevated_concern_date_end IS NULL AND reasons_for_removal IS NULL)
						OR (current_status_code = 'REMOVED' AND elevated_concern_date_end IS NOT NULL AND reasons_for_removal IS NOT NULL))
);

COMMENT ON COLUMN tbl_elevated_concern.specific_directions_to_all_staff IS 'This text will display at the top of the client''s page, and be viewable by all staff';

COMMENT ON COLUMN tbl_elevated_concern.reasons_for_removal IS 'Detail the reasons client is being removed from the List (must be completed when removing client)';
COMMENT ON COLUMN tbl_elevated_concern.expectations IS 'Expectations beyond standard Elevated Concern List protocol';
COMMENT ON COLUMN tbl_elevated_concern.current_status_code IS 'Change to "Implementing" after initial meeting';
COMMENT ON COLUMN tbl_elevated_concern.ecl_point_case_manager IS 'MH or CD CM who will attempt daily contact';
COMMENT ON COLUMN tbl_elevated_concern.criteria_for_removal IS 'Conditions under which client will be removed from the List';


CREATE OR REPLACE VIEW elevated_concern AS
SELECT * FROM tbl_elevated_concern WHERE NOT is_deleted;

CREATE OR REPLACE VIEW elevated_concern_current AS
SELECT * FROM elevated_concern WHERE elevated_concern_date_end IS NULL OR elevated_concern_date_end > CURRENT_DATE;

CREATE INDEX index_tbl_elevated_concern_client_id ON tbl_elevated_concern ( client_id );
CREATE INDEX index_tbl_elevated_concern_elevated_concern_date ON tbl_elevated_concern ( elevated_concern_date );
CREATE INDEX index_tbl_elevated_concern_elevated_concern_date_end ON tbl_elevated_concern ( elevated_concern_date_end );
CREATE INDEX index_tbl_elevated_concern_client_id_elevated_concern_date_end ON tbl_elevated_concern ( client_id,elevated_concern_date_end );
CREATE INDEX index_tbl_elevated_concern_client_id_elevated_concern_date ON tbl_elevated_concern ( client_id,elevated_concern_date );
CREATE INDEX index_tbl_elevated_concern_client_id_dates ON tbl_elevated_concern ( client_id,elevated_concern_date,elevated_concern_date_end );

--triggers
CREATE TRIGGER insert_update_post_log AFTER INSERT OR UPDATE
	ON tbl_elevated_concern FOR EACH ROW
	EXECUTE PROCEDURE elevated_concern_post_log();
