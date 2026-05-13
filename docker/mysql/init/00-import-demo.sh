#!/bin/sh
set -eu

MYSQL_ARGS="-uroot"

if [ -n "${MYSQL_ROOT_PASSWORD:-}" ]; then
	MYSQL_ARGS="$MYSQL_ARGS -p$MYSQL_ROOT_PASSWORD"
fi

mysql $MYSQL_ARGS "$MYSQL_DATABASE" < /seed/20250901_HP_DemoSPC_Data.sql
