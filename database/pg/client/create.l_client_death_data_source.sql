CREATE TABLE tbl_l_client_death_data_source (
	client_death_data_source_code		VARCHAR(10) PRIMARY KEY,
	description					TEXT UNIQUE NOT NULL,
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

INSERT INTO tbl_l_client_death_data_source VALUES ('STAFF','Agency Staff',sys_user(),current_timestamp,sys_user(),current_timestamp);
INSERT INTO tbl_l_client_death_data_source VALUES ('MED','Medical Personnel',sys_user(),current_timestamp,sys_user(),current_timestamp);
INSERT INTO tbl_l_client_death_data_source VALUES ('NEWS','News/Public Information',sys_user(),current_timestamp,sys_user(),current_timestamp);
INSERT INTO tbl_l_client_death_data_source VALUES ('RELATIVE','Relative',sys_user(),current_timestamp,sys_user(),current_timestamp);
INSERT INTO tbl_l_client_death_data_source VALUES ('FRIEND','Friend',sys_user(),current_timestamp,sys_user(),current_timestamp);
INSERT INTO tbl_l_client_death_data_source VALUES ('OTHER','Other',sys_user(),current_timestamp,sys_user(),current_timestamp);

CREATE VIEW l_client_death_data_source AS (SELECT * FROM tbl_l_client_death_data_source WHERE NOT is_deleted);
