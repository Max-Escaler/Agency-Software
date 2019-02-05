/*

--use this version once the code has been updated to use per-object storage method

CREATE TABLE tbl_engine_config (
	val_name		VARCHAR(10),
	engine_object		NAME,
	changed_at		TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
	value			TEXT NOT NULL,

	CONSTRAINT tbl_engine_config_pkey PRIMARY KEY (val_name,engine_object)

);
*/

CREATE TABLE tbl_engine_config (
        val_name                VARCHAR(10) PRIMARY KEY,
        changed_at              TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
        value                   TEXT NOT NULL
);

