CREATE TABLE tbl_guest_visit (
    guest_visit_id SERIAL PRIMARY KEY,
	entered_at TIMESTAMP(0),
	exited_at TIMESTAMP(0),
	guest_id INTEGER NOT NULL REFERENCES tbl_guest (guest_id),
	client_id  INTEGER NOT NULL REFERENCES tbl_client (client_id),
	housing_unit_code varchar(10) NOT NULL REFERENCES tbl_housing_unit(housing_unit_code),
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

CREATE OR REPLACE VIEW guest_visit AS SELECT * FROM tbl_guest_visit WHERE NOT is_deleted;

