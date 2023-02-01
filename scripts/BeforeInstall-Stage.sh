#!/bin/bash
DIR="/var/www/html/test02"

#important
if [ -d "$DIR" ]; then
    echo "$DIR exists"
else
    echo "Creating $DIR Directory"
    mkdir ${DIR}
fi

# Change Ownership of all files and folders
sudo chmod -R 777 $DIR

# Change Ownership of all files and folders
sudo chown -R ubuntu:ubuntu $DIR

#rm -r -f -v $DIR
cp -r -f -v . $DIR
