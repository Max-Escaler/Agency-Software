/*
 * This script will create the database user and the database
 *
 * It must be run by the Postgres superuser (usually "postgres")
 *
 * An install script might bypass this file
 */

CREATE
	USER fff
	PASSWORD 'fff'
	NOCREATEDB NOCREATEUSER;

CREATE
	DATABASE fff
	OWNER fff;

  \c fff

