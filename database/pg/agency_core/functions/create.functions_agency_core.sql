/* 
 * Here are a series of be_null functions, to test any data
 * type for NULL or '', and return true for either.
 * FIXME: these could easily be sql functions instead of plpgsql
 */

CREATE OR REPLACE FUNCTION be_null( x int ) RETURNS boolean AS $$

     SELECT $1 IS NULL;

$$ LANGUAGE sql IMMUTABLE;

CREATE OR REPLACE FUNCTION be_null( x smallint ) RETURNS boolean AS $$

     SELECT $1 IS NULL;

$$ LANGUAGE sql IMMUTABLE;

CREATE OR REPLACE FUNCTION be_null( x float ) RETURNS BOOLEAN AS $$

      SELECT $1 IS NULL;

$$ LANGUAGE sql IMMUTABLE;

CREATE OR REPLACE FUNCTION be_null( x interval ) RETURNS BOOLEAN AS $$

      SELECT $1 IS NULL;

$$ LANGUAGE sql IMMUTABLE;

CREATE OR REPLACE FUNCTION be_null( x text ) RETURNS boolean AS $$

     SELECT $1 IS NULL OR $1 = '';

$$ LANGUAGE sql IMMUTABLE;

CREATE OR REPLACE FUNCTION be_null( x date ) RETURNS boolean AS $$

    SELECT $1 IS NULL;

$$ LANGUAGE sql IMMUTABLE;

CREATE OR REPLACE FUNCTION be_null( x boolean ) RETURNS boolean AS $$

    SELECT $1 IS NULL;

$$ LANGUAGE sql IMMUTABLE;

CREATE OR REPLACE FUNCTION be_null( x timestamp ) RETURNS boolean AS $$

    SELECT $1 IS NULL;

$$ LANGUAGE sql IMMUTABLE;

CREATE OR REPLACE FUNCTION be_null( x varchar[] ) RETURNS BOOLEAN AS $$

      SELECT $1 IS NULL;

$$ LANGUAGE sql IMMUTABLE;

CREATE OR REPLACE FUNCTION be_null( x integer[] ) RETURNS BOOLEAN AS $$

      SELECT $1 IS NULL;

$$ LANGUAGE sql IMMUTABLE;

CREATE OR REPLACE FUNCTION be_null( x point ) RETURNS BOOLEAN AS $$

      SELECT $1 IS NULL;

$$ LANGUAGE sql IMMUTABLE;

/*
 * These are some convenience functions.
 */

CREATE OR REPLACE FUNCTION null_or( test boolean ) RETURNS boolean AS $$

     SELECT CASE WHEN $1 THEN TRUE ELSE NULL END;

$$ LANGUAGE sql IMMUTABLE;

CREATE OR REPLACE FUNCTION XOR( boolean, boolean) RETURNS boolean as $$
	SELECT ( $1 and not $2) or ( not $1 and $2);

$$ LANGUAGE sql IMMUTABLE;

CREATE OR REPLACE FUNCTION link( url text, label text ) RETURNS text AS $$

     SELECT '<a href="' || $1 || '">' || $2 || '</a>';

$$ language sql IMMUTABLE;


CREATE OR REPLACE FUNCTION bold( input text ) RETURNS TEXT AS $$
BEGIN
     RETURN '<b>' || input || '</b>';
END;$$ LANGUAGE plpgsql IMMUTABLE;     


--------------------------------------------
--
--         Generic Procedural Functions
--
--------------------------------------------

CREATE OR REPLACE FUNCTION bool_f(a boolean) RETURNS text AS $$
BEGIN
     RETURN CASE a WHEN TRUE THEN 'Y' WHEN FALSE THEN 'N' ELSE NULL END;
END; $$ LANGUAGE plpgsql IMMUTABLE;

-------------------------------------
--
--     staff functions
--
-------------------------------------

/*
 * Staff assignment functions depend on a main object,
 * and so are version-specific
 */

CREATE OR REPLACE FUNCTION staff_name( sid int4 ) RETURNS text AS $$
DECLARE
     staff_name     text;
BEGIN
     SELECT INTO staff_name TRIM(name_first) || ' ' || TRIM(name_last) FROM staff WHERE staff_id=sid;
     RETURN staff_name;
END; $$ LANGUAGE plpgsql STABLE;

CREATE OR REPLACE FUNCTION staff_name( sid text ) RETURNS text AS $$
DECLARE
     staff_name     text;
BEGIN
     SELECT INTO staff_name TRIM(name_first) || ' ' || TRIM(name_last) FROM staff WHERE staff_id=sid;
     RETURN staff_name;
END; $$ LANGUAGE plpgsql STABLE;

/* Metadata functions, moving from PHP Code */

CREATE OR REPLACE FUNCTION primary_key( tabname varchar ) RETURNS text AS $$
/* If key should happen to have two+ columns, will be returned comma separated */
DECLARE
	pkey	text;
BEGIN
	SELECT INTO pkey 
		SUBSTRING(pg_catalog.pg_get_indexdef(i.indexrelid) FROM 'USING btree ' || E'\\' || '((.*)' || E'\\' || ')')
	FROM pg_catalog.pg_class c, pg_catalog.pg_class c2, pg_catalog.pg_index i
	WHERE c.relname = tabname AND c.oid = i.indrelid AND i.indexrelid = c2.oid AND i.indisprimary;
	return pkey;
END; $$LANGUAGE plpgsql STABLE;

/* Table log function, but doesn't need superuser */

CREATE OR REPLACE FUNCTION enable_table_logging(varchar,varchar) RETURNS boolean AS $$
	if {[info exists 1]} {
		set TABLE $1
	} else {
		elog ERROR "no table passed to enable_table_logging()"
		return false
	}
	if {[info exists 2] && ($2 != "")} {
		set TRIGGER_TYPES $2
	} else {
		set TRIGGER_TYPES "UPDATE OR INSERT OR DELETE"
	}
	set trigger_exec [ list "SELECT * INTO ${TABLE}_log FROM ${TABLE} LIMIT 0;" \
		"ALTER TABLE ${TABLE}_log ADD COLUMN trigger_mode VARCHAR(10);" \
		"ALTER TABLE ${TABLE}_log ADD COLUMN trigger_tuple VARCHAR(5);" \
		"ALTER TABLE ${TABLE}_log ADD COLUMN trigger_changed TIMESTAMP;" \
		"ALTER TABLE ${TABLE}_log ADD COLUMN trigger_id BIGINT;" \
		"CREATE SEQUENCE ${TABLE}_log_id; " \
		"SELECT SETVAL('${TABLE}_log_id', 1, FALSE); " \
		"ALTER TABLE ${TABLE}_log ALTER COLUMN trigger_id SET DEFAULT NEXTVAL('${TABLE}_log_id'); " \
		"CREATE TRIGGER ${TABLE}_log_chg  \ 
			AFTER ${TRIGGER_TYPES} ON ${TABLE} \
			FOR EACH ROW EXECUTE PROCEDURE table_log();" \
		"CREATE RULE ${TABLE}_log_nodelete AS \
			ON DELETE TO ${TABLE}_log DO INSTEAD NOTHING;" \
		"CREATE RULE ${TABLE}_log_noupdate AS \
			ON UPDATE TO ${TABLE}_log DO INSTEAD NOTHING; " ]
	foreach x  $trigger_exec {
		spi_exec "$x"
	}
	spi_exec -array C "SELECT primary_key('$TABLE') as pkey" {
    	spi_exec "CREATE INDEX index_${TABLE}_log_$C(pkey) ON ${TABLE}_log ( $C(pkey) );"
	}
	return true	

$$ LANGUAGE pltcl;

CREATE OR REPLACE FUNCTION enable_table_logging_all() RETURNS boolean AS $$

	#never enable table logging for these tables (security or performance reasons):
	set never_enable_string "'tbl_user_option','tbl_staff_password','tbl_engine_config'"

	#log on UPDATE/DELETE ONLY tables, otherwise, default is INSERT/UPDATE/DELETE
	set update_delete_only [ list tbl_bed tbl_entry tbl_user_login tbl_alert ]

	set query  "SELECT c.relname AS table FROM pg_catalog.pg_class c 
	LEFT JOIN pg_catalog.pg_user u ON u.usesysid = c.relowner
	LEFT JOIN pg_catalog.pg_namespace n ON n.oid = c.relnamespace
	WHERE c.relkind IN ('r','')
	AND n.nspname NOT IN ('pg_catalog', 'pg_toast')
	AND pg_catalog.pg_table_is_visible(c.oid)
	AND (c.relname ~ '^tbl_' OR c.relname ~ '^l_') AND c.relname NOT IN ( ${never_enable_string} )
	AND (c.relname NOT IN ( SELECT * FROM table_log_enabled_tables ))
    AND (c.relname = 'tbl_log' OR c.relname !~ '_log$')
	ORDER BY 1"

	spi_exec -array C $query {
		if { [lsearch -exact $update_delete_only $C(table)] != -1 } {
			set action_string "UPDATE OR DELETE"
		} else {
			set action_string ""
		}
		spi_exec "SELECT enable_table_logging('$C(table)','$action_string');"
	}
	return true
$$ LANGUAGE pltcl;

CREATE OR REPLACE FUNCTION system_live_at() RETURNS timestamp(0) AS $$

	SELECT message::timestamp(0) FROM system_log WHERE event_type='SYSTEM_LIVE'
	-- Should only be 1, but just in case
	ORDER BY added_at DESC LIMIT 1;
$$ LANGUAGE sql IMMUTABLE;


