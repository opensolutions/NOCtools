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

/**
 * Controller class
 *
 * @author Barry O'Donovan <barry@opensolutions.ie>
 * @package    Controllers
 * @copyright  Copyright (c) 2012 Open Source Solutions Limited <http://www.opensolutions.ie/>
 */
class ErrorController extends OSS_Controller_Action
{

    /**
    * The default error handler action
    */
    public function errorAction()
    {
        $this->getLogger()->debug( "\n" );

        $this->getLogger()->debug( 'ErrorController::errorAction()' );

        $log = "\n\n************************************************************************\n"
        	. "****************************** EXCEPTIONS *******************************\n"
        	. "************************************************************************\n\n";

        $exceptions = $this->getResponse()->getException();

        foreach( $exceptions as $e )
        {
            $log .= "--------------------------- EXCEPTION --------------------------\n\n"
            	. "Message: " . $e->getMessage()
            	. "\nLine: "  . $e->getLine()
            	. "\nFile: "  . $e->getFile();

        	$log .= "\n\nTrace:\n\n"
        		. $e->getTraceAsString() . "\n\n"
        		. print_r( OSS_Debug::compact_debug_backtrace(), true )
        		. "\n\n";
        }

        $log .= "------------------------\n\n"
        	. "HTTP_HOST : {$_SERVER['HTTP_HOST']}\n"
        	. "HTTP_USER_AGENT: {$_SERVER['HTTP_USER_AGENT']}\n"
        	. "HTTP_COOKIE: {$_SERVER['HTTP_COOKIE']}\n"
        	. "REMOTE_PORT: {$_SERVER['REMOTE_PORT']}\n"
        	. "REQUEST_METHOD: {$_SERVER['REQUEST_METHOD']}\n"
        	. "REQUEST_URI: {$_SERVER['REQUEST_URI']}\n\n";

        $this->getResponse()->setBody( 'OK: 0' );

        if( isset( $this->view ) )
        {
            $errors = $this->_getParam( 'error_handler' );

            $this->getResponse()->clearBody();

            switch( $errors->type )
            {
                case Zend_Controller_Plugin_ErrorHandler::EXCEPTION_NO_CONTROLLER:
                case Zend_Controller_Plugin_ErrorHandler::EXCEPTION_NO_ACTION:
                    // 404 error -- controller or action not found
                    $this->getResponse()
                         ->setRawHeader( 'HTTP/1.1 404 Not Found' );

                    Zend_Controller_Action_HelperBroker::removeHelper( 'viewRenderer' );
                    $this->view->display( 'error/error-404.phtml' );
                    $this->getLogger()->debug( $log );
                    break;

                default:
                	$this->getLogger()->crit( $log );
                    $this->view->exceptions = $exceptions;
                    break;
            }
        }

        return true;
    }


    /**
    * does nothing
    */
    public function invalidAction()
    {
    }

}
