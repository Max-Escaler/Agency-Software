CREATE TABLE tbl_user_login (
	user_login_id		SERIAL PRIMARY KEY,
	staff_id			INTEGER NOT NULL REFERENCES tbl_staff ( staff_id ),
	login_at			TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
	ip_address			CIDR NOT NULL,
--system fields - not sure if these fields make sense in this table - JH
	added_by			INTEGER NOT NULL REFERENCES tbl_staff (staff_id) DEFAULT sys_user(),
	added_at			TIMESTAMP(0)     NOT NULL DEFAULT CURRENT_TIMESTAMP,
	changed_by			INTEGER NOT NULL  REFERENCES tbl_staff (staff_id) DEFAULT sys_user(),
	changed_at			TIMESTAMP(0)     NOT NULL DEFAULT CURRENT_TIMESTAMP,
	is_deleted			BOOLEAN NOT NULL DEFAULT FALSE,
	deleted_at			TIMESTAMP(0),
	deleted_by			INTEGER REFERENCES tbl_staff(staff_id),
	deleted_comment		TEXT,
	sys_log			TEXT
);

CREATE RULE rule_tbl_user_login_no_delete AS
	ON DELETE TO tbl_user_login DO INSTEAD NOTHING;

CREATE INDEX tbl_user_login_staff_id ON tbl_user_login ( staff_id );
CREATE INDEX tbl_user_login_login_at ON tbl_user_login ( login_at );
CREATE INDEX tbl_user_login_joint_staff_id_login_at ON tbl_user_login ( staff_id,login_at );

CREATE VIEW user_login AS SELECT * FROM tbl_user_login WHERE NOT is_deleted;

CREATE VIEW user_login_latest AS 
SELECT DISTINCT ON (staff_id) staff_id,login_at,ip_address FROM user_login ORDER BY staff_id,login_at DESC;

