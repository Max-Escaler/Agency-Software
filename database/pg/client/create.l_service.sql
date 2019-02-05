CREATE TABLE tbl_l_service (
	service_code 		VARCHAR(10) PRIMARY KEY,
	description 		TEXT NOT NULL UNIQUE,
	is_current			BOOLEAN NOT NULL DEFAULT TRUE,
	cpt_code 			VARCHAR(5),
--	cpt_modifiers		VARCHAR(11),
	used_by_cd 			BOOLEAN NOT NULL DEFAULT FALSE,
	used_by_housing		BOOLEAN NOT NULL DEFAULT FALSE,
	used_by_heet		BOOLEAN NOT NULL DEFAULT FALSE,
	used_by_crp			BOOLEAN NOT NULL DEFAULT FALSE,
	used_by_ir			BOOLEAN NOT NULL DEFAULT FALSE,
	used_by_connections	BOOLEAN NOT NULL DEFAULT FALSE,
	staff_qualification_codes	VARCHAR[],
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

--COMMENT ON COLUMN l_service.cpt_code IS 'Set to "xx3xx" or leave blank if service shouldn\'t/can\'t be reported to King County';
--COMMENT ON COLUMN l_service.cpt_modifiers IS 'Up to 4 modifiers can be included, as a single string, of the form MM or MM:MM, up to MM:MM:MM:MM';

INSERT INTO tbl_l_service VALUES ('680', 'Adult Day TX', true, NULL, false, true, false, false, false, false, NULL,sys_user(),current_timestamp,sys_user(),current_timestamp);
INSERT INTO tbl_l_service VALUES ('CD_ASSESS', 'Assessment/Intake', true, NULL, true, false, false, false, false, false, NULL,sys_user(),current_timestamp,sys_user(),current_timestamp);
INSERT INTO tbl_l_service VALUES ('514', 'Case Specific Interdisciplinary Staffing', true, NULL, false, true, false, false, false, false, NULL,sys_user(),current_timestamp,sys_user(),current_timestamp);
INSERT INTO tbl_l_service VALUES ('650', 'Concurrent Review', true, NULL, false, true, false, false, false, false, NULL,sys_user(),current_timestamp,sys_user(),current_timestamp);
INSERT INTO tbl_l_service VALUES ('260', 'Crisis Services', true, NULL, false, true, false, false, false, false, NULL,sys_user(),current_timestamp,sys_user(),current_timestamp);
INSERT INTO tbl_l_service VALUES ('618', 'Drop In Club', true, NULL, false, true, false, false, false, false, NULL,sys_user(),current_timestamp,sys_user(),current_timestamp);
INSERT INTO tbl_l_service VALUES ('288', 'Family Treatment Services (meeting with client and family)', true, NULL, false, true, false, false, false, false, NULL,sys_user(),current_timestamp,sys_user(),current_timestamp);
INSERT INTO tbl_l_service VALUES ('284', 'General Outreach (HOST & SAGE)', true, NULL, false, true, false, false, false, false, NULL,sys_user(),current_timestamp,sys_user(),current_timestamp);
INSERT INTO tbl_l_service VALUES ('CD_GROUP', 'Group', true, NULL, true, false, false, false, false, false, NULL,sys_user(),current_timestamp,sys_user(),current_timestamp);
INSERT INTO tbl_l_service VALUES ('278', 'Group Treatment Svcs.', true, NULL, false, true, false, false, false, false, NULL,sys_user(),current_timestamp,sys_user(),current_timestamp);
INSERT INTO tbl_l_service VALUES ('115', 'Health Care Visit Non-Emergent', true, NULL, false, true, false, false, false, false, NULL,sys_user(),current_timestamp,sys_user(),current_timestamp);
INSERT INTO tbl_l_service VALUES ('800', 'Housing Assistance', true, NULL, false, true, true, true, true, true, NULL,sys_user(),current_timestamp,sys_user(),current_timestamp);
INSERT INTO tbl_l_service VALUES ('CD_CM', 'Indirect Consultation (Case Management)', true, NULL, true, false, false, false, false, false, NULL,sys_user(),current_timestamp,sys_user(),current_timestamp);
INSERT INTO tbl_l_service VALUES ('CD_IND', 'Individual', true, NULL, true, false, false, false, false, false, NULL,sys_user(),current_timestamp,sys_user(),current_timestamp);
INSERT INTO tbl_l_service VALUES ('276', 'Individual Case Management', true, NULL, false, true, true, true, true, true, NULL,sys_user(),current_timestamp,sys_user(),current_timestamp);
INSERT INTO tbl_l_service VALUES ('264', 'Intake Evaluation', true, NULL, false, true, false, true, true, true, NULL,sys_user(),current_timestamp,sys_user(),current_timestamp);
INSERT INTO tbl_l_service VALUES ('684', 'Interpreter Services - Group', true, NULL, false, true, false, false, false, false, NULL,sys_user(),current_timestamp,sys_user(),current_timestamp);
INSERT INTO tbl_l_service VALUES ('682', 'Interpreter Services - Individual', true, NULL, false, true, false, false, false, false, NULL,sys_user(),current_timestamp,sys_user(),current_timestamp);
INSERT INTO tbl_l_service VALUES ('811', 'Life Skills Training', true, NULL, false, true, false, true, true, true, NULL,sys_user(),current_timestamp,sys_user(),current_timestamp);
INSERT INTO tbl_l_service VALUES ('830', 'Medical Administration', true, NULL, false, true, false, false, false, false, NULL,sys_user(),current_timestamp,sys_user(),current_timestamp);
INSERT INTO tbl_l_service VALUES ('274', 'Medication Management-Group', true, NULL, false, true, false, false, false, false, NULL,sys_user(),current_timestamp,sys_user(),current_timestamp);
INSERT INTO tbl_l_service VALUES ('272', 'Medication Management/Training-Indiv', true, NULL, false, true, false, false, false, false, NULL,sys_user(),current_timestamp,sys_user(),current_timestamp);
INSERT INTO tbl_l_service VALUES ('820', 'Medication Support', true, NULL, false, true, false, false, false, false, NULL,sys_user(),current_timestamp,sys_user(),current_timestamp);
INSERT INTO tbl_l_service VALUES ('282', 'No Show, Case Management', true, NULL, false, true, false, false, false, false, NULL,sys_user(),current_timestamp,sys_user(),current_timestamp);
INSERT INTO tbl_l_service VALUES ('OTHER', 'Other Service (please describe)', true, NULL, false, false, true, true, true, true, NULL,sys_user(),current_timestamp,sys_user(),current_timestamp);
INSERT INTO tbl_l_service VALUES ('277', 'Payee Services', true, NULL, false, true, false, false, false, false, NULL,sys_user(),current_timestamp,sys_user(),current_timestamp);
INSERT INTO tbl_l_service VALUES ('681', 'Prescriber Appointment w/ Interpreter', true, NULL, false, true, false, false, false, false, NULL,sys_user(),current_timestamp,sys_user(),current_timestamp);
INSERT INTO tbl_l_service VALUES ('513', 'Prescriber Consultation (includes psych consult)', true, NULL, false, true, false, false, false, false, NULL,sys_user(),current_timestamp,sys_user(),current_timestamp);
INSERT INTO tbl_l_service VALUES ('900', 'Progress Note Only', true, NULL, false, true, false, true, true, true, NULL,sys_user(),current_timestamp,sys_user(),current_timestamp);
INSERT INTO tbl_l_service VALUES ('622', 'Psychiatrist Evaluation - comprehensive', true, NULL, false, true, false, false, false, false, NULL,sys_user(),current_timestamp,sys_user(),current_timestamp);
INSERT INTO tbl_l_service VALUES ('110', 'Psychiatrist or ARNP, Cancel by CM or Client', true, NULL, false, true, false, false, false, false, NULL,sys_user(),current_timestamp,sys_user(),current_timestamp);
INSERT INTO tbl_l_service VALUES ('100', 'Psychiatrist or ARNP, No Shows', true, NULL, false, true, false, false, false, false, NULL,sys_user(),current_timestamp,sys_user(),current_timestamp);
INSERT INTO tbl_l_service VALUES ('275', 'Psychotherapy, Individual', true, NULL, false, true, false, false, false, false, NULL,sys_user(),current_timestamp,sys_user(),current_timestamp);
INSERT INTO tbl_l_service VALUES ('266', 'Special Population Evaluation', true, NULL, false, true, false, false, false, false, NULL,sys_user(),current_timestamp,sys_user(),current_timestamp);
INSERT INTO tbl_l_service VALUES ('263', 'Stabilization Services', true, NULL, false, true, false, false, false, false, NULL,sys_user(),current_timestamp,sys_user(),current_timestamp);
INSERT INTO tbl_l_service VALUES ('512', 'Travel Time', true, NULL, false, true, false, false, false, false, NULL,sys_user(),current_timestamp,sys_user(),current_timestamp);
INSERT INTO tbl_l_service VALUES ('CD_TPR', 'Treatment Plan Review', true, NULL, true, false, false, false, false, false, NULL,sys_user(),current_timestamp,sys_user(),current_timestamp);
INSERT INTO tbl_l_service VALUES ('CD_UA', 'Urinalysis Sample', true, NULL, true, false, false, false, false, false, NULL,sys_user(),current_timestamp,sys_user(),current_timestamp);
INSERT INTO tbl_l_service VALUES ('810', 'Vocation Support', true, NULL, false, true, false, true, true, true, NULL,sys_user(),current_timestamp,sys_user(),current_timestamp);

INSERT INTO tbl_l_service (service_code,description,cpt_code, used_by_heet,used_by_cd,used_by_connections,used_by_crp,used_by_ir, is_current,added_by,added_at,changed_by,changed_at)
VALUES
    ('656','Elevated Concern Plan Review','H2015', false,true,true,true,true, true,sys_user(),current_timestamp,sys_user(),current_timestamp), 
	('515','Looked for client, didn''t find',NULL, true,true,true,true,true, true,sys_user(),current_timestamp,sys_user(),current_timestamp);

CREATE VIEW l_service AS (SELECT * FROM tbl_l_service WHERE NOT is_deleted);

