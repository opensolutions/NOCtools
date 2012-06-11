<?php
/**
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
 * Class to set up
 *
 * @category OSS
 * @package OSS_Bootstrap_Resources
 * @copyright Copyright (c) 2009 Open Source Solutions Limited <http://www.opensolutions.ie/>
 */
class OSS_Resource_Namespace extends Zend_Application_Resource_ResourceAbstract
{
    /**
     * Holds the Logger instance
     *
     * @var
     */
    protected $_session;


    public function init()
    {
        // Return session so bootstrap will store it in the registry
        return $this->getSession();
    }


    public function getSession()
    {
        if( null === $this->_session )
        {
            $this->getBootstrap()->bootstrap( 'Session' );

            // Get session configuration options from the application.ini file
            $options = $this->getOptions();

            $ApplicationNamespace = new Zend_Session_Namespace( 'Application' );

            // Secutiry tip from http://framework.zend.com/manual/en/zend.session.global_session_management.html
            if( !isset( $ApplicationNamespace->initialised ) )
            {
                // FIXME Zend_Session::regenerateId();
                $ApplicationNamespace->initialized = true;
            }

            // ensure IP consistancy
            if ( (isset($options['checkip'])) && ($options['checkip']) && (isset($_SERVER['REMOTE_ADDR'])) )
            {
                if( !isset( $ApplicationNamespace->clientIP ) )
                {
                    $ApplicationNamespace->clientIP = $_SERVER['REMOTE_ADDR'];
                }
                else if( $ApplicationNamespace->clientIP != $_SERVER['REMOTE_ADDR'] )
                {
                    // security violation - client IP has changed indicating a possible hijacked session
                    $this->getBootstrap()->bootstrap( 'Logger' );
                    $this->getBootstrap()->getResource('logger')->warn(
                        "IP address changed - possible session hijack attempt."
                        . "OLD: {$ApplicationNamespace->clientIP} NEW: {$_SERVER['REMOTE_ADDR']}"
                    );
                    Zend_Session::destroy( true, true );
                    die(
                        "Your IP address has changed indication a possible session hijack attempt. Your session has been destroyed for your own security."
                    );
                }
            }

            $this->_session = $ApplicationNamespace;

        }

        return $this->_session;
    }


}
