#!/bin/sh
# makethumbs.sh
# script to make thumbnails for any pictures missing them
# in an AGENCY photo directory.
# It doesn't do levels of directories--you have to recurse it yourself.

thumb="120x160"
ext=jpg
for x in `ls *$ext | grep -v $thumb`
do
        y=`echo $x | cut -f 1 -d "."`
        if ! [ -f $y.$thumb.$ext ]
        then
                convert -geometry 120x160 $y.$ext $y.$thumb.$ext
        fi
done
chown .agencylink *jpg
chmod 664 *jpg
echo All done with `pwd`
