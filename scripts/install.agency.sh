#!/bin/bash

PG_USER=agency
PG_DB=agency
PG_HOST=localhost
PG_PORT=5432

APP_DIR=agency
SOURCE_TARBALL=$1
GIT_REPO=git://agency.git.sourceforge.net/gitroot/agency/agency 
AG_CORE=database/pg/agency_core
CONFIG_DB_TEMPLATE=config/agency_config_db.php.template
CONFIG_DB_DEST=config/agency_config_db.php
PG_SU_SQL=install2.db.sql
PG_SU_DIR=pg_super_user
PG_SU_SCRIPT=$WEB_BASE/$APP_DIR/scripts/install.agency.root.sh
#PG_COMMAND="psql -f $PG_SU_SQL"
PG_SU_USER=postgres
LOG_DIR=/var/log/agency
LOG_FILE=/agency_error.log
RPM_REQS="postgresql postgresql-devel postgresql-libs postgresql-server postgresql-pltcl postgresql-table_log postgresql-contrib zziplib zziplib-devel php-pear gcc php-pgsql php-devel mod_ssl pcre-devel make git"
DEB_REQS="libpq-dev postgresql-server-dev-8.4 make apache2 libapache2-mod-php5 php5-cli php5-pgsql php5-dev php-pear libzzip-0-13 libpcre3-dev git-core"
# Fixme: No ubuntu package for table_log

if $(cat /etc/issue | grep -i ubuntu > /dev/null) ; then
	DISTRO=UBUNTU
elif $(cat /etc/issue | grep -i fedora > /dev/null) ; then
	DISTRO=FEDORA
elif $(cat /etc/issue | grep -i centos > /dev/null) ; then #one of these is superfluous
	DISTRO=FEDORA
elif $(cat /etc/issue | grep -i 'cent os' > /dev/null) ; then #space in cent os?
	DISTRO=FEDORA
fi
#echo detected $DISTRO

if [ $USER = 'root' ] ; then
	case "$DISTRO" in
		UBUNTU)
			WEB_BASE=/var/www
			;;
		FEDORA)
			WEB_BASE=/var/www/html
			;;
	esac
	IS_ROOT=true

else
	WEB_BASE="/home/$USER/public_html"
	case "$DISTRO" in
		UBUNTU)
			SU_COMMAND=sudo
			;;
		FEDORA)
			SU_COMMAND="su root -c"
			;;
	esac
#FIXME:  only for testing	
#	WEB_BASE="/home/$USER/public_html2"
#	IS_ROOT=false
fi

case "$DISTRO" in
	UBUNTU)
		WEB_USER=www-data 
		WWW_SERVICE=apache2
		PKG_COMMAND="apt-get install"
		;;
	FEDORA)
		WEB_USER=apache
		WWW_SERVICE=httpd
		PKG_COMMAND="yum -y install"
		;;
esac





function start_services {
	/sbin/service postgresql start
	/sbin/service $WWW_SERVICE start
}

function make_log {
	if ! [ -d $LOG_DIR ] ; then
		mkdir $LOG_DIR
	fi
	touch $LOG_DIR/$LOG_FILE
	$SU_COMMAND chown $WEB_USER $LOG_DIR/$LOG_FILE
}
	
function install_software_reqs {

    case "$DISTRO" in
        UBUNTU)
            PKG_REQS="$DEB_REQS"
            ;;
        FEDORA)
            PKG_REQS="$RPM_REQS"
            ;;
	esac
	$SU_COMMAND "$PKG_COMMAND $PKG_REQS"
	$SU_COMMAND "pecl install zip"
}

function install_software {
	if [ "$SOURCE_TARBALL" ]; then
		if [ $( dirname $SOURCE_TARBALL ) = "." ] ; then
			SOURCE_TARBALL=$(pwd)/$SOURCE_TARBALL
		fi
	fi

	if [ -d $APP_DIR ]
		then echo $WEB_BASE/$APP_DIR already exists.
		echo You must remove it before running this script.
		exit 1;
	fi

	if [ "$SOURCE_TARBALL" ]; then
		if ! $( tar xfz $SOURCE_TARBALL ) ; then
			echo failed to unpack tarball: $SOURCE_TARBALL
			exit 1;
		fi
	else
		git clone $GIT_REPO
	fi
}

function create_config_file {
	touch $CONFIG_DB_DEST #FIXME--It shouldn't exist, but should test anyway
	chmod 600 $CONFIG_DB_DEST
	cat $CONFIG_DB_TEMPLATE | \
		sed "s/\\\$PG_USER/$PG_USER/g" | \
		sed "s/\\\$PG_DB/$PG_DB/g" | \
		sed "s/\\\$PG_SERVER/$PG_HOST/g" | \
		sed "s/\\\$PG_PASS/$PG_PASS/g" | \
		sed "s/\\\$PG_PORT/$PG_PORT/g" \
		>> $CONFIG_DB_DEST
	chmod 400 $CONFIG_DB_DEST
	$SU_COMMAND "chown $WEB_USER $CONFIG_DB_DEST"
}
	

if [ "$SOURCE_TARBALL" = "-h" ] ||  [ "$SOURCE_TARBALL" = "--help" ] ; then
		echo
		echo Usage: $0 [ agency_source.tgz ]
		echo
		exit
fi

read -p "Please be advised this install script is EXPERIMENTAL.  Would you like to continue? (y/N)" -n 1 alpha_warn
echo
echo
if [ "$alpha_warn" != "Y" ] && [ "$alpha_warn" != "y" ] ; then
	exit
fi

read -p "would you like to install any necessary software dependencies? (y/N)" -n 1 install_reqs
echo
if [ "$install_reqs" == "y" ] || [ "$install_reqs" == "Y" ] ; then
	install_software_reqs
	if [ $? != 0 ] ; then
		echo
		read -n 1 -p "there was an error installing software requirements.  Do you want to continue? (y/N)" -n 1 proceed
		echo
		if [ "$proceed" != "Y" ] && [ "$proceed" != "y" ] ; then
			exit 1
		fi
	fi
fi

cd $WEB_BASE

if [ "$SOURCE_TARBALL" != "" ] ; then
	# If specified, assume yes install
	install_software=Y
else
	read -p "would you like to install AGENCY software with Git? (Y/n)" -n 1 install_software
	echo
fi

if [ "$install_software" != "n" ] && [ "$install_software" != "N" ] ; then
	install_software
	if [ $? != 0 ] ; then
		echo
		read -n 1 -p "there was an error installing software.  Do you want to continue?" -n 1 proceed
		echo
		if [ "$proceed" != "Y" ] && [ "$proceed" != "y" ] ; then
			exit 1
		fi
	fi
fi

start_services

echo cding to $WEB_BASE/$APP_DIR/$AG_CORE/$PG_SU_DIR
cd $WEB_BASE/$APP_DIR/$AG_CORE/$PG_SU_DIR
if [ $? != 0 ] ; then
	echo could not change to directory for superuser scripts, from $( pwd )
	exit 1;
fi

echo In directory $( pwd )
if [ ! -f $PG_SU_SCRIPT ] ; then
	echo cant find script: $PG_SU_SCRIPT
	echo something is wrong!
	exit 1;
fi

echo attempting to run database superuser scripts...
echo in directory $( pwd )
case $DISTRO in 
		UBUNTU) sudo $PG_SU_SCRIPT $PG_DB $PG_USER $PG_SU_USER $PG_SU_SQL ; run_su=$? ;;
		FEDORA) su -c "$PG_SU_SCRIPT $PG_DB $PG_USER $PG_SU_USER $PG_SU_SQL" ; run_su=$? ;;
esac 
if   [ $run_su = 0 ] ; then
	echo successfully ran database superuser scripts.
else
	echo Attempting to run the database superuser scripts encountered an error.  Exiting...
	#Fixme--allow to continue
	exit 1
fi

cd ../.. ; #to datababase/pg
echo Please choose which version of AGENCY to install:
select AG_FLAVOR in $( ls -1 install.*_database.sql | cut -f 2 -d '.' | cut -f 1 -d '_' ) ; do
	if [ "$AG_FLAVOR" != "" ] ; then
		break;
	fi
done;

PG_SQL=install.${AG_FLAVOR}_database.sql
LOCAL_CONFIG_SRC=agency_config_local.sample.$AG_FLAVOR

echo running rest of install scripts for $AG_FLAVOR version
cat $PG_SQL | psql -U $PG_USER -h $PG_HOST $PG_DB 
if [ $? = 0 ] ; then
	echo successfully ran AGENCY install script
else
	echo Error running AGENCY install scripts
	exit 1;
fi

cp $WEB_BASE/$APP_DIR/config/$LOCAL_CONFIG_SRC $WEB_BASE/$APP_DIR/config/agency_config_local.php

read -p "would you like to create the agency_config_db.php file? (Y/n)" -n 1 create_config
echo

if [ "$create_config" != "N"  ] && [ "$create_config" != "n" ] ; then
	echo
	echo "OK.  Sorry to do this to you, but I'll need to ask you your database password 2 more times."
	PG_PASS2=dummy
	while [ "$PG_PASS" != "$PG_PASS2" ] ; do
		read -s -p "Please enter your database password: " PG_PASS
		echo
		read -s -p "Please confirm your database password: " PG_PASS2
		echo
	done
	cd ../../ # Should be same as $WEBBASE
	create_config_file 
fi

make_log
#setsebool -P httpd_can_network_connect_db=1

