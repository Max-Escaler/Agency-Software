CREATE TABLE db_list (
	db_name		NAME PRIMARY KEY,
	description		TEXT NOT NULL,
	is_test_db		BOOLEAN NOT NULL,
	primary_url		VARCHAR(150),
	CONSTRAINT db_list_name_description_unique UNIQUE (db_name,description)
);
