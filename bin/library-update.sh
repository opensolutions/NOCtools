#!/bin/bash

#
#    Copyright (c) 2012, Open Source Solutions Limited, Dublin, Ireland
#    All rights reserved.
#
#    Contact: Barry O'Donovan - barry (at) opensolutions (dot) ie
#             http://www.opensolutions.ie/
#
#    This file is part of the NOCtools package.
#
#    NOCtools is free software: you can redistribute it and/or modify
#    it under the terms of the GNU General Public License as published by
#    the Free Software Foundation, either version 3 of the License, or
#    (at your option) any later version.
#
#    NOCtools is distributed in the hope that it will be useful,
#    but WITHOUT ANY WARRANTY; without even the implied warranty of
#    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
#    GNU General Public License for more details.
#
#    You should have received a copy of the GNU General Public License
#    along with NOCtools.  If not, see <http://www.gnu.org/licenses/>.

# This file will update up SVN / git externals in library/


# Is SVN installed and in the path?

svn &>/dev/null

if [[ $? -eq 127 ]]; then
    echo ERROR: SVN not installed or not in the path
    exit
fi

git &>/dev/null

if [[ $? -eq 127 ]]; then
    echo ERROR: Git not installed or not in the path
    exit
fi


LIBDIR=`dirname "$0"`/../library
TOPDIR=`dirname "$0"`/..

cd $LIBDIR/Minify
git pull
cd -

cd $LIBDIR/OSS_SNMP
git pull
cd -

for name in Zend Smarty; do
    echo -e "\n\n\n\n\n-------------\n\nUpdating $name..."
    cd $LIBDIR/$name
    svn up
    cd -
done

