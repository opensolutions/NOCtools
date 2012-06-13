<?php
/**
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
