#!/bin/bash

OLDDIR=$(pwd)
ROOTDIR="$(dirname "$(dirname "$(readlink -f "$0")")")"
cd $ROOTDIR
composer ruckusing
cd $OLDDIR
