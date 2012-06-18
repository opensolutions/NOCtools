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
 */

class CdpController extends OSS_Controller_Action
{

    /**
     * The default action - show the home page
     */
    public function indexAction()
    {
        // TODO Auto-generated TopologyController::indexAction() default action
    }


    public function neighboursAction()
    {
        $this->view->cdp_root = $host = $this->_getParam( 'cdp_root', '' );

        if( strlen( $host ) )
        {
            try
            {
                $host = new \OSS\SNMP( $host, $this->_options['community'] );
                $this->view->host = $host;
                $this->view->neighbours = $host->useCisco_CDP()->neighbours( true, $this->_options['cdp']['l2topology']['ignore'] );
            }
            catch( \OSS\Exception $e )
            {
                $this->addMessage( "Could not perform CDP neighbour discovery on the requested host", OSS_Message::ERROR );
                $this->_forward( 'index' );
            }
        }
    }

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
            $host = new \OSS\SNMP( $cdp_root, $this->_options['community'] );
            $this->view->host = $host;
            $this->view->deviceId = $host->useCisco_CDP()->id();
            $this->view->neighbours = $host->useCisco_CDP()->neighbours();
        }
        catch( \OSS\Exception $e )
        {
            $this->addMessage( "Could not perform CDP neighbour discovery on the requested host", OSS_Message::ERROR );
            $this->_forward( 'index' );
        }

        header( 'content-type: image/png' );

        $file = 'img-neighbour-graph-' . OSS_String::random( 16, true, true, true );

        file_put_contents( APPLICATION_PATH . '/../var/tmp/' . $file . '.dot', $this->view->render( 'cdp/img-neighbours-graph.dot' ) );

        system( '/usr/bin/dot -T png -o ' . APPLICATION_PATH . '/../var/tmp/'
                    . $file . '.png ' . APPLICATION_PATH . '/../var/tmp/' . $file . '.dot' );

        header( 'content-type: image/png' );
        readfile( APPLICATION_PATH . '/../var/tmp/' . $file . '.png' );
    }

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

                $root = new \OSS\SNMP( $host, $this->_options['community'] );

                $devices = array();
                $root->useCisco_CDP()->crawl( $devices, null, $ignoreList );

                // if we're not splitting LAGs, we need to sanitise the $links
                if( !$splitLags )
                    $devices = $root->useCisco_CDP()->collapseDevicesLAGs( $devices );

                $this->view->links = $root->useCisco_CDP()->linkTopology( $devices );
                $this->view->devices = $devices;
                $this->view->locations = $this->extractLocation( $this->view->devices );

                $this->view->file = $file = OSS_String::random( 16, true, true, true );
                $file = 'img-cdp-topology-' . $file;

                file_put_contents( APPLICATION_PATH . '/../var/tmp/' . $file . '.dot',
                    $this->view->render( 'cdp/l2-topology-graph.dot' )
                );

            }while( false );
        }
        else
        {
            $this->view->ignoreList = implode( "\n", $this->_options['cdp']['l2topology']['ignore'] );
        }

    }

    public function imgL2TopologyAction()
    {
        $file = $this->_getParam( 'file', false );

        if( $file && file_exists( APPLICATION_PATH . '/../var/tmp/img-cdp-topology-' . $file . '.dot' ) )
        {
            $file = 'img-cdp-topology-' . $file;

            if( !file_exists( APPLICATION_PATH . '/../var/tmp/' . $file . '.png' ) )
                system( '/usr/bin/dot -T png -o ' . APPLICATION_PATH . '/../var/tmp/'
                        . $file . '.png ' . APPLICATION_PATH . '/../var/tmp/' . $file . '.dot' );

            header( 'content-type: image/png' );
            readfile( APPLICATION_PATH . '/../var/tmp/' . $file . '.png' );
        }
    }

    public function extractLocation( $devs )
    {
        $locations = array();
        foreach( $devs as $swname => $info )
        {
            $loc = substr( $swname, strpos( $swname, '.' ) + 1, strpos( $swname, '.', strpos( $swname, '.' ) + 1 ) - strpos( $swname, '.' ) - 1 );
            if( !isset( $locations[ $loc ] ) )
                $locations[ $loc ] = array();

            $locations[ $loc ][] = $swname;
        }

        return $locations;
    }

    public function cliGenerateDeviceIniAction()
    {
        $root = new \OSS\SNMP( $this->_options['cdp']['default_root'], $this->_options['community'] );
        $devices = array();
        $devices = $root->useCisco_CDP()->crawl( $devices, null );
        ksort( $devices, SORT_REGULAR );

        foreach( $devices as $name => $details )
            echo "devices[] = \"{$name}\"\n";
    }



    public function rstpTopologyAction()
    {
        $this->view->cdp_root               = $host                   = $this->_getParam( 'cdp_root', '' );
        $this->view->vlanid                 = $vlanid                 = $this->_getParam( 'vlanid', false );
        $this->view->excludeNonParticipants = true;
        $this->view->showPortRoles          = true;

        $this->view->ignoreList = implode( "\n", $this->_options['cdp']['l2topology']['ignore'] );

        if( strlen( $host ) )
        {
            $root = new \OSS\SNMP( $host, $this->_options['community'] );
            $this->view->vlans = $root->useCisco_VTP()->vlanNames();
        }

        if( $this->getRequest()->isPost() )
        {
            do
            {
                if( $vlanid && $this->_getParam( 'excludeNonParticipants', null ) === null )
                    $this->view->excludeNonParticipants = $excludeNonParticipants = false;

                if( $vlanid && $this->_getParam( 'showPortRoles', null ) === null )
                    $this->view->showPortRoles = $showPortRoles = false;

                $this->view->ignoreList = ( isset( $this->view->ignoreList ) ? $this->view->ignoreList : $this->_getParam( 'ignoreList' ) );
                $ignoreList =  array();

                foreach( explode( "\n", $this->_getParam( 'ignoreList' ) ) as $i )
                    $ignoreList[] = trim( $i );

                if( !strlen( $host ) )
                {
                    $this->addMessage( 'You must select a device as the root for CDP neighbour discovery and a VLAN', OSS_Message::ERROR );
                    break;
                }

                if( !$vlanid )
                    break;

                $devices = array();
                $root->useCisco_CDP()->crawl( $devices, null, $ignoreList );

                // we're not splitting LAGs, we need to sanitise the links
                $devices = $root->useCisco_CDP()->collapseDevicesLAGs( $devices );


                // now, find the links which are participating in RSTP and their roles
                $portRoles = [];

                foreach( $devices as $aDevice => $neighbours )
                {
                    if( !isset( $portRoles[ $aDevice ] ) )
                        $portRoles[ $aDevice ] = $this->_rstpTopologyPortRoles( $aDevice, $vlanid );

                    foreach( $neighbours as $bDevice => $ports )
                        if( !isset( $portRoles[ $bDevice ] ) )
                            $portRoles[ $bDevice ] = $this->_rstpTopologyPortRoles( $bDevice, $vlanid );
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
                                if( in_array( $devices[ $aDevice ][ $bDevice ][ $idx ][ 'localRSTP' ], \OSS\SNMP\MIBS\Cisco\RSTP::$STP_X_RSTP_PASSING_PORT_ROLES )
                                            && in_array( $devices[ $aDevice ][ $bDevice ][ $idx ][ 'remoteRSTP' ], \OSS\SNMP\MIBS\Cisco\RSTP::$STP_X_RSTP_PASSING_PORT_ROLES ) )
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
                $this->view->locations = $this->extractLocation( $devices );

                $this->view->file = $file = OSS_String::random( 16, true, true, true );
                $file = 'img-rstp-topology-' . $file;

                file_put_contents( APPLICATION_PATH . '/../var/tmp/' . $file . '.dot',
                    $this->view->render( 'cdp/rstp-topology-graph.dot' )
                );

            }while( false );
        }
    }

    /**
     * Utility function to get the RSTP port roles for a given device on a given VLAN
     *
     * @see rstpTopologyAction()
     * @param string $device The device to query
     * @param int $vlanid The VLAN id to query
     * @return array Array of roles for participating ports (empty array of none or if device could not be queried)
     */
    private function _rstpTopologyPortRoles( $device, $vlanid )
    {
        try
        {
            $_h = new \OSS\SNMP( $device, $this->_options['community'] );
            return $_h->useCisco_RSTP()->rstpPortRole( $vlanid, true );
        }
        catch( Exception $e )
        {
            return array();
        }
    }

    public function imgRstpTopologyAction()
    {
        $file = $this->_getParam( 'file', false );

        if( $file && file_exists( APPLICATION_PATH . '/../var/tmp/img-rstp-topology-' . $file . '.dot' ) )
        {
            $file = 'img-rstp-topology-' . $file;

            if( !file_exists( APPLICATION_PATH . '/../var/tmp/' . $file . '.png' ) )
                system( '/usr/bin/dot -T png -o ' . APPLICATION_PATH . '/../var/tmp/'
                        . $file . '.png ' . APPLICATION_PATH . '/../var/tmp/' . $file . '.dot' );

            header( 'content-type: image/png' );
            readfile( APPLICATION_PATH . '/../var/tmp/' . $file . '.png' );
        }
    }


}
