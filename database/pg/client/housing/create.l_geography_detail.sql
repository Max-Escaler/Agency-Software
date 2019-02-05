CREATE TABLE tbl_l_geography_detail (
	geography_detail_code	INTEGER PRIMARY KEY,
	description				TEXT UNIQUE,
	category				VARCHAR(30),
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

INSERT INTO tbl_l_geography_detail VALUES (200, 'Ballard', 'Seattle Neighborhood',sys_user(),current_timestamp,sys_user(),current_timestamp);
INSERT INTO tbl_l_geography_detail VALUES (205, 'Capitol Hill', 'Seattle Neighborhood',sys_user(),current_timestamp,sys_user(),current_timestamp);
INSERT INTO tbl_l_geography_detail VALUES (210, 'Central Seattle', 'Seattle Neighborhood',sys_user(),current_timestamp,sys_user(),current_timestamp);
INSERT INTO tbl_l_geography_detail VALUES (215, 'Delridge', 'Seattle Neighborhood',sys_user(),current_timestamp,sys_user(),current_timestamp);
INSERT INTO tbl_l_geography_detail VALUES (220, 'Downtown', 'Seattle Neighborhood',sys_user(),current_timestamp,sys_user(),current_timestamp);
INSERT INTO tbl_l_geography_detail VALUES (225, 'Duwamish', 'Seattle Neighborhood',sys_user(),current_timestamp,sys_user(),current_timestamp);
INSERT INTO tbl_l_geography_detail VALUES (230, 'Lake Union', 'Seattle Neighborhood',sys_user(),current_timestamp,sys_user(),current_timestamp);
INSERT INTO tbl_l_geography_detail VALUES (240, 'Queen Anne', 'Seattle Neighborhood',sys_user(),current_timestamp,sys_user(),current_timestamp);
INSERT INTO tbl_l_geography_detail VALUES (245, 'Magnolia', 'Seattle Neighborhood',sys_user(),current_timestamp,sys_user(),current_timestamp);
INSERT INTO tbl_l_geography_detail VALUES (250, 'Cascade', 'Seattle Neighborhood',sys_user(),current_timestamp,sys_user(),current_timestamp);
INSERT INTO tbl_l_geography_detail VALUES (255, 'University District', 'Seattle Neighborhood',sys_user(),current_timestamp,sys_user(),current_timestamp);
INSERT INTO tbl_l_geography_detail VALUES (260, 'Wallingford', 'Seattle Neighborhood',sys_user(),current_timestamp,sys_user(),current_timestamp);
INSERT INTO tbl_l_geography_detail VALUES (265, 'Fremont', 'Seattle Neighborhood',sys_user(),current_timestamp,sys_user(),current_timestamp);
INSERT INTO tbl_l_geography_detail VALUES (270, 'Roosevelt', 'Seattle Neighborhood',sys_user(),current_timestamp,sys_user(),current_timestamp);
INSERT INTO tbl_l_geography_detail VALUES (275, 'Greenwood', 'Seattle Neighborhood',sys_user(),current_timestamp,sys_user(),current_timestamp);
INSERT INTO tbl_l_geography_detail VALUES (280, 'Sandpoint', 'Seattle Neighborhood',sys_user(),current_timestamp,sys_user(),current_timestamp);
INSERT INTO tbl_l_geography_detail VALUES (285, 'Eastlake', 'Seattle Neighborhood',sys_user(),current_timestamp,sys_user(),current_timestamp);
INSERT INTO tbl_l_geography_detail VALUES (290, 'Montlake', 'Seattle Neighborhood',sys_user(),current_timestamp,sys_user(),current_timestamp);
INSERT INTO tbl_l_geography_detail VALUES (295, 'International District', 'Seattle Neighborhood',sys_user(),current_timestamp,sys_user(),current_timestamp);
INSERT INTO tbl_l_geography_detail VALUES (300, 'Beacon Hill', 'Seattle Neighborhood',sys_user(),current_timestamp,sys_user(),current_timestamp);
INSERT INTO tbl_l_geography_detail VALUES (310, 'Rainier Valley', 'Seattle Neighborhood',sys_user(),current_timestamp,sys_user(),current_timestamp);
INSERT INTO tbl_l_geography_detail VALUES (315, 'Columbia City', 'Seattle Neighborhood',sys_user(),current_timestamp,sys_user(),current_timestamp);
INSERT INTO tbl_l_geography_detail VALUES (320, 'Pioneer Square', 'Seattle Neighborhood',sys_user(),current_timestamp,sys_user(),current_timestamp);
INSERT INTO tbl_l_geography_detail VALUES (325, 'Sodo', 'Seattle Neighborhood',sys_user(),current_timestamp,sys_user(),current_timestamp);
INSERT INTO tbl_l_geography_detail VALUES (330, 'Georgetown', 'Seattle Neighborhood',sys_user(),current_timestamp,sys_user(),current_timestamp);
INSERT INTO tbl_l_geography_detail VALUES (335, 'Downtown-Pike/Pine', 'Seattle Neighborhood',sys_user(),current_timestamp,sys_user(),current_timestamp);
INSERT INTO tbl_l_geography_detail VALUES (340, 'Belltown', 'Seattle Neighborhood',sys_user(),current_timestamp,sys_user(),current_timestamp);
INSERT INTO tbl_l_geography_detail VALUES (345, 'Denny Regrade', 'Seattle Neighborhood',sys_user(),current_timestamp,sys_user(),current_timestamp);
INSERT INTO tbl_l_geography_detail VALUES (350, 'Downtown Business District', 'Seattle Neighborhood',sys_user(),current_timestamp,sys_user(),current_timestamp);
INSERT INTO tbl_l_geography_detail VALUES (355, 'Downtown Waterfront', 'Seattle Neighborhood',sys_user(),current_timestamp,sys_user(),current_timestamp);
INSERT INTO tbl_l_geography_detail VALUES (360, 'Downtown-area unknown/unspecified', 'Seattle Neighborhood',sys_user(),current_timestamp,sys_user(),current_timestamp);
INSERT INTO tbl_l_geography_detail VALUES (400, 'North Seattle', 'Seattle Neighborhood',sys_user(),current_timestamp,sys_user(),current_timestamp);
INSERT INTO tbl_l_geography_detail VALUES (405, 'NE Seattle', 'Seattle Neighborhood',sys_user(),current_timestamp,sys_user(),current_timestamp);
INSERT INTO tbl_l_geography_detail VALUES (410, 'NW Seattle', 'Seattle Neighborhood',sys_user(),current_timestamp,sys_user(),current_timestamp);
INSERT INTO tbl_l_geography_detail VALUES (415, 'SE Seattle', 'Seattle Neighborhood',sys_user(),current_timestamp,sys_user(),current_timestamp);
INSERT INTO tbl_l_geography_detail VALUES (420, 'SW Seattle', 'Seattle Neighborhood',sys_user(),current_timestamp,sys_user(),current_timestamp);
INSERT INTO tbl_l_geography_detail VALUES (500, 'Kent', 'SW County',sys_user(),current_timestamp,sys_user(),current_timestamp);
INSERT INTO tbl_l_geography_detail VALUES (505, 'Renton', 'SW County',sys_user(),current_timestamp,sys_user(),current_timestamp);
INSERT INTO tbl_l_geography_detail VALUES (510, 'Federal Way', 'SW County',sys_user(),current_timestamp,sys_user(),current_timestamp);
INSERT INTO tbl_l_geography_detail VALUES (515, 'Burien', 'SW County',sys_user(),current_timestamp,sys_user(),current_timestamp);
INSERT INTO tbl_l_geography_detail VALUES (520, 'White Center', 'SW County',sys_user(),current_timestamp,sys_user(),current_timestamp);
INSERT INTO tbl_l_geography_detail VALUES (525, 'Highline', 'SW County',sys_user(),current_timestamp,sys_user(),current_timestamp);
INSERT INTO tbl_l_geography_detail VALUES (530, 'Normandy Park', 'SW County',sys_user(),current_timestamp,sys_user(),current_timestamp);
INSERT INTO tbl_l_geography_detail VALUES (535, 'Sea-Tac', 'SW County',sys_user(),current_timestamp,sys_user(),current_timestamp);
INSERT INTO tbl_l_geography_detail VALUES (540, 'Auburn', 'SW County',sys_user(),current_timestamp,sys_user(),current_timestamp);
INSERT INTO tbl_l_geography_detail VALUES (545, 'Enumclaw', 'SW County',sys_user(),current_timestamp,sys_user(),current_timestamp);
INSERT INTO tbl_l_geography_detail VALUES (550, 'SW King County-No Municipality', 'SW County',sys_user(),current_timestamp,sys_user(),current_timestamp);
INSERT INTO tbl_l_geography_detail VALUES (600, 'Shoreline', 'King County',sys_user(),current_timestamp,sys_user(),current_timestamp);
INSERT INTO tbl_l_geography_detail VALUES (605, 'Kenmore', 'King County',sys_user(),current_timestamp,sys_user(),current_timestamp);
INSERT INTO tbl_l_geography_detail VALUES (610, 'Matthews Beach', 'Seattle Neighborhood',sys_user(),current_timestamp,sys_user(),current_timestamp);
INSERT INTO tbl_l_geography_detail VALUES (615, 'Edmonds', 'Snohomish County',sys_user(),current_timestamp,sys_user(),current_timestamp);
INSERT INTO tbl_l_geography_detail VALUES (620, 'Mountlake Terrace', 'Snohomish County',sys_user(),current_timestamp,sys_user(),current_timestamp);
INSERT INTO tbl_l_geography_detail VALUES (625, 'North County-area unknown/unspecified', 'King County',sys_user(),current_timestamp,sys_user(),current_timestamp);

CREATE VIEW l_geography_detail AS (SELECT * FROM tbl_l_geography_detail WHERE NOT is_deleted);

