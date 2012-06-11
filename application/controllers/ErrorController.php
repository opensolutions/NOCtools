<?php

/*
    Copyright (c) 2012, Open Source Solutions Limited, Dublin, Ireland
    All rights reserved.

    This file is part of the phpNOCtools package.

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
