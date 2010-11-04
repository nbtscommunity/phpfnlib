#! /bin/bash

exec < /dev/null > /dev/null &

export NICKNAME USERNAME

($* < /dev/null > /dev/null &) & 
