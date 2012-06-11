#!/usr/bin/env php
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

/**
 * CLI script
 */

require_once( dirname( __FILE__ ) . '/utils.inc' );
define( 'APPLICATION_ENV', scriptutils_get_application_env() );

define( 'SCRIPT_NAME', 'cli-tool - NOCtools CLI Management Tool (V' . APPLICATION_VERSION . ')' );
define( 'SCRIPT_COPY', '(c) Copyright 2012 - ' . date( 'Y' ) . ' Open Source Solutions Limited' );

error_reporting( E_ALL );
ini_set( 'display_errors', true );

defined( 'APPLICATION_PATH' ) || define( 'APPLICATION_PATH', realpath( dirname( __FILE__ ) . '/../application' ) );

// Ensure library/ is on include_path
set_include_path( implode( PATH_SEPARATOR,
        array(
            realpath( APPLICATION_PATH . '/../library' ),
            get_include_path()
        )
    )
);

/** Zend_Application */
require_once 'Zend/Application.php';

// Create application, bootstrap, and run

$application = new Zend_Application( APPLICATION_ENV, APPLICATION_PATH . '/configs/application.ini' );

try
{
    $application->bootstrap();
    $bootstrap = $application->getBootstrap();
    $bootstrap->bootstrap( 'frontController' );
}
catch( Exception $e )
{
    die( print_r( $e, true ) );
}

try
{
    $opts = new Zend_Console_Getopt(
        array(
            'help|h'        => 'Displays usage information.',
            'action|a=s'    => 'Action to perform in format of module.controller.action',
            'testmode|t'    => 'Enables APPLICATION_TESTING mode for actions supporting it',
            'verbose|v'     => 'Verbose messages will be dumped to the default output.',
            'development|d' => 'Enables development mode.',
            'copyright|c'   => 'Display copyright information.'
        )
    );

    $opts->parse();
}
catch( Zend_Console_Getopt_Exception $e )
{
    exit( $e->getMessage() . "\n\n" . $e->getUsageMessage() );
}

if( isset( $opts->h ) || isset( $opts->help ))
{
    echo "\n\033[1mHelp:\033[0m\n";

    echo "\thelp|h,        Displays usage information.
\taction|a=s,    Action to perform in format of module.controller.action.
\ttestmode|t,    Enables APPLICATION_TESTING mode for actions supporting it.
\tverbose|v,     Verbose messages will be dumped to the default output.
\tdevelopment|d, Enables development mode.
\tcopyright|c,   Display copyright information.\n\n";

    echo "\n" . SCRIPT_NAME . "\n" . SCRIPT_COPY . "\n\n";
    exit;
}

if( isset( $opts->c ) )
{
    echo SCRIPT_NAME . "\n" . SCRIPT_COPY . "\n\n";
    echo <<<END_BSD
    
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

END_BSD;
    exit;
}

define( 'APPLICATION_TESTING', isset( $opts->t ) );

if( isset( $opts->a ) )
{
    try
    {
        $reqRoute = array_reverse( explode( '.', $opts->a ) );

        @list( $action, $controller, $module ) = $reqRoute;

        if( ($action != '') && ($controller == '') )
        {
            $controller = $action;
            $action = 'index';
        }

        if( $opts->v )
            echo '[' . date( 'Y-m-d H:i:s' ) . "] Starting {$module}/{$controller}/{$action}...\n";

        $front = $bootstrap->frontController;

        $front->throwExceptions( true );

        $front->setRequest(  new Zend_Controller_Request_Simple( $action, $controller, $module ) );
        $front->setRouter(   new OSS_Controller_Router_Cli() );
        $front->setResponse( new Zend_Controller_Response_Cli() );

        $front->setParam( 'noViewRenderer', true )
              ->setParam( 'disableOutputBuffering', true );

        if( $opts->v )
            $front->getRequest()->setParam( 'cli-verbose', true );
        else
            $front->getRequest()->setParam( 'cli-verbose', false );
        
        //$front->addModuleDirectory( APPLICATION_PATH . '/modules' );

        $application->run();

        if( $opts->v )
            echo '[' . date( 'Y-m-d H:i:s' ) . "] Completed {$module}/{$controller}/{$action}.\n";
    }
    catch( Exception $e )
    {
        echo "ERROR: " . $e->getMessage() . "\n\n";

        if( $opts->v )
        {
            echo $e->getTraceAsString();
        }
    }
}
else
{
    echo "\n\033[1mHelp:\033[0m\n";

    echo "\thelp|h,        Displays usage information.
\taction|a=s,    Action to perform in format of module.controller.action.
\ttestmode|t,    Enables APPLICATION_TESTING mode for actions supporting it.
\tverbose|v,     Verbose messages will be dumped to the default output.
\tdevelopment|d, Enables development mode.
\tcopyright|c,   Display copyright information.\n\n";

    echo "\n" . SCRIPT_NAME . "\n" . SCRIPT_COPY . "\n\n";
    exit;
}
