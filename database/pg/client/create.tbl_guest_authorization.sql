CREATE TABLE tbl_guest_authorization (
	guest_authorization_id SERIAL PRIMARY KEY,
	guest_id INTEGER NOT NULL REFERENCES tbl_guest (guest_id),
	client_id INTEGER NOT NULL REFERENCES tbl_client (client_id),
	guest_authorization_date DATE NOT NULL,
	guest_authorization_date_end DATE,
--	allowed_project_codes VARCHAR(20)[], -- REFERENCES l_housing_project -- None will be interpreted as ALL
--   connection_type_code varchar(10) REFERENCES tbl_l_connection_type (connection_type_code),
	comment TEXT,
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
CREATE OR REPLACE VIEW guest_authorization AS SELECT * FROM tbl_guest_authorization WHERE NOT is_deleted;
CREATE VIEW guest_authorization_current AS SELECT * FROM guest_authorization WHERE COALESCE(guest_authorization_date_end,current_date) >= current_date;
