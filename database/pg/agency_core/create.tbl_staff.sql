CREATE TABLE tbl_staff (
	staff_id				SERIAL PRIMARY KEY,
	username				VARCHAR(21) NOT NULL, --UNIQUE NOT NULL,
	username_unix			VARCHAR(21),
	staff_email				VARCHAR(60),
	kc_staff_id				INTEGER,
	name_last				VARCHAR(40) NOT NULL,
	name_first				VARCHAR(30) NOT NULL,
	name_first_legal			VARCHAR(30),
	is_active				BOOLEAN  NOT NULL   ,
	login_allowed			BOOLEAN NOT NULL DEFAULT TRUE,
	pgp_key_public			VARCHAR(1024),
	--fixme:  gender foreign key needs to be added after gender lookup created.
	--gender_code				VARCHAR(10) NOT NULL     REFERENCES tbl_l_gender (gender_code),
	gender_code				VARCHAR(10) NOT NULL,
	notes					TEXT,
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

/*
	CONSTRAINT no_redundant_username_date 
		CHECK ( (staff_email IS NULL OR ( (staff_email <> username) AND (staff_email<>username_unix) ))
				AND
			  (username_unix IS NULL OR username_unix <> username)
	)
*/
);

CREATE OR REPLACE FUNCTION add_user_option_record() RETURNS trigger AS $$
DECLARE
	staff_number int;
	x int;
BEGIN
	staff_number := new.staff_id;
	SELECT INTO x staff_id FROM tbl_user_option WHERE staff_id=staff_number;
	IF x IS NULL THEN 
		INSERT INTO tbl_user_option 
			(
			staff_id,
			added_by,
			changed_by,
			sys_log)
		VALUES
			(staff_number,new.added_by,new.changed_by,'Added by add_user_option_record');
	END IF;
	RETURN NEW;
END; $$ LANGUAGE plpgsql;

CREATE TRIGGER tbl_staff_insert
	AFTER INSERT ON tbl_staff FOR EACH ROW
	EXECUTE PROCEDURE add_user_option_record();

CREATE INDEX index_tbl_staff_is_active ON tbl_staff (is_active) WHERE NOT is_deleted;
CREATE INDEX index_tbl_staff_name_first ON tbl_staff (name_first) WHERE NOT is_deleted;
CREATE INDEX index_tbl_staff_name_last ON tbl_staff (name_last) WHERE NOT is_deleted;
CREATE INDEX index_tbl_staff_login_allowed ON tbl_staff ( login_allowed ) WHERE NOT is_deleted;
CREATE INDEX index_tbl_staff_login_allowed_is_active ON tbl_staff (login_allowed,is_active) WHERE NOT is_deleted;

CREATE UNIQUE INDEX tbl_staff_username_key ON tbl_staff ( username ) WHERE NOT is_deleted AND is_active;
CREATE UNIQUE INDEX tbl_staff_kc_staff_id_key ON tbl_staff ( kc_staff_id ) WHERE NOT is_deleted;
