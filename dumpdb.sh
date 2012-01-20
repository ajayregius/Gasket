#!/bin/sh

# show progress dots
function progress(){
echo -n "Please wait..."
while true
do
     echo -n "."
     sleep 1
done
}

function dodump(){
	# Redirect output to stderr.
	exec 1>&2

	DBHOST="localhost"
	DBUSER="root"
	DBPASS=""
	DB="gg"
	SCHEMAPATH="!DATABASE"

	echo
	echo "Dumping database $DB from $DBHOST to $SCHEMAPATH/$DB.sql"
	echo
	mysqldump -h $DBHOST -u $DBUSER --skip-extended-insert $DB > $SCHEMAPATH/$DB.sql
}

# Start it in the background
progress &

# Save PID
MYSELF=$!

# Start backup
dodump

# Kill progress
kill $MYSELF &>/dev/null

echo -n "...done."
echo

exit 0



