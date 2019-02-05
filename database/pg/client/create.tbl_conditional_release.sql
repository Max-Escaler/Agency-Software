CREATE TABLE tbl_conditional_release (
	conditional_release_id			SERIAL PRIMARY KEY,
	client_id				INTEGER NOT NULL REFERENCES tbl_client (client_id),
	conditional_release_type_code		VARCHAR(10) NOT NULL REFERENCES tbl_l_conditional_release_type (conditional_release_type_code),
	reference_number			VARCHAR(30),
	conditional_release_date		DATE NOT NULL,
	conditional_release_date_accuracy_code	VARCHAR(10) NOT NULL REFERENCES tbl_l_accuracy (accuracy_code),
	conditional_release_date_end		DATE CHECK (conditional_release_date_end >= conditional_release_date),
	conditional_release_date_end_accuracy_code VARCHAR(10) NOT NULL REFERENCES tbl_l_accuracy (accuracy_code),
	county_code				VARCHAR(10) NOT NULL REFERENCES tbl_l_washington_county (washington_county_code),
	paperwork_in_chart_code			VARCHAR(10) NOT NULL REFERENCES tbl_l_yes_no (yes_no_code),
	client_rights_in_chart_code		VARCHAR(10) NOT NULL REFERENCES tbl_l_yes_no (yes_no_code),
	continuation_of_previous_code		VARCHAR(10) NOT NULL REFERENCES tbl_l_yes_no (yes_no_code),
	previous_reference_number		VARCHAR(30),
	requirement_residence			TEXT,
	compliance_residence_code		VARCHAR(10) NOT NULL REFERENCES tbl_l_yes_no (yes_no_code),
	compliance_plan_residence		TEXT,
	requirement_appointment			TEXT,
	compliance_appointment_code		VARCHAR(10) NOT NULL REFERENCES tbl_l_yes_no (yes_no_code),
	compliance_plan_appointment		TEXT,
	compliance_medication_code		VARCHAR(10) NOT NULL REFERENCES tbl_l_yes_no (yes_no_code),
	compliance_plan_medication		TEXT,
	compliance_substance_code		VARCHAR(10) NOT NULL REFERENCES tbl_l_yes_no (yes_no_code),
	compliance_plan_substance		TEXT,
	compliance_threat_code			VARCHAR(10) NOT NULL REFERENCES tbl_l_yes_no (yes_no_code),
	compliance_plan_threat			TEXT,
	compliance_firearm_code			VARCHAR(10) NOT NULL REFERENCES tbl_l_yes_no (yes_no_code),
	compliance_plan_firearm			TEXT,
	requirement_other			TEXT,
	compliance_other_code			VARCHAR(10) NOT NULL REFERENCES tbl_l_yes_no (yes_no_code),
	compliance_plan_other			TEXT,
	transition_plan				TEXT,
	comment					TEXT,
	requirements_met_code			VARCHAR(10) NOT NULL REFERENCES tbl_l_yes_no (yes_no_code),
	voluntary_treatment_transition_code	VARCHAR(10) NOT NULL REFERENCES tbl_l_yes_no (yes_no_code),
	conditional_release_extension_code	VARCHAR(10) NOT NULL REFERENCES tbl_l_conditional_release_extension (conditional_release_extension_code),
	client_redetained_code			VARCHAR(10) NOT NULL REFERENCES tbl_l_yes_no (yes_no_code),


	--system fields
	added_by			INTEGER NOT NULL REFERENCES tbl_staff (staff_id),
	added_at			TIMESTAMP(0)     NOT NULL DEFAULT CURRENT_TIMESTAMP,
	changed_by			INTEGER NOT NULL  REFERENCES tbl_staff (staff_id),
	changed_at			TIMESTAMP(0)     NOT NULL DEFAULT CURRENT_TIMESTAMP,
	is_deleted			BOOLEAN NOT NULL DEFAULT FALSE,
	deleted_at			TIMESTAMP(0) CHECK ((NOT is_deleted AND deleted_at IS NULL) OR (is_deleted AND deleted_at IS NOT NULL)),
	deleted_by			INTEGER REFERENCES tbl_staff(staff_id)
					CHECK ((NOT is_deleted AND deleted_by IS NULL) OR (is_deleted AND deleted_by IS NOT NULL)),
	deleted_comment		TEXT,
	sys_log			TEXT
);

CREATE VIEW conditional_release AS SELECT * FROM tbl_conditional_release WHERE NOT is_deleted;
CREATE VIEW conditional_release_current AS
SELECT * FROM conditional_release 
WHERE conditional_release_date <= CURRENT_DATE 
	AND COALESCE(conditional_release_date_end, CURRENT_DATE) > CURRENT_DATE;

CREATE INDEX index_tbl_conditional_release_client_id ON tbl_conditional_release (client_id);
CREATE INDEX index_tbl_conditional_release_dates
	ON tbl_conditional_release (conditional_release_date, conditional_release_date_end);
CREATE INDEX index_tbl_conditional_release_dates_client_id
	ON tbl_conditional_release (client_id, conditional_release_date, conditional_release_date_end);
