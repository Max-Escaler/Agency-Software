CREATE TABLE tbl_news (
	news_id			SERIAL PRIMARY KEY,
	posted_at			TIMESTAMP(0) NOT NULL DEFAULT CURRENT_TIMESTAMP,
	subject			VARCHAR(90) NOT NULL,
	news_text			TEXT NOT NULL,
	news_priority_code	VARCHAR(10) NOT NULL REFERENCES tbl_l_news_priority ( news_priority_code ) DEFAULT 'NORMAL',
	--system fields
	added_by			INTEGER NOT NULL REFERENCES tbl_staff (staff_id),
	added_at			TIMESTAMP(0)     NOT NULL DEFAULT CURRENT_TIMESTAMP,
	changed_by			INTEGER NOT NULL  REFERENCES tbl_staff (staff_id),
	changed_at			TIMESTAMP(0)     NOT NULL DEFAULT CURRENT_TIMESTAMP,
	is_deleted			BOOLEAN NOT NULL DEFAULT FALSE,
	deleted_at			TIMESTAMP(0),
	deleted_by			INTEGER REFERENCES tbl_staff(staff_id),
	deleted_comment		TEXT,
	sys_log			TEXT
);

CREATE VIEW news AS SELECT * FROM tbl_news WHERE NOT is_deleted;

CREATE INDEX index_tbl_news_posted_at ON tbl_news ( posted_at );

INSERT INTO tbl_news (posted_at,subject,news_text,added_by,changed_by) VALUES (current_timestamp,'AGENCY Installed','An installation of AGENCY has been set up on this system.',sys_user(),sys_user());
