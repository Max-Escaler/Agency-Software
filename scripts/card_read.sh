#!/bin/sh
# card_readh.sh
# This is part of the gatekeeping (entry) machine
# It reads a line from standard in (which would be redirected from the card reader)
# It writes the card info, plus a timestamp, out to an output
# file (OUT1 or OUT2), which would be processed by other little scripts.
# Normally, output goes to OUT1, but if BLOCKFILE exists, it will go to OUT2 instead.

BASE=/home/gate/
POOL_LOCAL=data/local
POOL_SQL=data/sql

cd $BASE

while [ 1 = 1 ] ; do
        read CARD
        if [ $CARD ] ; then
                TIME=`date "+%Y-%m-%d_%H.%M.%S"`
                touch $POOL_SQL/"$TIME"_$CARD
                touch $POOL_LOCAL/"$TIME"_$CARD
                echo GOT "$TIME"_$CARD
                echo $TIME
        fi
done
