CREATE TABLE tbl_work_order (
	work_order_id		SERIAL PRIMARY KEY,
	title			VARCHAR NOT NULL,
	description		TEXT NOT NULL,
	assigned_to		INTEGER REFERENCES tbl_staff (staff_id),-- NOT NULL,
	cc_list			INTEGER[], --REFERENCES tbl_staff(staff_id),
	work_order_status_code	VARCHAR NOT NULL REFERENCES tbl_l_work_order_status (work_order_status_code) DEFAULT 'PENDING',
	work_order_type_code	VARCHAR NOT NULL REFERENCES tbl_l_work_order_type (work_order_type_code),
	work_order_category_code	VARCHAR NOT NULL REFERENCES tbl_l_work_order_category (work_order_category_code),
	work_order_resolution_code	VARCHAR NOT NULL REFERENCES tbl_l_work_order_resolution (work_order_resolution_code),
	duplicate_of_id	INTEGER REFERENCES tbl_work_order (work_order_id),
	priority		INTEGER NOT NULL DEFAULT 3 CHECK (priority BETWEEN 1 and 5),
	blocked_by_ids	INTEGER[], --REFERENCES tbl_work_order (work_order_id),
	blocker_of_ids	INTEGER[], --REFERENCES tbl_work_order (work_order_id),
	agency_project_code	VARCHAR(10) NOT NULL REFERENCES tbl_l_agency_project (agency_project_code),
	work_order_category_code	VARCHAR NOT NULL REFERENCES tbl_l_work_order_category (work_order_category_code),
	housing_project_code	VARCHAR REFERENCES tbl_l_housing_project (housing_project_code),
	housing_unit_code	VARCHAR REFERENCES tbl_housing_unit (housing_unit_code),
	target_date		DATE,
	next_action_date	DATE,
	next_action_comment	TEXT,
	comment_status	TEXT,
	closed_date		DATE,
	hours_estimated		REAL,
	hours_actual		REAL,
	perm_type_list			VARCHAR[], -- REFERENCES tbl_l_permission_type (permission_type_code),
	perm_type_view			VARCHAR[], -- REFERENCES tbl_l_permission_type (permission_type_code),
	perm_type_edit			VARCHAR[], -- REFERENCES tbl_l_permission_type (permission_type_code),
	comment_submitter	TEXT,
	comment_assignee	TEXT,
	comment_assigner	TEXT,
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

COMMENT ON COLUMN tbl_work_order.priority IS '1 = highest priority, 5 = lowest';

CREATE INDEX index_tbl_work_order_assigned_to ON tbl_work_order ( assigned_to );
CREATE INDEX index_tbl_work_order_added_by ON tbl_work_order ( added_by );
CREATE INDEX index_tbl_work_order_added_at ON tbl_work_order ( added_at );
CREATE INDEX index_tbl_work_order_target_date ON tbl_work_order ( target_date );
CREATE INDEX index_tbl_work_order_priority ON tbl_work_order ( priority );
CREATE INDEX index_tbl_work_order_agency_project_code ON tbl_work_order ( agency_project_code );
CREATE INDEX index_tbl_work_order_housing_project_code ON tbl_work_order ( housing_project_code );
CREATE INDEX index_tbl_work_order_housing_unit_code ON tbl_work_order ( housing_unit_code );
CREATE INDEX index_tbl_work_order_work_order_category_code ON tbl_work_order ( work_order_category_code );
CREATE INDEX index_tbl_work_order_next_action_date ON tbl_work_order ( next_action_date );

/*
BEGIN;
ALTER TABLE tbl_work_order ADD COLUMN cc_list INTEGER[];
ALTER TABLE tbl_work_order_log ADD COLUMN cc_list INTEGER[];
ALTER TABLE tbl_work_order ADD COLUMN	blocked_by_ids	INTEGER[];
ALTER TABLE tbl_work_order_log ADD COLUMN	blocked_by_ids	INTEGER[];
ALTER TABLE tbl_work_order ADD COLUMN	blocker_of_ids	INTEGER[];
ALTER TABLE tbl_work_order_log ADD COLUMN	blocker_of_ids	INTEGER[];
DROP VIEW work_order;
\i create.view.work_order.sql
*/

/*
BEGIN;
ALTER TABLE tbl_work_order ADD COLUMN work_order_type_code    VARCHAR NOT NULL REFERENCES tbl_l_work_order_type (work_order_type_code);
ALTER TABLE tbl_work_order ADD COLUMN work_order_category_code        VARCHAR NOT NULL REFERENCES tbl_l_work_order_category (work_order_category_code);
ALTER TABLE tbl_work_order ADD COLUMN work_order_resolution_code      VARCHAR NOT NULL REFERENCES tbl_l_work_order_resolution (work_order_resolution_code);
ALTER TABLE tbl_work_order ADD COLUMN duplicate_of_id INTEGER REFERENCES tbl_work_order (work_order_id);
ALTER TABLE tbl_work_order ADD COLUMN next_action_comment     TEXT;
ALTER TABLE tbl_work_order ADD COLUMN comment_status  TEXT;
ALTER TABLE tbl_work_order ADD COLUMN perm_type_list                  VARCHAR[]; -- REFERENCES tbl_l_permission_type (permission_type_code),
ALTER TABLE tbl_work_order ADD COLUMN perm_type_view                  VARCHAR[]; -- REFERENCES tbl_l_permission_type (permission_type_code),
ALTER TABLE tbl_work_order ADD COLUMN perm_type_edit                  VARCHAR[]; -- REFERENCES tbl_l_permission_type (permission_type_code),
ALTER TABLE tbl_work_order ADD COLUMN comment_submitter       TEXT;
ALTER TABLE tbl_work_order ADD COLUMN comment_assignee        TEXT;
ALTER TABLE tbl_work_order ADD COLUMN comment_assigner        TEXT;

ALTER TABLE tbl_work_order_log ADD COLUMN work_order_type_code VARCHAR;
ALTER TABLE tbl_work_order_log ADD COLUMN work_order_category_code        VARCHAR;
ALTER TABLE tbl_work_order_log ADD COLUMN work_order_resolution_code      VARCHAR;
ALTER TABLE tbl_work_order_log ADD COLUMN duplicate_of_id INTEGER;
ALTER TABLE tbl_work_order_log ADD COLUMN next_action_comment     TEXT;
ALTER TABLE tbl_work_order_log ADD COLUMN comment_status  TEXT;
ALTER TABLE tbl_work_order_log ADD COLUMN perm_type_list                  VARCHAR[]; -- REFERENCES tbl_l_permission_type (permission_type_code),
ALTER TABLE tbl_work_order_log ADD COLUMN perm_type_view                  VARCHAR[]; -- REFERENCES tbl_l_permission_type (permission_type_code),
ALTER TABLE tbl_work_order_log ADD COLUMN perm_type_edit                  VARCHAR[]; -- REFERENCES tbl_l_permission_type (permission_type_code),
ALTER TABLE tbl_work_order_log ADD COLUMN comment_submitter       TEXT;
ALTER TABLE tbl_work_order_log ADD COLUMN comment_assignee        TEXT;
ALTER TABLE tbl_work_order_log ADD COLUMN comment_assigner        TEXT;
*/
