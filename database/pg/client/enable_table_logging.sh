#!/bin/sh

DEF_TRIG="UPDATE OR INSERT OR DELETE"
if ! [ "$1" ] ; then
	echo
	echo "Usage: $0 table_name [trigger type]"
	echo
	echo "(enables table logging in Postgres)"
	echo "(default trigger is $DEF_TRIG)"
 	exit;
fi

if [ "$2" ] ; then
	TRIGG=$2
else
	TRIGG=$DEF_TRIG
fi

TABLE=$1
cat <<DONE
-- drop old trigger
DROP TRIGGER ${TABLE}_log_chg ON ${TABLE};

-- create the table without data from table
--DROP TABLE ${TABLE}_log;
SELECT * INTO ${TABLE}_log FROM ${TABLE} LIMIT 0;
ALTER TABLE ${TABLE}_log ADD COLUMN trigger_mode VARCHAR(10);
ALTER TABLE ${TABLE}_log ADD COLUMN trigger_tuple VARCHAR(5);
ALTER TABLE ${TABLE}_log ADD COLUMN trigger_changed TIMESTAMP;
ALTER TABLE ${TABLE}_log ADD COLUMN trigger_id BIGINT;
CREATE SEQUENCE ${TABLE}_log_id;
SELECT SETVAL('${TABLE}_log_id', 1, FALSE);
ALTER TABLE ${TABLE}_log ALTER COLUMN trigger_id SET DEFAULT NEXTVAL('${TABLE}_log_id');

-- create trigger
CREATE TRIGGER ${TABLE}_log_chg 
--	AFTER UPDATE OR INSERT OR DELETE ON ${TABLE} 
	AFTER $TRIGG ON ${TABLE} 
	FOR EACH ROW EXECUTE PROCEDURE table_log();
-- Disable updates & deletes of log table
CREATE RULE ${TABLE}_log_nodelete AS
	ON DELETE TO ${TABLE}_log DO INSTEAD NOTHING;
CREATE RULE ${TABLE}_log_noupdate AS
	ON UPDATE TO ${TABLE}_log DO INSTEAD NOTHING;
DONE
exit
-- The rest is all example stuff:
/*
Function only needs to be run once, and it's been done.
Leaving for reference.

-- drop old function
DROP FUNCTION table_log ();

-- create function
CREATE FUNCTION table_log ()
    RETURNS opaque
    AS 'MODULE_PATHNAME' LANGUAGE C;
CREATE FUNCTION "table_log_restore_table" (VARCHAR, VARCHAR, CHAR, CHAR, CHAR, TIMESTAMP, CHAR, INT, INT)
    RETURNS VARCHAR
    AS 'MODULE_PATHNAME', 'table_log_restore_table' LANGUAGE C;
CREATE FUNCTION "table_log_restore_table" (VARCHAR, VARCHAR, CHAR, CHAR, CHAR, TIMESTAMP, CHAR, INT)
    RETURNS VARCHAR
    AS 'MODULE_PATHNAME', 'table_log_restore_table' LANGUAGE C;
CREATE FUNCTION "table_log_restore_table" (VARCHAR, VARCHAR, CHAR, CHAR, CHAR, TIMESTAMP, CHAR)
    RETURNS VARCHAR
    AS 'MODULE_PATHNAME', 'table_log_restore_table' LANGUAGE C;
CREATE FUNCTION "table_log_restore_table" (VARCHAR, VARCHAR, CHAR, CHAR, CHAR, TIMESTAMP)
    RETURNS VARCHAR
    AS 'MODULE_PATHNAME', 'table_log_restore_table' LANGUAGE C;
End adding functions
*/

/*
-- test trigger
INSERT INTO ${TABLE} VALUES (1, 'name');
SELECT * FROM ${TABLE};
SELECT * FROM ${TABLE}_log;
UPDATE ${TABLE} SET name='other name' WHERE id=1;
SELECT * FROM ${TABLE};
SELECT * FROM ${TABLE}_log;
*/

/*
-- This code is just a test/example of restoring
-- create restore table
SELECT table_log_restore_table('${TABLE}', 'id', '${TABLE}_log', '${TABLE}_log_id', '${TABLE}_recover', NOW());
SELECT * FROM ${TABLE}_recover;
*/
