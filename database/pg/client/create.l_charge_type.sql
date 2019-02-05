CREATE TABLE tbl_l_charge_type (
	charge_type_code	VARCHAR(10) PRIMARY KEY,
	description		VARCHAR(80) NOT NULL UNIQUE,
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

INSERT INTO tbl_l_charge_type (charge_type_code, description, added_by, added_at, changed_by, changed_at) VALUES ('ADJUST', 'Adjustment Charge',sys_user(),current_timestamp,sys_user(),current_timestamp);
INSERT INTO tbl_l_charge_type (charge_type_code, description, added_by, added_at, changed_by, changed_at) VALUES ('DAMAGE', 'Damage Charge',sys_user(),current_timestamp,sys_user(),current_timestamp);
INSERT INTO tbl_l_charge_type (charge_type_code, description, added_by, added_at, changed_by, changed_at) VALUES ('KEY', 'Key Charge',sys_user(),current_timestamp,sys_user(),current_timestamp);
INSERT INTO tbl_l_charge_type (charge_type_code, description, added_by, added_at, changed_by, changed_at) VALUES ('MISC', 'Miscellaneous/Other Charges',sys_user(),current_timestamp,sys_user(),current_timestamp);
INSERT INTO tbl_l_charge_type (charge_type_code, description, added_by, added_at, changed_by, changed_at) VALUES ('RENT', 'Rent Charges',sys_user(),current_timestamp,sys_user(),current_timestamp);
INSERT INTO tbl_l_charge_type (charge_type_code, description, added_by, added_at, changed_by, changed_at) VALUES ('SECURITY', 'Security Deposit',sys_user(),current_timestamp,sys_user(),current_timestamp);
INSERT INTO tbl_l_charge_type (charge_type_code, description, added_by, added_at, changed_by, changed_at) VALUES ('SUBSIDY', 'Subsidy Charge',sys_user(),current_timestamp,sys_user(),current_timestamp);
INSERT INTO tbl_l_charge_type (charge_type_code, description, added_by, added_at, changed_by, changed_at) VALUES ('VACANCY', 'Vacancy Charge',sys_user(),current_timestamp,sys_user(),current_timestamp);
INSERT INTO tbl_l_charge_type (charge_type_code, description, added_by, added_at, changed_by, changed_at) VALUES ('WRITEOFF', 'Write Off of Existing Charges',sys_user(),current_timestamp,sys_user(),current_timestamp);

CREATE VIEW l_charge_type AS (SELECT * FROM tbl_l_charge_type WHERE NOT is_deleted);
