CREATE TABLE tbl_l_state (
    state_code      CHAR(2) PRIMARY KEY,
    description VARCHAR(60) NOT NULL UNIQUE,
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

INSERT INTO tbl_l_state ( description, state_code,added_by,added_at,changed_by,changed_at ) VALUES (INITCAP('ALABAMA'),'AL',sys_user(),current_timestamp,sys_user(),current_timestamp);
INSERT INTO tbl_l_state ( description, state_code,added_by,added_at,changed_by,changed_at ) VALUES (INITCAP('ALASKA'),'AK',sys_user(),current_timestamp,sys_user(),current_timestamp);
INSERT INTO tbl_l_state ( description, state_code,added_by,added_at,changed_by,changed_at ) VALUES (INITCAP('AMERICAN SAMOA'),'AS',sys_user(),current_timestamp,sys_user(),current_timestamp);
INSERT INTO tbl_l_state ( description, state_code,added_by,added_at,changed_by,changed_at ) VALUES (INITCAP('ARIZONA'),'AZ',sys_user(),current_timestamp,sys_user(),current_timestamp);
INSERT INTO tbl_l_state ( description, state_code,added_by,added_at,changed_by,changed_at ) VALUES (INITCAP('ARKANSAS'),'AR',sys_user(),current_timestamp,sys_user(),current_timestamp);
INSERT INTO tbl_l_state ( description, state_code,added_by,added_at,changed_by,changed_at ) VALUES (INITCAP('CALIFORNIA'),'CA',sys_user(),current_timestamp,sys_user(),current_timestamp);
INSERT INTO tbl_l_state ( description, state_code,added_by,added_at,changed_by,changed_at ) VALUES (INITCAP('COLORADO'),'CO',sys_user(),current_timestamp,sys_user(),current_timestamp);
INSERT INTO tbl_l_state ( description, state_code,added_by,added_at,changed_by,changed_at ) VALUES (INITCAP('CONNECTICUT'),'CT',sys_user(),current_timestamp,sys_user(),current_timestamp);
INSERT INTO tbl_l_state ( description, state_code,added_by,added_at,changed_by,changed_at ) VALUES (INITCAP('DELAWARE'),'DE',sys_user(),current_timestamp,sys_user(),current_timestamp);
INSERT INTO tbl_l_state ( description, state_code,added_by,added_at,changed_by,changed_at ) VALUES (INITCAP('DISTRICT OF COLUMBIA'),'DC',sys_user(),current_timestamp,sys_user(),current_timestamp);
INSERT INTO tbl_l_state ( description, state_code,added_by,added_at,changed_by,changed_at ) VALUES (INITCAP('FEDERATED STATES OF MICRONESIA'),'FM',sys_user(),current_timestamp,sys_user(),current_timestamp);
INSERT INTO tbl_l_state ( description, state_code,added_by,added_at,changed_by,changed_at ) VALUES (INITCAP('FLORIDA'),'FL',sys_user(),current_timestamp,sys_user(),current_timestamp);
INSERT INTO tbl_l_state ( description, state_code,added_by,added_at,changed_by,changed_at ) VALUES (INITCAP('GEORGIA'),'GA',sys_user(),current_timestamp,sys_user(),current_timestamp);
INSERT INTO tbl_l_state ( description, state_code,added_by,added_at,changed_by,changed_at ) VALUES (INITCAP('GUAM'),'GU',sys_user(),current_timestamp,sys_user(),current_timestamp);
INSERT INTO tbl_l_state ( description, state_code,added_by,added_at,changed_by,changed_at ) VALUES (INITCAP('HAWAII'),'HI',sys_user(),current_timestamp,sys_user(),current_timestamp);
INSERT INTO tbl_l_state ( description, state_code,added_by,added_at,changed_by,changed_at ) VALUES (INITCAP('IDAHO'),'ID',sys_user(),current_timestamp,sys_user(),current_timestamp);
INSERT INTO tbl_l_state ( description, state_code,added_by,added_at,changed_by,changed_at ) VALUES (INITCAP('ILLINOIS'),'IL',sys_user(),current_timestamp,sys_user(),current_timestamp);
INSERT INTO tbl_l_state ( description, state_code,added_by,added_at,changed_by,changed_at ) VALUES (INITCAP('INDIANA'),'IN',sys_user(),current_timestamp,sys_user(),current_timestamp);
INSERT INTO tbl_l_state ( description, state_code,added_by,added_at,changed_by,changed_at ) VALUES (INITCAP('IOWA'),'IA',sys_user(),current_timestamp,sys_user(),current_timestamp);
INSERT INTO tbl_l_state ( description, state_code,added_by,added_at,changed_by,changed_at ) VALUES (INITCAP('KANSAS'),'KS',sys_user(),current_timestamp,sys_user(),current_timestamp);
INSERT INTO tbl_l_state ( description, state_code,added_by,added_at,changed_by,changed_at ) VALUES (INITCAP('KENTUCKY'),'KY',sys_user(),current_timestamp,sys_user(),current_timestamp);
INSERT INTO tbl_l_state ( description, state_code,added_by,added_at,changed_by,changed_at ) VALUES (INITCAP('LOUISIANA'),'LA',sys_user(),current_timestamp,sys_user(),current_timestamp);
INSERT INTO tbl_l_state ( description, state_code,added_by,added_at,changed_by,changed_at ) VALUES (INITCAP('MAINE'),'ME',sys_user(),current_timestamp,sys_user(),current_timestamp);
INSERT INTO tbl_l_state ( description, state_code,added_by,added_at,changed_by,changed_at ) VALUES (INITCAP('MARSHALL ISLANDS'),'MH',sys_user(),current_timestamp,sys_user(),current_timestamp);
INSERT INTO tbl_l_state ( description, state_code,added_by,added_at,changed_by,changed_at ) VALUES (INITCAP('MARYLAND'),'MD',sys_user(),current_timestamp,sys_user(),current_timestamp);
INSERT INTO tbl_l_state ( description, state_code,added_by,added_at,changed_by,changed_at ) VALUES (INITCAP('MASSACHUSETTS'),'MA',sys_user(),current_timestamp,sys_user(),current_timestamp);
INSERT INTO tbl_l_state ( description, state_code,added_by,added_at,changed_by,changed_at ) VALUES (INITCAP('MICHIGAN'),'MI',sys_user(),current_timestamp,sys_user(),current_timestamp);
INSERT INTO tbl_l_state ( description, state_code,added_by,added_at,changed_by,changed_at ) VALUES (INITCAP('MINNESOTA'),'MN',sys_user(),current_timestamp,sys_user(),current_timestamp);
INSERT INTO tbl_l_state ( description, state_code,added_by,added_at,changed_by,changed_at ) VALUES (INITCAP('MISSISSIPPI'),'MS',sys_user(),current_timestamp,sys_user(),current_timestamp);
INSERT INTO tbl_l_state ( description, state_code,added_by,added_at,changed_by,changed_at ) VALUES (INITCAP('MISSOURI'),'MO',sys_user(),current_timestamp,sys_user(),current_timestamp);
INSERT INTO tbl_l_state ( description, state_code,added_by,added_at,changed_by,changed_at ) VALUES (INITCAP('MONTANA'),'MT',sys_user(),current_timestamp,sys_user(),current_timestamp);
INSERT INTO tbl_l_state ( description, state_code,added_by,added_at,changed_by,changed_at ) VALUES (INITCAP('NEBRASKA'),'NE',sys_user(),current_timestamp,sys_user(),current_timestamp);
INSERT INTO tbl_l_state ( description, state_code,added_by,added_at,changed_by,changed_at ) VALUES (INITCAP('NEVADA'),'NV',sys_user(),current_timestamp,sys_user(),current_timestamp);
INSERT INTO tbl_l_state ( description, state_code,added_by,added_at,changed_by,changed_at ) VALUES (INITCAP('NEW HAMPSHIRE'),'NH',sys_user(),current_timestamp,sys_user(),current_timestamp);
INSERT INTO tbl_l_state ( description, state_code,added_by,added_at,changed_by,changed_at ) VALUES (INITCAP('NEW JERSEY'),'NJ',sys_user(),current_timestamp,sys_user(),current_timestamp);
INSERT INTO tbl_l_state ( description, state_code,added_by,added_at,changed_by,changed_at ) VALUES (INITCAP('NEW MEXICO'),'NM',sys_user(),current_timestamp,sys_user(),current_timestamp);
INSERT INTO tbl_l_state ( description, state_code,added_by,added_at,changed_by,changed_at ) VALUES (INITCAP('NEW YORK'),'NY',sys_user(),current_timestamp,sys_user(),current_timestamp);
INSERT INTO tbl_l_state ( description, state_code,added_by,added_at,changed_by,changed_at ) VALUES (INITCAP('NORTH CAROLINA'),'NC',sys_user(),current_timestamp,sys_user(),current_timestamp);
INSERT INTO tbl_l_state ( description, state_code,added_by,added_at,changed_by,changed_at ) VALUES (INITCAP('NORTH DAKOTA'),'ND',sys_user(),current_timestamp,sys_user(),current_timestamp);
INSERT INTO tbl_l_state ( description, state_code,added_by,added_at,changed_by,changed_at ) VALUES (INITCAP('NORTHERN MARIANA ISLANDS'),'MP',sys_user(),current_timestamp,sys_user(),current_timestamp);
INSERT INTO tbl_l_state ( description, state_code,added_by,added_at,changed_by,changed_at ) VALUES (INITCAP('OHIO'),'OH',sys_user(),current_timestamp,sys_user(),current_timestamp);
INSERT INTO tbl_l_state ( description, state_code,added_by,added_at,changed_by,changed_at ) VALUES (INITCAP('OKLAHOMA'),'OK',sys_user(),current_timestamp,sys_user(),current_timestamp);
INSERT INTO tbl_l_state ( description, state_code,added_by,added_at,changed_by,changed_at ) VALUES (INITCAP('OREGON'),'OR',sys_user(),current_timestamp,sys_user(),current_timestamp);
INSERT INTO tbl_l_state ( description, state_code,added_by,added_at,changed_by,changed_at ) VALUES (INITCAP('PALAU'),'PW',sys_user(),current_timestamp,sys_user(),current_timestamp);
INSERT INTO tbl_l_state ( description, state_code,added_by,added_at,changed_by,changed_at ) VALUES (INITCAP('PENNSYLVANIA'),'PA',sys_user(),current_timestamp,sys_user(),current_timestamp);
INSERT INTO tbl_l_state ( description, state_code,added_by,added_at,changed_by,changed_at ) VALUES (INITCAP('PUERTO RICO'),'PR',sys_user(),current_timestamp,sys_user(),current_timestamp);
INSERT INTO tbl_l_state ( description, state_code,added_by,added_at,changed_by,changed_at ) VALUES (INITCAP('RHODE ISLAND'),'RI',sys_user(),current_timestamp,sys_user(),current_timestamp);
INSERT INTO tbl_l_state ( description, state_code,added_by,added_at,changed_by,changed_at ) VALUES (INITCAP('SOUTH CAROLINA'),'SC',sys_user(),current_timestamp,sys_user(),current_timestamp);
INSERT INTO tbl_l_state ( description, state_code,added_by,added_at,changed_by,changed_at ) VALUES (INITCAP('SOUTH DAKOTA'),'SD',sys_user(),current_timestamp,sys_user(),current_timestamp);
INSERT INTO tbl_l_state ( description, state_code,added_by,added_at,changed_by,changed_at ) VALUES (INITCAP('TENNESSEE'),'TN',sys_user(),current_timestamp,sys_user(),current_timestamp);
INSERT INTO tbl_l_state ( description, state_code,added_by,added_at,changed_by,changed_at ) VALUES (INITCAP('TEXAS'),'TX',sys_user(),current_timestamp,sys_user(),current_timestamp);
INSERT INTO tbl_l_state ( description, state_code,added_by,added_at,changed_by,changed_at ) VALUES (INITCAP('UTAH'),'UT',sys_user(),current_timestamp,sys_user(),current_timestamp);
INSERT INTO tbl_l_state ( description, state_code,added_by,added_at,changed_by,changed_at ) VALUES (INITCAP('VERMONT'),'VT',sys_user(),current_timestamp,sys_user(),current_timestamp);
INSERT INTO tbl_l_state ( description, state_code,added_by,added_at,changed_by,changed_at ) VALUES (INITCAP('VIRGIN ISLANDS'),'VI',sys_user(),current_timestamp,sys_user(),current_timestamp);
INSERT INTO tbl_l_state ( description, state_code,added_by,added_at,changed_by,changed_at ) VALUES (INITCAP('VIRGINIA'),'VA',sys_user(),current_timestamp,sys_user(),current_timestamp);
INSERT INTO tbl_l_state ( description, state_code,added_by,added_at,changed_by,changed_at ) VALUES (INITCAP('WASHINGTON'),'WA',sys_user(),current_timestamp,sys_user(),current_timestamp);
INSERT INTO tbl_l_state ( description, state_code,added_by,added_at,changed_by,changed_at ) VALUES (INITCAP('WEST VIRGINIA'),'WV',sys_user(),current_timestamp,sys_user(),current_timestamp);
INSERT INTO tbl_l_state ( description, state_code,added_by,added_at,changed_by,changed_at ) VALUES (INITCAP('WISCONSIN'),'WI',sys_user(),current_timestamp,sys_user(),current_timestamp);
INSERT INTO tbl_l_state ( description, state_code,added_by,added_at,changed_by,changed_at ) VALUES (INITCAP('WYOMING'),'WY',sys_user(),current_timestamp,sys_user(),current_timestamp);

CREATE VIEW l_state AS (SELECT * FROM tbl_l_state WHERE NOT is_deleted);

