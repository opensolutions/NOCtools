<?php

/*
    Copyright (c) 2012, Open Source Solutions Limited, Dublin, Ireland
    All rights reserved.

    This file is part of the NOCtools package.

    Contact: Barry O'Donovan - barry (at) opensolutions (dot) ie
             http://www.opensolutions.ie/

    NOCtools is free software: you can redistribute it and/or modify
    it under the terms of the GNU General Public License as published by
    the Free Software Foundation, either version 3 of the License, or
    (at your option) any later version.

    NOCtools is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.

    You should have received a copy of the GNU General Public License
    along with NOCtools.  If not, see <http://www.gnu.org/licenses/>.
*/

// let's time how long it takes to execute
define( 'APPLICATION_STARTTIME', microtime( true ) );

error_reporting( E_ALL ^ E_NOTICE );

mb_internal_encoding( 'UTF-8' );
mb_language( 'uni' );
setlocale( LC_ALL, "en_IE.utf8" );

date_default_timezone_set( 'Europe/Dublin' );

// Define path to application directory
defined( 'APPLICATION_PATH' ) || define( 'APPLICATION_PATH', realpath( dirname( __FILE__ ) . '/../application' ) );

// Define application environment
if( getenv( 'APPLICATION_ENV' ) === false )
    die( 'ERROR: APPLICATION_ENV has not been defined!' );

define( 'APPLICATION_ENV', getenv( 'APPLICATION_ENV' ) );

if( getenv( 'APPLICATION_TESTING' ) )
    define( 'APPLICATION_TESTING', getenv( 'APPLICATION_TESTING' ) );
else
    define( 'APPLICATION_TESTING', 0 );

require_once( APPLICATION_PATH . '/../library/NOCtools/Version.php' );

// Ensure library/ is in include_path
set_include_path( implode( PATH_SEPARATOR, array( realpath( APPLICATION_PATH . '/../library' ), get_include_path() ) ) );


// face it, we want this:
require_once 'OSS_SNMP/OSS_SNMP/SNMP.php';

// Zend_Application
require_once 'Zend/Application.php';

// Create application, bootstrap, and run
$application = new Zend_Application( APPLICATION_ENV, APPLICATION_PATH . '/configs/application.ini' );

$application->bootstrap()->run();

$scriptExecutionTime = microtime( true ) - APPLICATION_STARTTIME;
