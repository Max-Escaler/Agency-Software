CREATE TABLE tbl_l_client_death_location (
	client_death_location_code	VARCHAR(10) PRIMARY KEY,
	description				TEXT NOT NULL UNIQUE,
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

INSERT INTO tbl_l_client_death_location VALUES ('RESIDENCE','Client\'s Residence',sys_user(),current_timestamp,sys_user(),current_timestamp);
INSERT INTO tbl_l_client_death_location VALUES ('AGENCY','Agency Facilities',sys_user(),current_timestamp,sys_user(),current_timestamp);
INSERT INTO tbl_l_client_death_location VALUES ('SHELTER','Emergency Shelter',sys_user(),current_timestamp,sys_user(),current_timestamp);
INSERT INTO tbl_l_client_death_location VALUES ('HOSPITAL','Hospital/Medical Facility',sys_user(),current_timestamp,sys_user(),current_timestamp);
INSERT INTO tbl_l_client_death_location VALUES ('JAIL','Jail/Prison',sys_user(),current_timestamp,sys_user(),current_timestamp);
INSERT INTO tbl_l_client_death_location VALUES ('STREET','Streets or Outside',sys_user(),current_timestamp,sys_user(),current_timestamp);
INSERT INTO tbl_l_client_death_location VALUES ('OTHER','Other',sys_user(),current_timestamp,sys_user(),current_timestamp);
INSERT INTO tbl_l_client_death_location VALUES ('UNKNOWN','Unknown',sys_user(),current_timestamp,sys_user(),current_timestamp);

CREATE VIEW l_client_death_location AS (SELECT * FROM tbl_l_client_death_location WHERE NOT is_deleted);
