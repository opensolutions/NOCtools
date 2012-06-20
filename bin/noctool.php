#!/usr/bin/env php
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

require_once( dirname( __FILE__ ) . '/utils.inc' );
define( 'APPLICATION_ENV', scriptutils_get_application_env() );

define( 'SCRIPT_NAME', 'noctool - NOCtools CLI Management Tool (V' . APPLICATION_VERSION . ')' );

if( date( 'Y' ) != '2012' )
    define( 'SCRIPT_COPY', '(c) Copyright 2012 - ' . date( 'Y' ) . ' Open Source Solutions Limited' );
else
    define( 'SCRIPT_COPY', '(c) Copyright 2012 Open Source Solutions Limited' );

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

// face it, we want this:
require_once 'OSS_SNMP/OSS_SNMP/SNMP.php';

require_once 'Zend/Application.php';

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
