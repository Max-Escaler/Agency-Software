CREATE TABLE tbl_l_donor_type (
	donor_type_code VARCHAR(10) PRIMARY KEY,
	description TEXT NOT NULL UNIQUE,
	default_mip_account INTEGER NOT NULL,
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

INSERT INTO tbl_l_donor_type VALUES ('CORP','Corporation',6430,sys_user(),current_timestamp,sys_user(),current_timestamp);
INSERT INTO tbl_l_donor_type VALUES ('FFO','Federated Funding Org.',6450,sys_user(),current_timestamp,sys_user(),current_timestamp);
INSERT INTO tbl_l_donor_type VALUES ('FOUN','Foundations',6410,sys_user(),current_timestamp,sys_user(),current_timestamp);
INSERT INTO tbl_l_donor_type VALUES ('GOVE','Government',6420,sys_user(),current_timestamp,sys_user(),current_timestamp);
INSERT INTO tbl_l_donor_type VALUES ('INDI','Individual',6420,sys_user(),current_timestamp,sys_user(),current_timestamp);
INSERT INTO tbl_l_donor_type VALUES ('ORGN','Civic/Religous Groups',6440,sys_user(),current_timestamp,sys_user(),current_timestamp);
INSERT INTO tbl_l_donor_type VALUES ('OTHE','Other type',6420,sys_user(),current_timestamp,sys_user(),current_timestamp);

CREATE VIEW l_donor_type AS (SELECT * FROM tbl_l_donor_type WHERE NOT is_deleted);

