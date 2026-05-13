#!/bin/sh
set -eu

mysql -uroot -p"$MYSQL_ROOT_PASSWORD" "$MYSQL_DATABASE" < /seed/20250901_HP_DemoSPC_Structure.sql
mysql -uroot -p"$MYSQL_ROOT_PASSWORD" "$MYSQL_DATABASE" < /seed/20250901_HP_DemoSPC_Data.sql
