<?php

/*
    Copyright (c) 2012, Open Source Solutions Limited, Dublin, Ireland
    All rights reserved.

    This file is part of the NOCtools package.

    Contact: Barry O'Donovan - barry (at) opensolutions (dot) ie
             http://www.opensolutions.ie/

    Redistribution and use in source and binary forms, with or without
    modification, are permitted provided that the following conditions are met:

        * Redistributions of source code must retain the above copyright
          notice, this list of conditions and the following disclaimer.
        * Redistributions in binary form must reproduce the above copyright
          notice, this list of conditions and the following disclaimer in the
          documentation and/or other materials provided with the distribution.
        * Neither the name of Open Source Solutions Limited nor the
          names of its contributors may be used to endorse or promote products
          derived from this software without specific prior written permission.

    THIS SOFTWARE IS PROVIDED BY THE COPYRIGHT HOLDERS AND CONTRIBUTORS "AS IS" AND
    ANY EXPRESS OR IMPLIED WARRANTIES, INCLUDING, BUT NOT LIMITED TO, THE IMPLIED
    WARRANTIES OF MERCHANTABILITY AND FITNESS FOR A PARTICULAR PURPOSE ARE
    DISCLAIMED. IN NO EVENT SHALL <COPYRIGHT HOLDER> BE LIABLE FOR ANY
    DIRECT, INDIRECT, INCIDENTAL, SPECIAL, EXEMPLARY, OR CONSEQUENTIAL DAMAGES
    (INCLUDING, BUT NOT LIMITED TO, PROCUREMENT OF SUBSTITUTE GOODS OR SERVICES;
    LOSS OF USE, DATA, OR PROFITS; OR BUSINESS INTERRUPTION) HOWEVER CAUSED AND
    ON ANY THEORY OF LIABILITY, WHETHER IN CONTRACT, STRICT LIABILITY, OR TORT
    (INCLUDING NEGLIGENCE OR OTHERWISE) ARISING IN ANY WAY OUT OF THE USE OF THIS
    SOFTWARE, EVEN IF ADVISED OF THE POSSIBILITY OF SUCH DAMAGE.
*/

// let's time how long it takes to execute
define( 'APPLICATION_STARTTIME', microtime( true ) );

error_reporting( E_ALL ^ E_NOTICE );

mb_internal_encoding( 'UTF-8' );
mb_language( 'uni' );
setlocale( LC_ALL, "en_IE.utf8" );

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

require_once( APPLICATION_PATH . '/../library/OSS/Version.php' );

// Ensure library/ is in include_path
set_include_path( implode( PATH_SEPARATOR, array( realpath( APPLICATION_PATH . '/../library' ), get_include_path() ) ) );

// Zend_Application
require_once 'Zend/Application.php';

// Create application, bootstrap, and run
$application = new Zend_Application( APPLICATION_ENV, APPLICATION_PATH . '/configs/application.ini' );

$application->bootstrap()->run();

$scriptExecutionTime = microtime( true ) - APPLICATION_STARTTIME;
