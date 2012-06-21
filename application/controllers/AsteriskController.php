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
 * AsteriskController
 *
 * @author Barry O'Donovan <barry@opensolutions.ie>
 * @see https://github.com/opensolutions/NOCtools/wiki/Asterisk
 */
class AsteriskController extends OSS_Controller_Action
{

    /**
     * The default action - show the home page
     */
    public function indexAction()
    {
    }

    /**
     * Turn time ticks into a string
     *
     * @param int $tt Time ticks
     * @return string String represnetation as "x days, HH::mm:ss"
     */
    private function _timeticksToString( $tt )
    {
        $secs = floor( $tt / 100 );
        
        $days = floor( $secs / 60 / 60 / 24 );
        $secs -= $days * 60 * 60 * 24;
        
        $hrs  = floor( $secs / 60 / 60 );
        $secs -= $hrs * 60 * 60;
        
        $mins = floor( $secs / 60 );
        $secs -= $mins * 60;
        
        return sprintf( "%d days, %02d:%02d:%02d", $days, $hrs, $mins, $secs );
    }

    /**
     * Query Asterisk for details via SNMP
     */
    public function ajaxGetForHostAction()
    {
        $host = $this->_getParam( 'host', null );

        if( $host )
        {
            $device = new \OSS_SNMP\SNMP( $host, $this->_options['community'] );
            
            $details['astVersion'] = $device->useAsterisk()->version();
            $details['astTag']     = $device->useAsterisk()->tag();
            
            $details['astUptime']  = $this->_timeticksToString( $device->useAsterisk()->uptime() );
            $details['astReload']  = $this->_timeticksToString( $device->useAsterisk()->reloadTime() );
            
            $details['astCallsProcessed'] = $device->useAsterisk()->callsProcessed();
            $details['astCallsActive']    = $device->useAsterisk()->callsActive();
            
            $details['calls'] = $device->useAsterisk_Channels()->channelDetails( true );
            
            unset( $device );
                
            $this->getResponse()
                ->setHeader('Content-Type', 'application/json')
                ->setBody( Zend_Json::encode( $details ) )
                ->sendResponse();
            
            exit( 0 );
        }
    }
}
