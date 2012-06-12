<?php

/*
    Copyright (c) 2012, Open Source Solutions Limited, Dublin, Ireland
    All rights reserved.

    This file is part of the NOCtools package.

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
                $this->view->neighbours = $host->useCisco_CDP()->neighbours();
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
        $this->view->cdp_root = $host = $this->_getParam( 'cdp_root', '' );

        if( !strlen( $host ) )
        {
            header( 'content-type: text/plain' );
            echo 'You must provide a hostname or IP address for CDP neighbour discovery';
            return;
        }

        try
        {
            $host = new \OSS\SNMP( $host, $this->_options['community'] );
            $this->view->host = $host;
            $this->view->neighbours = $host->useCisco_CDP()->neighbours();
        }
        catch( \OSS\Exception $e )
        {
            $this->addMessage( "Could not perform CDP neighbour discovery on the requested host", OSS_Message::ERROR );
            $this->_forward( 'index' );
        }

        $this->view->deviceId = $host->useCisco_CDP()->id();
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
                $this->view->links = $root->useCisco_CDP()->linkTopology( $root->useCisco_CDP()->crawl( $devices, null, $ignoreList ) );
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

}
