CREATE TABLE      tbl_l_bar_location (
	bar_location_code		VARCHAR(10)     PRIMARY KEY,
	description				TEXT NOT NULL UNIQUE,
	resolution_location		BOOLEAN NOT NULL DEFAULT true,
	incident_location			BOOLEAN NOT NULL DEFAULT true,
    --system fields
    added_by                        INTEGER NOT NULL REFERENCES tbl_staff (staff_id),
    added_at                        TIMESTAMP(0)     NOT NULL DEFAULT CURRENT_TIMESTAMP,
    changed_by                      INTEGER NOT NULL  REFERENCES tbl_staff (staff_id),
    changed_at                      TIMESTAMP(0)     NOT NULL DEFAULT CURRENT_TIMESTAMP,
    is_deleted                      BOOLEAN NOT NULL DEFAULT FALSE,
    deleted_at                      TIMESTAMP(0) CHECK ((NOT is_deleted AND deleted_at IS
 NULL) OR (is_deleted AND deleted_at IS NOT NULL)),
    deleted_by                      INTEGER REFERENCES tbl_staff(staff_id)
                                       CHECK ((NOT is_deleted AND deleted_by IS NULL) OR (is_deleted AND deleted_by IS NOT NULL)),
    deleted_comment         TEXT,
    sys_log                 TEXT
);

/* Pre-populate with AGENCY facility list--customize as needed */
INSERT INTO tbl_l_bar_location (SELECT agency_facility_code AS bar_location_code, description,true,true,sys_user(),current_timestamp,sys_user(),current_timestamp FROM l_agency_facility);

CREATE VIEW l_bar_location AS (SELECT * FROM tbl_l_bar_location WHERE NOT is_deleted);

CREATE VIEW l_bar_incident_location AS SELECT bar_location_code AS bar_incident_location_code,description FROM l_bar_location WHERE incident_location;
CREATE VIEW l_bar_resolution_location AS SELECT bar_location_code AS bar_resolution_location_code,description FROM l_bar_location WHERE resolution_location;
