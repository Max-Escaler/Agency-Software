#!/bin/sh

if ! [ "$1" ] ; then
	echo
	echo Usage: $0 table_name
	echo "(enables automatic changed_at updating in Postgres)"
	echo
 	exit;
fi

TABLE=$1
cat <<DONE
-- drop old trigger
DROP TRIGGER ${TABLE}_changed_at_update ON ${TABLE};

-- create trigger
CREATE TRIGGER ${TABLE}_changed_at_update
	BEFORE UPDATE ON ${TABLE}
	FOR EACH ROW EXECUTE PROCEDURE auto_changed_at_update();
DONE
exit
