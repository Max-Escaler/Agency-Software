CREATE TABLE tbl_l_facility (
    facility_code character varying(10) PRIMARY KEY,
    description character varying(100) NOT NULL UNIQUE,
    category character varying(45),
    facility_code_old character varying(10),
    housing_status character varying(10),
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

/*

*/
                 

INSERT INTO tbl_l_facility VALUES ('OTHER', 'Other (please describe)', 'UNKNOWN', '', 'UNKNOWN',sys_user(),current_timestamp,sys_user(),current_timestamp);
INSERT INTO tbl_l_facility VALUES ('UNSUBHOUSE', 'Unsubsidized housing', 'Housing', '', 'HOUSED',sys_user(),current_timestamp,sys_user(),current_timestamp);
INSERT INTO tbl_l_facility VALUES ('SHEL-OWN', 'Emergency Shelter (own)', 'Emergency Shelter', '101', 'HOMELESS',sys_user(),current_timestamp,sys_user(),current_timestamp);
INSERT INTO tbl_l_facility VALUES ('SHEL-OTH', 'Emergency Shelter (not own)', 'Emergency Shelter', '103', 'HOMELESS',sys_user(),current_timestamp,sys_user(),current_timestamp);
INSERT INTO tbl_l_facility VALUES ('HOSPITAL', 'Hospital', 'Hospital', '25', 'INSTITUT',sys_user(),current_timestamp,sys_user(),current_timestamp);
INSERT INTO tbl_l_facility VALUES ('PSYCH', 'Hospital - Psychiatric Facility', 'Psychiatric Facility', '62', 'INSTITUT',sys_user(),current_timestamp,sys_user(),current_timestamp);
INSERT INTO tbl_l_facility VALUES ('INDEPEND', 'Independent Permanent Housing', 'Permanent Housing', '', 'HOUSED',sys_user(),current_timestamp,sys_user(),current_timestamp);
INSERT INTO tbl_l_facility VALUES ('TREATMENT', 'Inpatient Drug & Alcohol Treatment', 'Substance Abuse Treatment Facility', '24', 'INSTITUT',sys_user(),current_timestamp,sys_user(),current_timestamp);
INSERT INTO tbl_l_facility VALUES ('FAM_FRIEND', 'Stay with family/friends, not on lease', 'Living with relatives/friends', '105', 'HOMELESS',sys_user(),current_timestamp,sys_user(),current_timestamp);
INSERT INTO tbl_l_facility VALUES ('STREETS', 'Street, car or other public place', 'Streets', '104', 'HOMELESS',sys_user(),current_timestamp,sys_user(),current_timestamp);
INSERT INTO tbl_l_facility VALUES ('SUPP-OWN', 'Supportive Housing (own)', 'Supportive Housing', '', 'HOUSED',sys_user(),current_timestamp,sys_user(),current_timestamp);
INSERT INTO tbl_l_facility VALUES ('SUPP-OTH', 'Supportive Housing (not own)', 'Supportive Housing', '', 'HOUSED',sys_user(),current_timestamp,sys_user(),current_timestamp);
INSERT INTO tbl_l_facility VALUES ('TRANS-OWN', 'Transitional Housing (own)', 'Transitional Housing', '207', 'TRANSITION',sys_user(),current_timestamp,sys_user(),current_timestamp);
INSERT INTO tbl_l_facility VALUES ('TRANS-OTH', 'Transitional Housing (not own)', 'Transitional Housing', '207', 'TRANSITION',sys_user(),current_timestamp,sys_user(),current_timestamp);
INSERT INTO tbl_l_facility VALUES ('UNKNOWN', 'Unknown', 'UNKNOWN', '', 'UNKNOWN',sys_user(),current_timestamp,sys_user(),current_timestamp);
INSERT INTO tbl_l_facility VALUES ('NURSING', 'Skilled Nursing Facility', 'Assisted Living', '211', 'INSTITUT',sys_user(),current_timestamp,sys_user(),current_timestamp);
INSERT INTO tbl_l_facility VALUES ('SLAMMER', 'Incarcerated (Jail or Prison)', 'Jail', NULL, 'INSTITUT',sys_user(),current_timestamp,sys_user(),current_timestamp);
INSERT INTO tbl_l_facility VALUES ('HOMELESS', 'Mix of Shelter & Street (use sparingly)', NULL, NULL, 'HOMELESS',sys_user(),current_timestamp,sys_user(),current_timestamp);
INSERT INTO tbl_l_facility VALUES ('SHEL-VOUCH', 'Emergency Shelter (Voucher/Motel)', NULL, NULL, 'HOMELESS',sys_user(),current_timestamp,sys_user(),current_timestamp);
INSERT INTO tbl_l_facility VALUES ('VIOLENCE', 'Domestic Violence Situation', NULL, '208', 'UNKNOWN',sys_user(),current_timestamp,sys_user(),current_timestamp);

CREATE VIEW l_facility AS (SELECT * FROM tbl_l_facility WHERE NOT is_deleted);

