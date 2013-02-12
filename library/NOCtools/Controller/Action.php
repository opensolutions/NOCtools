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
 * Controller: Action
 *
 * @author     Barry O'Donovan <barry@opensolutions.ie>
 */
class NOCtools_Controller_Action extends OSS_Controller_Action
{
    // traits we want to use
    use OSS_Controller_Action_Trait_Namespace;
    use OSS_Controller_Action_Trait_Mailer;
    use OSS_Controller_Action_Trait_Logger;
    use OSS_Controller_Action_Trait_Smarty;
    use OSS_Controller_Action_Trait_Messages;
    
    /**
     * Zend_Config_Ini of device hostnames / IP addresses to populate dropdowns with / etc
     *
     * @var object Zend_Config_Ini of device hostnames / IP addresses to populate dropdowns with / etc
     */
    protected $_devices = null;
    

    /**
     * Override the Zend_Controller_Action's constructor (which is called
     * at the very beginning of this function anyway).
     *
     *
     * @param object $request See Parent class constructor
     * @param object $response See Parent class constructor
     * @param object $invokeArgs See Parent class constructor
     */
    public function __construct(
            Zend_Controller_Request_Abstract  $request,
            Zend_Controller_Response_Abstract $response,
            array $invokeArgs = null )
    {
        // call the parent's version where all the Zend magic happens
        parent::__construct( $request, $response, $invokeArgs );

        // load device / node array
        if( is_readable( APPLICATION_PATH . '/configs/devices.ini' ) )
        {
            $this->_devices = new Zend_Config_Ini( APPLICATION_PATH . '/configs/devices.ini' );
        
            $this->view->devices_ini = $this->_devices;
            $this->view->_devices = $this->_devices->devices;
        }
    }
    

    /**
     * Create a file name for a graph via controller / action and some other parameters.
     *
     */
    protected function generateGraphFilename( $params )
    {
        $n = 'dotimg-' . $this->_controller . '-' . $this->_action;
    
        if( isset( $this->_session->userRndId ) )
            $n .= '-' . $this->_session->userRndId;
    
        foreach( $params as $p )
            $n .= '-' . $p;
    
        return $n;
    }
    
    protected function generateDotGraph( $file, $dot, $tmp = null )
    {
        if( $tmp === null )
            $tmp = APPLICATION_PATH . '/../var/tmp';
    
        // auto clean up
        $this->autoCleanGraphFiles( $tmp, 'dotimg-' );
    
        $dotFile = "{$tmp}/{$file}.dot";
        $pngFile = "{$tmp}/{$file}.png";
    
        if( is_readable( $dotFile ) && file_get_contents( $dotFile ) == $dot )
            return $pngFile;
    
        file_put_contents( $dotFile, $dot );
    
        system( escapeshellcmd( "{$this->_options['cmd']['dot']} -T png -o {$pngFile} {$dotFile}" ) );
    
        return $pngFile;
    }
    
    
    protected function autoCleanGraphFiles( $tmp, $prefix )
    {
        $files = scandir( $tmp );
    
        if( $files && count( $files ) )
        {
            foreach( $files as $file )
            {
                if( is_file( "{$tmp}/{$file}" ) && is_writable( "{$tmp}/{$file}" ) && substr( $file, 0, strlen( $prefix ) ) == $prefix )
                {
                    if( time() - @stat( "{$tmp}/{$file}" )['atime'] > 60 * 60 * 24 )
                        @unlink( "{$tmp}/{$file}" );
                }
            }
        }
    }
    
}
