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
 * StpController
 *
 * @author Barry O'Donovan <barry@opensolutions.ie>
 * @see https://github.com/opensolutions/NOCtools/wiki/VLAN-Comparison
 */

class StpController extends NOCtools_Controller_Action
{
    /**
     * The default action - show the home page
     */
    public function indexAction()
    {
    }

    /**
     */
    public function portRolesAction()
    {
        if( $this->getRequest()->isPost() )
        {
            $this->view->portRolesDevice = $device      = $this->_getParam( 'portRolesDevice' );
            $this->view->limitToVlan     = $limitToVlan = $this->_getParam( 'limitToVlan', false );
            $this->view->type            = $type        = $this->getParam( 'type', 'rstp' );

            try
            {
                $host = new \OSS_SNMP\SNMP( $device, $this->_options['community'] );
                $vlans = $host->useCisco_VTP()->vlanNames();
            }
            catch( \OSS_SNMP\Exception $e )
            {
                $this->addMessage( "Could not query VLAN and port role information via SNMP from " . $device, OSS_Message::ERROR );
                return;
            }

            $roles       = [];
            $unknowns    = [];
            $portsInSTP  = [];

            if( $limitToVlan && isset( $vlans[ $limitToVlan ] ) )
            {
                $name = $vlans[ $limitToVlan ];
                unset( $vlans );
                $vlans = [ $limitToVlan => $name ];
            }

            foreach( $vlans as $vid => $vname )
            {
                try
                {
                	if( $type == 'mst' )
    	                $roles[ $vid ] = $host->useCisco_RSTP()->mstPortRole( $vid, true );
                	else
	                	$roles[ $vid ] = $host->useCisco_RSTP()->rstpPortRole( $vid, true );

                    foreach( $roles[ $vid ] as $portId => $role )
                        if( !isset( $portsInSTP[ $portId ] ) )
                            $portsInSTP[ $portId ] = $portId;
                }
                catch( \OSS_SNMP\Exception $e )
                {
                    $unknowns[ $vid ] = $vname;
                }
            }

            ksort( $portsInSTP, SORT_NUMERIC );
            $this->view->portsInSTP  = $portsInSTP;
            $this->view->ports       = $host->useIface()->names();
            $this->view->vlans       = $vlans;
            $this->view->roles       = $roles;
            $this->view->unknowns    = $unknowns;
            unset( $host );
        }

    }

}
