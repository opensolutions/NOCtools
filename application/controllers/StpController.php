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
            $this->view->portRolesDevice = $device          = $this->_getParam( 'portRolesDevice' );
            $this->view->limitToInstance = $limitToInstance = $this->_getParam( 'limitToInstance', false );
            $this->view->type            = $type            = $this->getParam( 'type', 'rstp' );

            try
            {
                $host = new \OSS_SNMP\SNMP( $device, $this->_options['community'] );
                
                switch( $type )
                {
                    case 'mst':
                        $instances = $host->useCisco_SMST()->instances();
                        break;
                        
                    case 'rstp':
                    default:
                        $instances = $host->useCisco_VTP()->vlanNames();
                }
            }
            catch( \OSS_SNMP\Exception $e )
            {
                $this->addMessage( "Could not query instance and port role information via SNMP from " . $device, OSS_Message::ERROR );
                return;
            }

            $roles       = [];
            $unknowns    = [];
            $portsInSTP  = [];

            if( $limitToInstance && isset( $instances[ $limitToInstance ] ) )
                $doInstances[ $limitToInstance ] = $instances[ $limitToInstance ];
            else
                $doInstances = $instances;
                
            foreach( $doInstances as $iid => $iname )
            {
                try
                {
                	if( $type == 'mst' )
    	                $roles[ $iid ] = $host->useCisco_MST()->portRoles( $iid, true );
                	else
	                	$roles[ $iid ] = $host->useCisco_RSTP()->portRoles( $iid, true );

                    foreach( $roles[ $iid ] as $portId => $role )
                        if( !isset( $portsInSTP[ $portId ] ) )
                            $portsInSTP[ $portId ] = $portId;
                }
                catch( \OSS_SNMP\Exception $e )
                {
                    $unknowns[ $iid ] = $iname;
                }
            }

            ksort( $portsInSTP, SORT_NUMERIC );
            $this->view->portsInSTP  = $portsInSTP;
            $this->view->ports       = $host->useIface()->names();
            $this->view->instances   = $instances;
            $this->view->roles       = $roles;
            $this->view->unknowns    = $unknowns;
            unset( $host );
        }

    }
    
    /**
     * AJAX function to provide a JSON list of MST instances on a particular device.
     *
     * For example, used to dynamically populate dropdowns in @see CdpController::stpTopologyAction()
     *
     * @param string $host The SNMP addressable hostname of the device to query for MST instances
     * @return JSON Encoded array of [instanceId] => 'instanceName' entries.
     */
    public function ajaxGetMstInstancesForHostAction()
    {
        $host = $this->_getParam( 'host', null );

        if( $host )
        {
            try
            {
                $device = new \OSS_SNMP\SNMP( $host, $this->_options['community'] );
                $instances = $device->useCisco_SMST()->instances();
                unset( $device );
            }
            catch( \OSS_SNMP\Exception $e )
            {
                return;
            }

            $this->getResponse()
                ->setHeader('Content-Type', 'application/json')
                ->setBody( Zend_Json::encode( $instances ) )
                ->sendResponse();
            exit( 0 );
        }
    }
     

    public function cliPortRolesDeltaAction()
    {
    	$devices = [];
OSS_Debug::dd( $devices );
		foreach( $this->_devices as $d )
		{
			$devices[ $d ] = [];
			 
			try
			{
				$device = new \OSS_SNMP\SNMP( $d, $this->_options['community'] );
				
				$devices[$d]['instances'] = $device->useCisco_SMST()->instances();
				
				foreach( $devices[$d]['instances'] as $iid => $iname )
				{
					$devices[$d]['instance_roles'][$iid] = $device->useCisco_MST()->portRoles( $iid, true );
				}
				
				print_r( $devices[ $d ] );
			}
			catch( \OSS_SNMP\Exception $e )
			{
				continue;
				return;
			}
				
		}
    }
}


