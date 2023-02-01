#!/bin/bash

DIR="/var/www/html/test02"

#important
if [ -d "$DIR" ]; then
    echo "$DIR exists"
else
    echo "Creating $DIR Directory"
    mkdir ${DIR}
fi

sudo chmod -R 777 $DIR

#rm -r -f -v $DIR
cp -r -f -v . $DIR
