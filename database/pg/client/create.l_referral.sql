CREATE TABLE tbl_l_referral (
	referral_code VARCHAR(10) PRIMARY KEY,
	description TEXT NOT NULL UNIQUE,
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

INSERT INTO tbl_l_referral VALUES ('OTHPROV','Other Provider',sys_user(),current_timestamp,sys_user(),current_timestamp);
INSERT INTO tbl_l_referral VALUES ('BASICFOOD','Basic Needs - Food',sys_user(),current_timestamp,sys_user(),current_timestamp);
INSERT INTO tbl_l_referral VALUES ('BASICITEM','Basic Needs - Household Items',sys_user(),current_timestamp,sys_user(),current_timestamp);
INSERT INTO tbl_l_referral VALUES ('BASICCLOTH','Basic Needs - Clothing',sys_user(),current_timestamp,sys_user(),current_timestamp);
INSERT INTO tbl_l_referral VALUES ('BASICID','Basic Needs - Identification Assistance',sys_user(),current_timestamp,sys_user(),current_timestamp);
INSERT INTO tbl_l_referral VALUES ('BASICHYG','Basic Needs - Hygiene Services',sys_user(),current_timestamp,sys_user(),current_timestamp);
INSERT INTO tbl_l_referral VALUES ('HEALTH','Healthcare Services',sys_user(),current_timestamp,sys_user(),current_timestamp);
INSERT INTO tbl_l_referral VALUES ('MHIN','Mental Health Services - Inpatient',sys_user(),current_timestamp,sys_user(),current_timestamp);
INSERT INTO tbl_l_referral VALUES ('MHOUT','Mental Health Services - Outpatient',sys_user(),current_timestamp,sys_user(),current_timestamp);
INSERT INTO tbl_l_referral VALUES ('CDIN','Chemical Dependency Services - Inpatient',sys_user(),current_timestamp,sys_user(),current_timestamp);
INSERT INTO tbl_l_referral VALUES ('CDOUT','Chemical Dependency Services - Outpatient',sys_user(),current_timestamp,sys_user(),current_timestamp);
INSERT INTO tbl_l_referral VALUES ('CDDETOX','Chemical Dependency Services - Detox',sys_user(),current_timestamp,sys_user(),current_timestamp);
INSERT INTO tbl_l_referral VALUES ('CDASSESS','Chemical Dependency Services - Assessment Center (ADATSA)',sys_user(),current_timestamp,sys_user(),current_timestamp);
INSERT INTO tbl_l_referral VALUES ('CDSOBER','Chemical Dependency Services - Sobering Center',sys_user(),current_timestamp,sys_user(),current_timestamp);
INSERT INTO tbl_l_referral VALUES ('CDAANA','Chemical Dependency Services - NA/AA (12 Step Group',sys_user(),current_timestamp,sys_user(),current_timestamp);
INSERT INTO tbl_l_referral VALUES ('CDSUP','Chemical Dependency Services - Support Group',sys_user(),current_timestamp,sys_user(),current_timestamp);
INSERT INTO tbl_l_referral VALUES ('H_SHELTER','Housing - Shelter',sys_user(),current_timestamp,sys_user(),current_timestamp);
INSERT INTO tbl_l_referral VALUES ('H_TRANS','Housing - Transitional',sys_user(),current_timestamp,sys_user(),current_timestamp);
INSERT INTO tbl_l_referral VALUES ('H_PERM','Housing - Permanent',sys_user(),current_timestamp,sys_user(),current_timestamp);
INSERT INTO tbl_l_referral VALUES ('ENTIT','Entitlements',sys_user(),current_timestamp,sys_user(),current_timestamp);

CREATE VIEW l_referral AS (SELECT * FROM tbl_l_referral WHERE NOT is_deleted);

