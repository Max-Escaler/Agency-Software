#!/bin/sh
# the part of AGENCY install that needs to be run as root
# really, it just needs to be done by the postgres superuser
# called from install script
#
# if called by hand, make sure you are in the correct directory
# (agency/database/agency_core/pg_super_user)

echo running as `whoami`
PG_DB=$1
PG_USER=$2
PG_SU_USER_DEFAULT=postgres
PG_SU_SQL_DEFAULT=install2.db.sql
PG_SU_USER=${3:-$PG_SU_USER_DEFAULT}
PG_SU_SQL=${4:-$PG_SU_SQL_DEFAULT}

if  [ "$PG_DB" = "" ] ; then
	echo
	echo "usage $0 database user db super_user"
	echo
	exit
fi
echo You will now be prompted for a password for the $PG_USER database user.
su $PG_SU_USER -c "createuser -SDRP $PG_USER" 
if [ $? = 0 ] ; then
	echo created user $PG_USER
else
	echo "warning... creating user $PG_USER failed... (Maybe it already exists?)"
fi

su $PG_SU_USER -c "createdb -O $PG_USER $PG_DB"
if [ $? = 0 ] ; then
	echo created database $PG_DB
else
	echo "ERROR:  failed to create datbase $PG_DB, owned by $PG_USER"
	exit 1;
fi
echo In directory... `pwd`
su $PG_SU_USER -c "cat $PG_SU_SQL | psql $PG_DB"
if [ $? = 0 ] ; then
	echo executed stage 2 of db super user install
else
	echo ERROR:  failed to execute stage 2 of db super user install
	exit 1
fi

