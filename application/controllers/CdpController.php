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
 * CdpController
 *
 * @author Barry O'Donovan <barry@opensolutions.ie>
 * @see https://github.com/opensolutions/NOCtools/wiki/Cisco-Discovery-Protocol
 */

class CdpController extends NOCtools_Controller_Action
{

    /**
     * The default action - show the home page
     */
    public function indexAction()
    {
        // TODO Auto-generated TopologyController::indexAction() default action
    }


    /**
     * For a given switch, display its CDP neighbours with information and also a graph showing connected ports.
     *
     * @see https://github.com/opensolutions/NOCtools/wiki/CDP-Neighbours
     */
    public function neighboursAction()
    {
        $this->view->cdp_root = $host = $this->_getParam( 'cdp_root', '' );

        if( strlen( $host ) )
        {
            try
            {
                $host = new \OSS_SNMP\SNMP( $host, $this->_options['community'] );
                $this->view->host = $host;
                $this->view->neighbours = $host->useCisco_CDP()->neighbours( true, $this->_getIgnoreList() );
            }
            catch( \OSS_SNMP\Exception $e )
            {
                $this->addMessage( "Could not perform CDP neighbour discovery on the requested host", OSS_Message::ERROR );
                $this->_forward( 'index' );
            }
        }
    }

    /**
     * Generate and serve generated image from neighboursAction()
     * @see CdpController::neighboursAction()
     */
    public function imgNeighboursGraphAction()
    {
        $this->view->cdp_root = $cdp_root = $this->_getParam( 'cdp_root', '' );

        if( !strlen( $cdp_root ) )
        {
            header( 'content-type: text/plain' );
            echo 'You must provide a hostname or IP address for CDP neighbour discovery';
            return;
        }

        try
        {
            $host = new \OSS_SNMP\SNMP( $cdp_root, $this->_options['community'] );
            $this->view->host = $host;
            $this->view->deviceId = $host->useCisco_CDP()->id();
            $this->view->neighbours = $host->useCisco_CDP()->neighbours();
        }
        catch( \OSS_SNMP\Exception $e )
        {
            $this->addMessage( "Could not perform CDP neighbour discovery on the requested host", OSS_Message::ERROR );
            $this->_forward( 'index' );
        }

        $file = $this->generateGraphFilename( array( $cdp_root ) );
        
        Zend_Controller_Action_HelperBroker::removeHelper('viewRenderer');
        header( 'content-type: image/png' );
        readfile( $this->generateDotGraph( $file, $this->view->render( 'cdp/img-neighbours-graph.dot' ) ) );
    }

    /**
     * Graph the layer 2 network topology based on a recursive crawl of CDP neighbours.
     *
     * @see https://github.com/opensolutions/NOCtools/wiki/CDP-L2-Topology
     */
    public function l2TopologyAction()
    {
        if( $this->getRequest()->isPost() )
        {
            do
            {
                $this->view->splitLags = $splitLags = $this->_getParam( 'splitLags', false );

                $this->view->ignoreList = $this->_getParam( 'ignoreList' );
                $ignoreList =  array();

                foreach( explode( "\n", $this->_getParam( 'ignoreList' ) ) as $i )
                    $ignoreList[] = trim( $i );

                $this->view->cdp_root = $host = $this->_getParam( 'cdp_root', '' );

                if( !strlen( $host ) )
                {
                    $this->addMessage( 'You must provide a hostname or IP address for CDP neighbour discovery', OSS_Message::ERROR );
                    break;
                }

                $root = new \OSS_SNMP\SNMP( $host, $this->_options['community'] );

                $devices = array();
                $root->useCisco_CDP()->crawl( $devices, null, $ignoreList );

                // if we're not splitting LAGs, we need to sanitise the $links
                if( !$splitLags )
                    $devices = $root->useCisco_CDP()->collapseDevicesLAGs( $devices );

                $this->view->links = $root->useCisco_CDP()->linkTopology( $devices );
                $this->view->devices = $devices;
                $this->view->locations = call_user_func( "{$this->_options['utilsClass']}::extractLocations", $devices );

                $this->view->file = $file = $this->generateGraphFilename( array( $host ) );

                if( $this->_getParam( 'submit' ) == 'Download DOT File' )
                {
                    header( 'Content-type: text/plain' );
                    header( 'Content-Disposition: attachment; filename="' . $file . '.dot"' );
                    Zend_Controller_Action_HelperBroker::removeHelper( 'viewRenderer' );
                    echo $this->view->render( 'cdp/l2-topology-graph.dot' );
                    return;
                }

                $this->getSessionNamespace()->l2_topology_file
                    = $this->generateDotGraph( $file, $this->view->render( 'cdp/l2-topology-graph.dot' ), APPLICATION_PATH . '/../var/tmp' );

            }while( false );
        }
        else
        {
            $this->view->ignoreList = implode( "\n", $this->_getIgnoreList() );
        }

    }

    /**
     * Serve generated image from l2TopologyAction()
     * @see CdpController::l2TopologyAction()
     */
    public function imgL2TopologyAction()
    {
        if( isset( $this->getSessionNamespace()->l2_topology_file ) )
        {
            Zend_Controller_Action_HelperBroker::removeHelper('viewRenderer');
            header( 'content-type: image/png' );
            readfile( $this->getSessionNamespace()->l2_topology_file );
        }
    }

    /**
     * A CLI tool to generate a list of devices by performing a CDP crawl.
     *
     * @see https://github.com/opensolutions/NOCtools/wiki/Devices-Configuration
     */
    public function cliGenerateDeviceIniAction()
    {
        $root = new \OSS_SNMP\SNMP( $this->_options['cdp']['default_root'], $this->_options['community'] );
        $devices = array();
        $devices = $root->useCisco_CDP()->crawl( $devices, null );
        ksort( $devices, SORT_REGULAR );

        foreach( $devices as $name => $details )
            echo "devices[] = \"{$name}\"\n";
    }


    /**
     * Similar to L2 Topology above but this takes a specific VLAN / instance and identifies and graphs Per-VLAN/instance Spanning Tree port roles.
     *
     * @see https://github.com/opensolutions/NOCtools/wiki/CDP-RSTP-Port-Roles
     */
    public function stpTopologyAction()
    {
        $this->view->cdp_root               = $host                   = $this->getParam( 'cdp_root', '' );
        $this->view->type                   = $type                   = $this->getParam( 'type', 'rstp' );
        $this->view->instance               = $instance               = $this->getParam( 'instance', false );
        $this->view->excludeNonParticipants = true;
        $this->view->showPortRoles          = true;

        $this->view->ignoreList = implode( "\n", $this->_getIgnoreList() );

        if( strlen( $host ) )
        {
            $root = new \OSS_SNMP\SNMP( $host, $this->_options['community'] );
            
            if( $type == 'mst' )
                $this->view->instances = $instances = $root->useCisco_SMST()->instances();
            else
                $this->view->instances = $instances = $root->useCisco_VTP()->vlanNames();
        }

        if( $this->getRequest()->isPost() )
        {
            do
            {
                if( $instance !== false && $this->getParam( 'excludeNonParticipants', null ) === null )
                    $this->view->excludeNonParticipants = $excludeNonParticipants = false;

                if( $instance !== false && $this->getParam( 'showPortRoles', null ) === null )
                    $this->view->showPortRoles = $showPortRoles = false;

                $this->view->ignoreList = ( isset( $this->view->ignoreList ) ? $this->view->ignoreList : $this->getParam( 'ignoreList' ) );
                $ignoreList =  array();

                foreach( explode( "\n", $this->getParam( 'ignoreList' ) ) as $i )
                    $ignoreList[] = trim( $i );

                if( !strlen( $host ) )
                {
                    $this->addMessage( 'You must select a device as the root for CDP neighbour discovery and a VLAN / instance', OSS_Message::ERROR );
                    break;
                }

                if( $instance === false )
                    break;
                    
                $devices = array();
                $root->useCisco_CDP()->crawl( $devices, null, $ignoreList );

                // we're not splitting LAGs, we need to sanitise the links
                $devices = $root->useCisco_CDP()->collapseDevicesLAGs( $devices );


                // now, find the links which are participating in RSTP / MST and their roles
                $portRoles = [];

                foreach( $devices as $aDevice => $neighbours )
                {
                    if( !isset( $portRoles[ $aDevice ] ) )
                        $portRoles[ $aDevice ] = $this->_stpTopologyPortRoles( $aDevice, $type, $instance );

                    foreach( $neighbours as $bDevice => $ports )
                        if( !isset( $portRoles[ $bDevice ] ) )
                            $portRoles[ $bDevice ] = $this->_stpTopologyPortRoles( $bDevice, $type, $instance );
                }


                foreach( $devices as $aDevice => $neighbours )
                {
                    foreach( $neighbours as $bDevice => $ports )
                    {
                        foreach( $ports as $idx => $portDetails )
                        {
                            if( isset( $portRoles[ $aDevice ][ $portDetails['localPortId'] ] ) && isset( $portRoles[ $bDevice ][ $portDetails['remotePortId'] ] ) )
                            {
                                $devices[ $aDevice ][ $bDevice ][ $idx ][ 'localRSTP' ]  = $portRoles[ $aDevice ][ $portDetails['localPortId'] ];
                                $devices[ $aDevice ][ $bDevice ][ $idx ][ 'remoteRSTP' ] = $portRoles[ $bDevice ][ $portDetails['remotePortId'] ];

                                // indicate if the link is passing traffic or not
                                if( in_array( $devices[ $aDevice ][ $bDevice ][ $idx ][ 'localRSTP' ], \OSS_SNMP\MIBS\Cisco\RSTP::$STP_X_RSTP_PASSING_PORT_ROLES )
                                            && in_array( $devices[ $aDevice ][ $bDevice ][ $idx ][ 'remoteRSTP' ], \OSS_SNMP\MIBS\Cisco\RSTP::$STP_X_RSTP_PASSING_PORT_ROLES ) )
                                    $devices[ $aDevice ][ $bDevice ][ $idx ][ 'RSTPpassing' ] = true;
                                else
                                    $devices[ $aDevice ][ $bDevice ][ $idx ][ 'RSTPpassing' ] = false;
                            }
                            else
                            {
                                $devices[ $aDevice ][ $bDevice ][ $idx ][ 'localRSTP'  ]  = false;
                                $devices[ $aDevice ][ $bDevice ][ $idx ][ 'remoteRSTP' ]  = false;
                                $devices[ $aDevice ][ $bDevice ][ $idx ][ 'RSTPpassing' ] = null;
                            }
                        }
                    }
                }

                $links = $root->useCisco_CDP()->linkTopology( $devices );

                $this->view->devices = $devices;
                $this->view->links   = $links;
                $this->view->locations = call_user_func( "{$this->_options['utilsClass']}::extractLocations", $devices );


                $this->view->file = $file = $this->generateGraphFilename( array( $host, $vlanid ) );

                if( $this->_getParam( 'submit' ) == 'Download DOT File' )
                {
                    header( 'Content-type: text/plain' );
                    header( 'Content-Disposition: attachment; filename="' . $file . '.dot"' );
                    Zend_Controller_Action_HelperBroker::removeHelper( 'viewRenderer' );
                    echo $this->view->render( 'cdp/stp-topology-graph.dot' );
                    return;
                }

                $this->getSessionNamespace()->stp_topology_file
                    = $this->generateDotGraph( $file, $this->view->render( 'cdp/stp-topology-graph.dot' ), APPLICATION_PATH . '/../var/tmp' );

            }while( false );
        }
    }

    /**
     * Utility function to get the RSTP port roles for a given device on a given VLAN
     *
     * @see rstpTopologyAction()
     * @param string $device The device to query
      *@param string $type Either 'mst' or else 'rstp' assumed
     * @param int $instance The VLAN / MST instance id to query
     * @return array Array of roles for participating ports (empty array of none or if device could not be queried)
     */
    private function _stpTopologyPortRoles( $device, $type, $instance )
    {
        try
        {
            $_h = new \OSS_SNMP\SNMP( $device, $this->_options['community'] );
            
            if( $type == 'mst' )
                return $_h->useCisco_MST()->portRoles( $instance, true );
            else
                return $_h->useCisco_RSTP()->portRoles( $instance, true );
        }
        catch( Exception $e )
        {
            return array();
        }
    }

    /**
     * Serve generated image from rstpTopologyAction()
     * @see CdpController::rstpTopologyAction()
     */
    public function imgStpTopologyAction()
    {
        if( isset( $this->getSessionNamespace()->stp_topology_file ) )
        {
            Zend_Controller_Action_HelperBroker::removeHelper('viewRenderer');
            header( 'content-type: image/png' );
            readfile( $this->getSessionNamespace()->stp_topology_file );
        }
    }


    /**
     * Get the ignore list - list of hosts that should not be polled for CDP data
     *
     * This is set in `application.ini` and this function just sanitsies is.
     *
     * @return array The ignore list or empty array
     */
    protected function _getIgnoreList()
    {
        if( isset( $this->_options['cdp']['l2topology']['ignore'] ) && is_array( $this->_options['cdp']['l2topology']['ignore'] ) )
            return $this->_options['cdp']['l2topology']['ignore'];

        return array();
    }

}
