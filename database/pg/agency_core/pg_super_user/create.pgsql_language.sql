/* plpgsql */
SELECT oid FROM pg_language WHERE lanname = 'plpgsql';
SELECT oid FROM pg_proc WHERE proname = 'plpgsql_call_handler' AND prorettype = (SELECT oid FROM pg_type WHERE typname = 'language_handler') AND pronargs = 0;
CREATE FUNCTION "plpgsql_call_handler" () RETURNS language_handler AS '$libdir/plpgsql' LANGUAGE C;
CREATE TRUSTED LANGUAGE "plpgsql" HANDLER "plpgsql_call_handler";

