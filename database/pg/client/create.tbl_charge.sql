CREATE TABLE tbl_charge (
	charge_id			SERIAL PRIMARY KEY,
	client_id			INTEGER NOT NULL REFERENCES tbl_client(client_id),
	effective_date		DATE NOT NULL,
	amount				NUMERIC(8,2) NOT NULL,
	charge_type_code	VARCHAR(10) NOT NULL REFERENCES tbl_l_charge_type (charge_type_code),
	housing_unit_code	VARCHAR(10) REFERENCES tbl_housing_unit ( housing_unit_code ),
	housing_project_code	VARCHAR(10) REFERENCES tbl_l_housing_project (housing_project_code),
	comment				TEXT,
	subsidy_type_code	VARCHAR(10) REFERENCES tbl_l_fund_type ( fund_type_code ),
	is_subsidy			BOOLEAN NOT NULL DEFAULT false,
	period_start		DATE,
	period_end			DATE,
	is_void				BOOLEAN NOT NULL DEFAULT false,
	voided_by			INTEGER REFERENCES tbl_staff (staff_id),
	void_comment		TEXT,
	voided_at			TIMESTAMP(0),
	--system fields
	added_by			INTEGER NOT NULL REFERENCES tbl_staff (staff_id),
	added_at			TIMESTAMP(0)     NOT NULL DEFAULT CURRENT_TIMESTAMP,
	changed_by			INTEGER NOT NULL  REFERENCES tbl_staff (staff_id),
	changed_at			TIMESTAMP(0)     NOT NULL DEFAULT CURRENT_TIMESTAMP,
/* deletes are not allowed. Ever. Ever. Ever.
	is_deleted			BOOLEAN NOT NULL DEFAULT FALSE,
	deleted_at			TIMESTAMP(0),
	deleted_by			INTEGER REFERENCES tbl_staff(staff_id),
	deleted_comment		TEXT,
*/
	sys_log			TEXT

	CONSTRAINT good_charge_voids CHECK ( (is_void IS FALSE AND voided_by IS NULL AND voided_at IS NULL AND void_comment IS NULL) 
						OR 
						(is_void IS TRUE AND voided_by IS NOT NULL AND voided_at IS NOT NULL AND void_comment IS NOT NULL) )
);


CREATE VIEW charge AS SELECT * FROM tbl_charge;

CREATE RULE tbl_charge_delete AS 
	ON DELETE TO tbl_charge
	DO INSTEAD NOTHING;

CREATE TRIGGER tbl_charge_modify
	BEFORE UPDATE ON tbl_charge
	FOR EACH ROW EXECUTE PROCEDURE validate_charge_modify();

/*

CREATE TRIGGER tbl_charge_insert
    AFTER INSERT ON tbl_charge FOR EACH ROW
    EXECUTE PROCEDURE charge_notify_misc();

*/

CREATE INDEX index_tbl_charge_charge_type_code ON tbl_charge(charge_type_code);
CREATE INDEX index_tbl_charge_client_id ON tbl_charge(client_id);
CREATE INDEX index_tbl_charge_effective_date ON tbl_charge(effective_date);
CREATE INDEX index_tbl_charge_is_subsidy ON tbl_charge(is_subsidy);
CREATE INDEX index_tbl_charge_is_void ON tbl_charge(is_void);
CREATE INDEX index_tbl_charge_period_end ON tbl_charge(period_end);
CREATE INDEX index_tbl_charge_period_start ON tbl_charge(period_start);
CREATE INDEX index_tbl_charge_housing_unit_code ON tbl_charge(housing_unit_code);
