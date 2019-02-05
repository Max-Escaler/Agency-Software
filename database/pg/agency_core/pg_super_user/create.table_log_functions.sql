/* table logging */
/*
These functions are needed to run table log.
Since they are in C, this code needs to be run
as the postgres user.
*/

CREATE FUNCTION "table_log" ()
    RETURNS opaque
    AS '$libdir/table_log', 'table_log' LANGUAGE C;
CREATE FUNCTION "table_log_restore_table" (VARCHAR, VARCHAR, CHAR, CHAR, CHAR, TIMESTAMP, CHAR, INT, INT)
    RETURNS VARCHAR
    AS '$libdir/table_log', 'table_log_restore_table' LANGUAGE C;
CREATE FUNCTION "table_log_restore_table" (VARCHAR, VARCHAR, CHAR, CHAR, CHAR, TIMESTAMP, CHAR, INT)
    RETURNS VARCHAR
    AS '$libdir/table_log', 'table_log_restore_table' LANGUAGE C;
CREATE FUNCTION "table_log_restore_table" (VARCHAR, VARCHAR, CHAR, CHAR, CHAR, TIMESTAMP, CHAR)
    RETURNS VARCHAR
    AS '$libdir/table_log', 'table_log_restore_table' LANGUAGE C;
CREATE FUNCTION "table_log_restore_table" (VARCHAR, VARCHAR, CHAR, CHAR, CHAR, TIMESTAMP)
    RETURNS VARCHAR
    AS '$libdir/table_log', 'table_log_restore_table' LANGUAGE C;

