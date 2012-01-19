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

function doimport(){
	# Redirect output to stderr.
	exec 1>&2

	DBHOST="localhost"
	DBUSER="root"
	DBPASS=""
	DB="gg"
	SCHEMAPATH="!DATABASE"

	echo
	echo "Importing $SCHEMAPATH/$DB.sql to $DB on $DBHOST"
	echo
	mysql -h $DBHOST -u $DBUSER $DB < $SCHEMAPATH/$DB.sql
}

# Start it in the background
progress &

# Save PID
MYSELF=$!

# Start backup
doimport

# Kill progress
kill $MYSELF &>/dev/null

echo -n "...done."
echo

exit 0



