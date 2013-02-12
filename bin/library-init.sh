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


# This file will set up SVN and GitHub externals in library/


# Is SVN installed and in the path?

svn &>/dev/null

if [[ $? -eq 127 ]]; then
    echo ERROR: SVN not installed or not in the path
    exit
fi

git &>/dev/null

if [[ $? -eq 127 ]]; then
    echo ERROR: git not installed or not in the path
    exit
fi

LIBDIR=`dirname "$0"`/../library
TOPDIR=`dirname "$0"`/..


# Minify

if [[ -e $LIBDIR/Minify ]]; then
    echo Minify exists - skipping!
else
    git clone git://github.com/opensolutions/Minify.git $LIBDIR/Minify
fi

# OSS_SNMP

if [[ -e $LIBDIR/OSS_SNMP ]]; then
    echo OSS_SNMP exists - skipping!
else
    git clone git://github.com/opensolutions/OSS_SNMP.git $LIBDIR/OSS_SNMP
fi

# Zend

if [[ -e $LIBDIR/Zend ]]; then
    echo Zend exists - skipping!
else
    svn co http://framework.zend.com/svn/framework/standard/branches/release-1.12/library/Zend/ $LIBDIR/Zend
fi


# Smarty

if [[ -e $LIBDIR/Smarty ]]; then
    echo Smarty exists - skipping!
else
    svn co http://smarty-php.googlecode.com/svn/trunk/distribution/libs/ $LIBDIR/Smarty
fi

# OSS-Framework
if [[ -e $LIBDIR/OSS-Framework.git ]]; then
    echo OSS-Framework.git exists - skipping!
else  
    git clone git://github.com/opensolutions/OSS-Framework.git $LIBDIR/OSS-Framework.git
fi
    
# Twitter form decorators
if [[ -e $LIBDIR/Bootstrap-Zend-Framework ]]; then  
    echo Bootstrap-Zend-Framework exists - skipping!
else
    git clone git://github.com/opensolutions/Bootstrap-Zend-Framework.git $LIBDIR/Bootstrap-Zend-Framework
fi
            
        