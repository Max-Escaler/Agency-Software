CREATE TABLE tbl_l_washington_county (
	washington_county_code	VARCHAR(10) PRIMARY KEY,
	description 		VARCHAR(60) NOT NULL UNIQUE,
	fips_state_code		INTEGER,
	king_cty_code		VARCHAR(2),
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

CREATE VIEW l_washington_county AS (SELECT * FROM tbl_l_washington_county WHERE NOT is_deleted);

/* this places king county at the top, but keeps it's place in the order as well */
CREATE OR REPLACE VIEW l_washington_county_add AS 
SELECT * FROM l_washington_county WHERE washington_county_code = 'KING'
UNION ALL
SELECT * FROM (SELECT * FROM l_washington_county ORDER BY description) a1;

INSERT INTO tbl_l_washington_county (fips_state_code,description,washington_county_code,king_cty_code,added_by,added_at,changed_by,changed_at) VALUES ('001', 'Adams County', 'ADAMS','01',sys_user(),current_timestamp,sys_user(),current_timestamp);
INSERT INTO tbl_l_washington_county (fips_state_code,description,washington_county_code,king_cty_code,added_by,added_at,changed_by,changed_at) VALUES ('003', 'Asotin County', 'ASOTIN','02',sys_user(),current_timestamp,sys_user(),current_timestamp);
INSERT INTO tbl_l_washington_county (fips_state_code,description,washington_county_code,king_cty_code,added_by,added_at,changed_by,changed_at) VALUES ('005', 'Benton County', 'BENTON','03',sys_user(),current_timestamp,sys_user(),current_timestamp);
INSERT INTO tbl_l_washington_county (fips_state_code,description,washington_county_code,king_cty_code,added_by,added_at,changed_by,changed_at) VALUES ('007', 'Chelan County', 'CHELAN','04',sys_user(),current_timestamp,sys_user(),current_timestamp);
INSERT INTO tbl_l_washington_county (fips_state_code,description,washington_county_code,king_cty_code,added_by,added_at,changed_by,changed_at) VALUES ('009', 'Clallam County', 'CLALLAM','05',sys_user(),current_timestamp,sys_user(),current_timestamp);
INSERT INTO tbl_l_washington_county (fips_state_code,description,washington_county_code,king_cty_code,added_by,added_at,changed_by,changed_at) VALUES ('011', 'Clark County', 'CLARK','06',sys_user(),current_timestamp,sys_user(),current_timestamp);
INSERT INTO tbl_l_washington_county (fips_state_code,description,washington_county_code,king_cty_code,added_by,added_at,changed_by,changed_at) VALUES ('013', 'Columbia County', 'COLUMBIA','07',sys_user(),current_timestamp,sys_user(),current_timestamp);
INSERT INTO tbl_l_washington_county (fips_state_code,description,washington_county_code,king_cty_code,added_by,added_at,changed_by,changed_at) VALUES ('015', 'Cowlitz County', 'COWLITZ','08',sys_user(),current_timestamp,sys_user(),current_timestamp);
INSERT INTO tbl_l_washington_county (fips_state_code,description,washington_county_code,king_cty_code,added_by,added_at,changed_by,changed_at) VALUES ('017', 'Douglas County', 'DOUGLAS','09',sys_user(),current_timestamp,sys_user(),current_timestamp);
INSERT INTO tbl_l_washington_county (fips_state_code,description,washington_county_code,king_cty_code,added_by,added_at,changed_by,changed_at) VALUES ('019', 'Ferry County', 'FERRY','10',sys_user(),current_timestamp,sys_user(),current_timestamp);
INSERT INTO tbl_l_washington_county (fips_state_code,description,washington_county_code,king_cty_code,added_by,added_at,changed_by,changed_at) VALUES ('021', 'Franklin County', 'FRANKLIN','11',sys_user(),current_timestamp,sys_user(),current_timestamp);
INSERT INTO tbl_l_washington_county (fips_state_code,description,washington_county_code,king_cty_code,added_by,added_at,changed_by,changed_at) VALUES ('023', 'Garfield County', 'GARFIELD','12',sys_user(),current_timestamp,sys_user(),current_timestamp);
INSERT INTO tbl_l_washington_county (fips_state_code,description,washington_county_code,king_cty_code,added_by,added_at,changed_by,changed_at) VALUES ('025', 'Grant County', 'GRANT','13',sys_user(),current_timestamp,sys_user(),current_timestamp);
INSERT INTO tbl_l_washington_county (fips_state_code,description,washington_county_code,king_cty_code,added_by,added_at,changed_by,changed_at) VALUES ('027', 'Grays Harbor County', 'GRAYSHRBR','14',sys_user(),current_timestamp,sys_user(),current_timestamp);
INSERT INTO tbl_l_washington_county (fips_state_code,description,washington_county_code,king_cty_code,added_by,added_at,changed_by,changed_at) VALUES ('029', 'Island County', 'ISLAND','15',sys_user(),current_timestamp,sys_user(),current_timestamp);
INSERT INTO tbl_l_washington_county (fips_state_code,description,washington_county_code,king_cty_code,added_by,added_at,changed_by,changed_at) VALUES ('031', 'Jefferson County', 'JEFFERSON','16',sys_user(),current_timestamp,sys_user(),current_timestamp);
INSERT INTO tbl_l_washington_county (fips_state_code,description,washington_county_code,king_cty_code,added_by,added_at,changed_by,changed_at) VALUES ('033', 'King County', 'KING','17',sys_user(),current_timestamp,sys_user(),current_timestamp);
INSERT INTO tbl_l_washington_county (fips_state_code,description,washington_county_code,king_cty_code,added_by,added_at,changed_by,changed_at) VALUES ('035', 'Kitsap County', 'KITSAP','18',sys_user(),current_timestamp,sys_user(),current_timestamp);
INSERT INTO tbl_l_washington_county (fips_state_code,description,washington_county_code,king_cty_code,added_by,added_at,changed_by,changed_at) VALUES ('037', 'Kittitas County', 'KITTITAS','19',sys_user(),current_timestamp,sys_user(),current_timestamp);
INSERT INTO tbl_l_washington_county (fips_state_code,description,washington_county_code,king_cty_code,added_by,added_at,changed_by,changed_at) VALUES ('039', 'Klickitat County', 'KLICKITAT','20',sys_user(),current_timestamp,sys_user(),current_timestamp);
INSERT INTO tbl_l_washington_county (fips_state_code,description,washington_county_code,king_cty_code,added_by,added_at,changed_by,changed_at) VALUES ('041', 'Lewis County', 'LEWIS','21',sys_user(),current_timestamp,sys_user(),current_timestamp);
INSERT INTO tbl_l_washington_county (fips_state_code,description,washington_county_code,king_cty_code,added_by,added_at,changed_by,changed_at) VALUES ('043', 'Lincoln County', 'LINCOLN','22',sys_user(),current_timestamp,sys_user(),current_timestamp);
INSERT INTO tbl_l_washington_county (fips_state_code,description,washington_county_code,king_cty_code,added_by,added_at,changed_by,changed_at) VALUES ('045', 'Mason County', 'MASON','23',sys_user(),current_timestamp,sys_user(),current_timestamp);
INSERT INTO tbl_l_washington_county (fips_state_code,description,washington_county_code,king_cty_code,added_by,added_at,changed_by,changed_at) VALUES ('047', 'Okanogan County', 'OKANOGAN','24',sys_user(),current_timestamp,sys_user(),current_timestamp);
INSERT INTO tbl_l_washington_county (fips_state_code,description,washington_county_code,king_cty_code,added_by,added_at,changed_by,changed_at) VALUES ('049', 'Pacific County', 'PACIFIC','25',sys_user(),current_timestamp,sys_user(),current_timestamp);
INSERT INTO tbl_l_washington_county (fips_state_code,description,washington_county_code,king_cty_code,added_by,added_at,changed_by,changed_at) VALUES ('051', 'Pend Oreille County', 'PNDOREILLE','26',sys_user(),current_timestamp,sys_user(),current_timestamp);
INSERT INTO tbl_l_washington_county (fips_state_code,description,washington_county_code,king_cty_code,added_by,added_at,changed_by,changed_at) VALUES ('053', 'Pierce County', 'PIERCE','27',sys_user(),current_timestamp,sys_user(),current_timestamp);
INSERT INTO tbl_l_washington_county (fips_state_code,description,washington_county_code,king_cty_code,added_by,added_at,changed_by,changed_at) VALUES ('055', 'San Juan County', 'SANJUAN','28',sys_user(),current_timestamp,sys_user(),current_timestamp);
INSERT INTO tbl_l_washington_county (fips_state_code,description,washington_county_code,king_cty_code,added_by,added_at,changed_by,changed_at) VALUES ('057', 'Skagit County', 'SKAGIT','29',sys_user(),current_timestamp,sys_user(),current_timestamp);
INSERT INTO tbl_l_washington_county (fips_state_code,description,washington_county_code,king_cty_code,added_by,added_at,changed_by,changed_at) VALUES ('059', 'Skamania County', 'SKAMANIA','30',sys_user(),current_timestamp,sys_user(),current_timestamp);
INSERT INTO tbl_l_washington_county (fips_state_code,description,washington_county_code,king_cty_code,added_by,added_at,changed_by,changed_at) VALUES ('061', 'Snohomish County', 'SNOHOMISH','31',sys_user(),current_timestamp,sys_user(),current_timestamp);
INSERT INTO tbl_l_washington_county (fips_state_code,description,washington_county_code,king_cty_code,added_by,added_at,changed_by,changed_at) VALUES ('063', 'Spokane County', 'SPOKANE','32',sys_user(),current_timestamp,sys_user(),current_timestamp);
INSERT INTO tbl_l_washington_county (fips_state_code,description,washington_county_code,king_cty_code,added_by,added_at,changed_by,changed_at) VALUES ('065', 'Stevens County', 'STEVENS','33',sys_user(),current_timestamp,sys_user(),current_timestamp);
INSERT INTO tbl_l_washington_county (fips_state_code,description,washington_county_code,king_cty_code,added_by,added_at,changed_by,changed_at) VALUES ('067', 'Thurston County', 'THURSTON','34',sys_user(),current_timestamp,sys_user(),current_timestamp);
INSERT INTO tbl_l_washington_county (fips_state_code,description,washington_county_code,king_cty_code,added_by,added_at,changed_by,changed_at) VALUES ('069', 'Wahkiakum County', 'WAHKIAKUM','35',sys_user(),current_timestamp,sys_user(),current_timestamp);
INSERT INTO tbl_l_washington_county (fips_state_code,description,washington_county_code,king_cty_code,added_by,added_at,changed_by,changed_at) VALUES ('071', 'Walla Walla County', 'WALLAWALLA','36',sys_user(),current_timestamp,sys_user(),current_timestamp);
INSERT INTO tbl_l_washington_county (fips_state_code,description,washington_county_code,king_cty_code,added_by,added_at,changed_by,changed_at) VALUES ('073', 'Whatcom County', 'WHATCOM','37',sys_user(),current_timestamp,sys_user(),current_timestamp);
INSERT INTO tbl_l_washington_county (fips_state_code,description,washington_county_code,king_cty_code,added_by,added_at,changed_by,changed_at) VALUES ('075', 'Whitman County', 'WHITMAN','38',sys_user(),current_timestamp,sys_user(),current_timestamp);
INSERT INTO tbl_l_washington_county (fips_state_code,description,washington_county_code,king_cty_code,added_by,added_at,changed_by,changed_at) VALUES ('077', 'Yakima County', 'YAKIMA','39',sys_user(),current_timestamp,sys_user(),current_timestamp);

INSERT INTO tbl_l_washington_county (fips_state_code,description,washington_county_code,king_cty_code,added_by,added_at,changed_by,changed_at) VALUES (NULL, 'Out of State', 'OUTOFSTATE','90',sys_user(),current_timestamp,sys_user(),current_timestamp);
INSERT INTO tbl_l_washington_county (fips_state_code,description,washington_county_code,king_cty_code,added_by,added_at,changed_by,changed_at) VALUES (NULL, 'Out of County', 'OUTOFCNTY','98',sys_user(),current_timestamp,sys_user(),current_timestamp);
INSERT INTO tbl_l_washington_county (fips_state_code,description,washington_county_code,king_cty_code,added_by,added_at,changed_by,changed_at) VALUES (NULL, 'Unknown', 'UNKNOWN','99',sys_user(),current_timestamp,sys_user(),current_timestamp);

