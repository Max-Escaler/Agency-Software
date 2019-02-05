#!/bin/sh
# small wrapper script to run card_local.php
# First echos log file, so previous history
# is available in the terminal, then runs
# card_local with a tee to the log file

DIR=/home/gate/agency/scripts

nice -20 /usr/bin/php -q $DIR/card_local.php $1
