CREATE TABLE tbl_lock (
	lock_code		VARCHAR(10) PRIMARY KEY CHECK (lock_code ~ '^1(0|1|2)H[0-9]{2}$'), -- Added '2' as additional option for second set of lock_code parameters, see db_mod.rel-1-34n_db.tweak_tbl_lock.sql
	combination_1	VARCHAR(8) NOT NULL CHECK (combination_1 ~ '^[0-9]{2}-[0-9]{2}-[0-9]{2}$'),
	combination_2	VARCHAR(8) NOT NULL CHECK (combination_2 ~ '^[0-9]{2}-[0-9]{2}-[0-9]{2}$'),
	combination_3	VARCHAR(8) NOT NULL CHECK (combination_3 ~ '^[0-9]{2}-[0-9]{2}-[0-9]{2}$'),
	combination_4	VARCHAR(8) NOT NULL CHECK (combination_4 ~ '^[0-9]{2}-[0-9]{2}-[0-9]{2}$'),
	combination_5	VARCHAR(8) NOT NULL CHECK (combination_5 ~ '^[0-9]{2}-[0-9]{2}-[0-9]{2}$'),
	dial_code		VARCHAR(4) NOT NULL CHECK (dial_code ~ '^[A-Z]-[0-9]{1,2}$'),
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

CREATE VIEW lock AS SELECT * FROM tbl_lock WHERE NOT is_deleted;
/* sample data
--Locker number','Lock serial #','Combination 1','Combination 2','Combination 3','Combination 4 ','Combination 5','Dial Code',923,923);
INSERT INTO tbl_lock (lock_code,combination_1,combination_2,combination_3,combination_4,combination_5,dial_code,added_by,changed_by) 
	VALUES ('15H21','24-18-10','37-11-03','23-32-48','16-31-42','08-24-34','V-3',923,923);

*/
