CREATE TABLE tbl_guest (
	guest_id SERIAL PRIMARY KEY,
	name_last varchar(40) NOT NULL,
	name_first varchar(40) NOT NULL,
	name_middle varchar(40),
	name_alias varchar(120),
	client_id INTEGER REFERENCES tbl_client (client_id),
    dob DATE NOT NULL, -- Not Null?
    guest_photo INTEGER,  --for photo of guest
	-- These fields can be filled in directly.  If NULL, in the guest view 
	-- they will be populated from the guest_identification table
	identification_type_code varchar(10) REFERENCES tbl_l_identification_type (identification_type_code),
	identification_number varchar(80),
	identification_expiration_date DATE,
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

