#!/bin/sh

if ! [ "$1" ] ; then
	echo
	echo Usage: $0 table_name [custom_id_field]
	echo "(enables auto-alert notify triggers for actions in Postgres)"
	echo
 	exit;
fi

TABLE=$1
CUSTOM_COLUMN=$2
cat <<DONE
-- create trigger
CREATE TRIGGER ${TABLE}_alert_notify
	AFTER INSERT OR UPDATE OR DELETE ON ${TABLE}
	FOR EACH ROW EXECUTE PROCEDURE table_alert_notify(${CUSTOM_COLUMN});
DONE
exit
