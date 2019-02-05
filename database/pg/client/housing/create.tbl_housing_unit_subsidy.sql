CREATE TABLE tbl_housing_unit_subsidy (
	housing_unit_subsidy_id		SERIAL PRIMARY KEY,
	housing_project_code    	VARCHAR(10) NOT NULL REFERENCES tbl_l_housing_project (housing_project_code),
	housing_unit_code			VARCHAR(10) NOT NULL REFERENCES tbl_housing_unit (housing_unit_code),
	housing_unit_subsidy_date 	DATE NOT NULL,
	housing_unit_subsidy_date_end DATE,
	unit_subsidy_amount		NUMERIC(6,2) NOT NULL,
	fund_type_code			VARCHAR(10) REFERENCES tbl_l_fund_type (fund_type_code),
	--system fields
	added_by 				INTEGER NOT NULL REFERENCES tbl_staff (staff_id),
	added_at 				TIMESTAMP(0) NOT NULL DEFAULT current_timestamp,
	changed_by 				INTEGER NOT NULL REFERENCES tbl_staff (staff_id),
	changed_at 				TIMESTAMP(0) NOT NULL DEFAULT current_timestamp,
	is_deleted 				BOOLEAN DEFAULT false,
	deleted_at  			TIMESTAMP(0),
	deleted_by  			INTEGER REFERENCES tbl_staff (staff_id),
	deleted_comment 			TEXT,
	sys_log 				TEXT
);

CREATE OR REPLACE VIEW housing_unit_subsidy AS
SELECT * FROM tbl_housing_unit_subsidy WHERE NOT is_deleted;
