
#!/bin/bash

cd /var/www/html/fileshare/development/www/css || exit 1

exec /usr/bin/sass --watch --poll scss:output
