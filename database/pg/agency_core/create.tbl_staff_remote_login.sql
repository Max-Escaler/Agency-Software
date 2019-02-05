CREATE TABLE tbl_staff_remote_login (
	staff_remote_login_id		SERIAL PRIMARY KEY,
	staff_id			INTEGER NOT NULL REFERENCES tbl_staff ( staff_id ),
	staff_remote_login_date		DATE NOT NULL DEFAULT CURRENT_DATE,
	staff_remote_login_date_end	DATE CHECK (staff_remote_login_date_end >= staff_remote_login_date),
	access_time			TIME NOT NULL DEFAULT '00:00:00.000000',
	access_time_end			TIME NOT NULL DEFAULT '23:59:59.999999',
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

CREATE VIEW staff_remote_login AS SELECT * FROM tbl_staff_remote_login WHERE NOT is_deleted;

CREATE VIEW staff_remote_login_current AS 
	SELECT * FROM staff_remote_login WHERE staff_remote_login_date <= CURRENT_DATE
		AND COALESCE(staff_remote_login_date_end,CURRENT_DATE) >= CURRENT_DATE;

CREATE VIEW staff_remote_login_now AS
	SELECT * FROM staff_remote_login_current WHERE CURRENT_TIME BETWEEN access_time AND access_time_end;

CREATE INDEX index_tbl_staff_remote_login_staff_id ON tbl_staff_remote_login ( staff_id );

CREATE INDEX index_tbl_staff_remote_login_staff_id_dates ON tbl_staff_remote_login ( staff_id,staff_remote_login_date,staff_remote_login_date_end );

CREATE INDEX index_tbl_staff_remote_login_dates ON tbl_staff_remote_login ( staff_remote_login_date,staff_remote_login_date_end );

CREATE INDEX index_tbl_staff_remote_login_dates_times ON tbl_staff_remote_login ( staff_remote_login_date,staff_remote_login_date_end,access_time,access_time_end );

CREATE INDEX index_tbl_staff_remote_login_access_times ON tbl_staff_remote_login ( access_time,access_time_end );
