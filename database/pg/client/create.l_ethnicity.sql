CREATE TABLE tbl_l_ethnicity (
	ethnicity_code	VARCHAR(10) PRIMARY KEY,
	description		VARCHAR(30) NOT NULL UNIQUE,
	king_cty_code	VARCHAR(10),
	ethnicity_simple_code	VARCHAR(10) REFERENCES tbl_l_ethnicity_simple NOT NULL,
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

INSERT INTO tbl_l_ethnicity VALUES 
	('0', 'Unknown', '999','UNKNOWN',sys_user(),current_timestamp,sys_user(),current_timestamp);
INSERT INTO tbl_l_ethnicity VALUES 
	('1', 'Caucasian', '800','CAUCASIAN',sys_user(),current_timestamp,sys_user(),current_timestamp);
INSERT INTO tbl_l_ethnicity VALUES 
	('10', 'Japanese', '611','ASIAN_PAC',sys_user(),current_timestamp,sys_user(),current_timestamp);
INSERT INTO tbl_l_ethnicity VALUES 
	('11', 'Other Asian', '699','ASIAN_PAC',sys_user(),current_timestamp,sys_user(),current_timestamp);
--INSERT INTO tbl_l_ethnicity VALUES 
--	('12', 'Multi-Racial', '998','MULTI',sys_user(),current_timestamp,sys_user(),current_timestamp);
INSERT INTO tbl_l_ethnicity VALUES 
	('13', 'Other', '799','OTHER',sys_user(),current_timestamp,sys_user(),current_timestamp);
INSERT INTO tbl_l_ethnicity VALUES 
	('2', 'African Am/African Descent', '870','AFRICAN',sys_user(),current_timestamp,sys_user(),current_timestamp);
INSERT INTO tbl_l_ethnicity VALUES 
	('3', 'Latino', '799', 'LATINO',sys_user(),current_timestamp,sys_user(),current_timestamp);
INSERT INTO tbl_l_ethnicity VALUES 
	('4', 'Native American Indian', '597','NATIVE_AM',sys_user(),current_timestamp,sys_user(),current_timestamp);
INSERT INTO tbl_l_ethnicity VALUES 
	('41', 'Alaska Native', '941','NATIVE_AM',sys_user(),current_timestamp,sys_user(),current_timestamp);
INSERT INTO tbl_l_ethnicity VALUES 
	('5', 'Korean', '612','ASIAN_PAC',sys_user(),current_timestamp,sys_user(),current_timestamp);
INSERT INTO tbl_l_ethnicity VALUES 
	('6', 'Pacific Islander', '699','ASIAN_PAC',sys_user(),current_timestamp,sys_user(),current_timestamp);
INSERT INTO tbl_l_ethnicity VALUES 
	('7', 'Filipino', '608','ASIAN_PAC',sys_user(),current_timestamp,sys_user(),current_timestamp);
INSERT INTO tbl_l_ethnicity VALUES 
	('8', 'Vietnam/Cambodia/Laos', '699','ASIAN_PAC',sys_user(),current_timestamp,sys_user(),current_timestamp);
INSERT INTO tbl_l_ethnicity VALUES 
	('9', 'Chinese', '605','ASIAN_PAC',sys_user(),current_timestamp,sys_user(),current_timestamp);
INSERT INTO tbl_l_ethnicity VALUES 
	('14', 'Asian Indian', '600','ASIAN_PAC',sys_user(),current_timestamp,sys_user(),current_timestamp);
INSERT INTO tbl_l_ethnicity VALUES 
	('15', 'Cambodian', '604','ASIAN_PAC',sys_user(),current_timestamp,sys_user(),current_timestamp);
INSERT INTO tbl_l_ethnicity VALUES 
	('16', 'Laotian', '613','ASIAN_PAC',sys_user(),current_timestamp,sys_user(),current_timestamp);
INSERT INTO tbl_l_ethnicity VALUES 
	('17', 'Thai', '618','ASIAN_PAC',sys_user(),current_timestamp,sys_user(),current_timestamp);
INSERT INTO tbl_l_ethnicity VALUES 
	('18', 'Vietnamese', '619','ASIAN_PAC',sys_user(),current_timestamp,sys_user(),current_timestamp);
INSERT INTO tbl_l_ethnicity VALUES 
	('19', 'Hawaiian', '658','ASIAN_PAC',sys_user(),current_timestamp,sys_user(),current_timestamp);
INSERT INTO tbl_l_ethnicity VALUES 
	('20', 'Guamanian', '660','ASIAN_PAC',sys_user(),current_timestamp,sys_user(),current_timestamp);
INSERT INTO tbl_l_ethnicity VALUES 
	('21', 'Samoan', '695','ASIAN_PAC',sys_user(),current_timestamp,sys_user(),current_timestamp);
INSERT INTO tbl_l_ethnicity VALUES 
	('23', 'Eskimo', '935','NATIVE_AM',sys_user(),current_timestamp,sys_user(),current_timestamp);
INSERT INTO tbl_l_ethnicity VALUES 
	('22', 'African', '871','AFRICAN',sys_user(),current_timestamp,sys_user(),current_timestamp);

CREATE VIEW l_ethnicity AS (SELECT * FROM tbl_l_ethnicity WHERE NOT is_deleted);

