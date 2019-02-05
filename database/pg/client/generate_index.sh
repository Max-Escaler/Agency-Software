#!/bin/sh

if ! [ "$1" ] || ! [ "$2" ]; then
	echo
	echo "Usage: $0 [table_name] [field_name]"
	echo
	echo "(outputs create statement for index)"
	echo 
 	exit;
fi

echo "CREATE INDEX index_$1_$2 ON $1 ( $2 );"
echo
