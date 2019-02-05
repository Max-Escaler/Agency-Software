CREATE OR REPLACE FUNCTION jail_get_ba_number(INT) RETURNS INT AS $$
DECLARE
	ba_num INT;
BEGIN
	SELECT INTO ba_num ba_number FROM tbl_jail WHERE jail_id = $1;
	RETURN ba_num;
END;$$ LANGUAGE plpgsql;

CREATE TABLE tbl_jail_charge (
	jail_charge_id	SERIAL PRIMARY KEY,
	jail_id		INTEGER NOT NULL REFERENCES tbl_jail ( jail_id ),
	ba_number		INTEGER NOT NULL,
	cause_number	INTEGER,
	court			VARCHAR(60),
	rcw			VARCHAR(30),
	release_reason	VARCHAR(60),
	bail			VARCHAR(30),
	charge		VARCHAR(30),
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

	CONSTRAINT matching_ba_number CHECK (ba_number = jail_get_ba_number(jail_id))
);

CREATE UNIQUE INDEX index_tbl_jail_charge_unique ON tbl_jail_charge (jail_id,ba_number,cause_number,court,rcw,release_reason,bail,charge);
