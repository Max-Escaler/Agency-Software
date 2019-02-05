CREATE TABLE tbl_staff_driver_authorization (
	staff_driver_authorization_id		SERIAL PRIMARY KEY,
	staff_id					INTEGER NOT NULL REFERENCES tbl_staff ( staff_id ),
	staff_driver_authorization_date		DATE_PAST NOT NULL,
	drivers_license_on_file			BOOLEAN NOT NULL DEFAULT FALSE 
								CHECK ((drivers_license_on_file AND drivers_license_expiration_date IS NOT NULL) 
								OR (NOT drivers_license_on_file AND drivers_license_expiration_date IS NULL)),
	drivers_license_expiration_date	DATE,
	insurance_on_file				BOOLEAN NOT NULL DEFAULT FALSE 
								CHECK ((insurance_on_file AND insurance_expiration_date IS NOT NULL) 
								OR (NOT insurance_on_file AND insurance_expiration_date IS NULL)),
	insurance_expiration_date		DATE,
	abstract_of_driving_record_on_file	BOOLEAN,
	personal_vehicle_use			BOOLEAN NOT NULL DEFAULT FALSE,
	staff_driver_authorization_date_end	DATE,
	--system fields
	added_by					INTEGER NOT NULL REFERENCES tbl_staff (staff_id),
	added_at					TIMESTAMP(0)     NOT NULL DEFAULT CURRENT_TIMESTAMP,
	changed_by					INTEGER NOT NULL  REFERENCES tbl_staff (staff_id),
	changed_at					TIMESTAMP(0)     NOT NULL DEFAULT CURRENT_TIMESTAMP,
	is_deleted					BOOLEAN NOT NULL DEFAULT FALSE,
	deleted_at					TIMESTAMP(0) CHECK ((NOT is_deleted AND deleted_at IS NULL)
											OR (is_deleted AND deleted_at IS NOT NULL)),
	deleted_by					INTEGER REFERENCES tbl_staff(staff_id)
							  	CHECK ((NOT is_deleted AND deleted_by IS NULL) 
											OR (is_deleted AND deleted_by IS NOT NULL)),
	deleted_comment				TEXT,
	sys_log					TEXT

);
