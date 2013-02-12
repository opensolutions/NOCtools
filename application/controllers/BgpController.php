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
 * BgpController
 *
 * @author Barry O'Donovan <barry@opensolutions.ie>
 * @see https://github.com/opensolutions/NOCtools/wiki/BGP
 */

class BgpController extends NOCtools_Controller_Action
{

    /**
     * The default action - show the home page
     */
    public function indexAction()
    {
    }

    /**
     * Show BGP Summary table
     *
     * @see https://github.com/opensolutions/NOCtools/wiki/BGP-Summary
     */
    public function summaryAction()
    {
        if( $this->getRequest()->isPost() )
        {
            $this->view->bgpDevice = $device      = $this->_getParam( 'bgpDevice' );

            try
            {
                $host = new \OSS_SNMP\SNMP( $device, $this->_options['community'] );
                $peers = $host->useBGP()->peerDetails( true );
            }
            catch( \OSS_SNMP\Exception $e )
            {
                $this->addMessage( "Could not query BGP peer information via SNMP from " . $device, OSS_Message::ERROR );
                return;
            }

            ksort( $peers, SORT_NUMERIC );
            $this->view->peers = $peers;
        }

    }

}
