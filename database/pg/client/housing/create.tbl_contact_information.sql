CREATE TABLE tbl_contact_information (
	contact_information_id		SERIAL PRIMARY KEY,
	client_id				INTEGER NOT NULL REFERENCES tbl_client (client_id),
	contact_information_type_code	VARCHAR(10) NOT NULL REFERENCES tbl_l_contact_information_type (contact_information_type_code) DEFAULT 'EMERGENCY',
	contact_information_date	DATE NOT NULL,
	contact_information_date_end	DATE,
	relation				VARCHAR(40),
	name					VARCHAR(40),
	phone_1				VARCHAR(14) NOT NULL CHECK (phone_1 ~ '\\([0-9]{3}\\) [0-9]{3}-[0-9]{4}$'),
	phone_2				VARCHAR(14) CHECK ((phone_2 IS NULL) or phone_2 ~ '\\([0-9]{3}\\) [0-9]{3}-[0-9]{4}$'),
	address_1				VARCHAR(70),
	address_2				VARCHAR(70),
	city					VARCHAR(30),
	state					CHAR(2),
	zipcode				VARCHAR(5),
	email					VARCHAR(70),
	comments				TEXT,
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

CREATE OR REPLACE VIEW contact_information AS
SELECT *,
	COALESCE(CASE WHEN relation IS NOT NULL THEN relation||'\n' END,'')
	||COALESCE(CASE WHEN name IS NOT NULL THEN name||'\n' END,'')
	||COALESCE(CASE WHEN phone_1 IS NOT NULL THEN phone_1||'\n' END,'')
	||COALESCE(CASE WHEN phone_2 IS NOT NULL THEN phone_2||'\n' END,'')
	||COALESCE(CASE WHEN address_1 IS NOT NULL THEN address_1||'\n' END,'')
	||COALESCE(CASE WHEN address_2 IS NOT NULL THEN address_2||'\n' END,'')
	||COALESCE(CASE WHEN city IS NOT NULL THEN city||' ' END,'')
	||COALESCE(CASE WHEN state IS NOT NULL THEN state||' ' END,'')
	||COALESCE(zipcode,'')
	||COALESCE(CASE WHEN email IS NOT NULL THEN '\n'||email END,'')
	AS contact_summary
FROM tbl_contact_information WHERE NOT is_deleted;



CREATE INDEX index_tbl_contact_information_client_id_date ON tbl_contact_information ( client_id,contact_information_date );
CREATE INDEX index_tbl_contact_information_contact_information_date ON tbl_contact_information ( contact_information_date );

