#!/bin/sh

if ! [ "$1" ] || ! [ "$2" ]; then
	echo
	echo "Usage: $0 [table_name(w/o the l_)] \"[description field datatype]\""
	echo
	echo "(outputs create statement for lookup table creation)"
	echo 
 	exit;
fi

echo "CREATE TABLE l_$1 ("
echo "	$1_code	VARCHAR(10) PRIMARY KEY,"
echo "	description $2 NOT NULL UNIQUE"
echo ");"
echo 
echo
