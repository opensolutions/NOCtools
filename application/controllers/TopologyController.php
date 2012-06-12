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
 * TopologyController
 *
 * @author
 * @version
 */

class TopologyController extends OSS_Controller_Action
{

    /**
     * The default action - show the home page
     */
    public function indexAction()
    {
        // TODO Auto-generated TopologyController::indexAction() default action
    }


    public function l2GraphAction()
    {
        
        $rootDevice = new \OSS\SNMP( $this->_options['topology']['l2grapher']['root'], $this->_options['community'] );
        
        $devices = array();
        
        $confTopologyIgnore = array(
            'edge1.ixdub1.brs.ie', 'edge2.ixdub1.networkrecovery.ie',
                'FINEOS_DR_Site', 'CInfinity-inside-SW04', 'Luzern-sw-01',
                    'PHNRDUBESX03.nrintdub.lan'
                    );
                    
        $links = $rootDevice->useCisco_CDP()->linkTopology( $rootDevice->useCisco_CDP()->crawl( $devices, null, $confTopologyIgnore ) );
        
        ////////////////////////////////////////////////
        // Start to build up the GraphViz graph
        ////////////////////////////////////////////////
        
        $digraph = <<<END_GRAPH
        
digraph G {
        
    nodesep = 2;
    ranksep = 2;
        
END_GRAPH;
        
        
        // first, we define a custom function that extracts the data centre / localtion for the device name. YMMV.
        function customExtractLocation( $swname )
        {
            // take the second component (where components separated by dots / periods)
            return substr( $swname, strpos( $swname, '.' ) + 1, strpos( $swname, '.', strpos( $swname, '.' ) + 1 ) - strpos( $swname, '.' ) - 1 );
        }
        
        // with this function, allocate switches to localtions
        $locations = array();
        foreach( $devices as $devName => $devNeighbours )
        {
            $location = customExtractLocation( $devName );
            $locations[ $location ][] = $devName;
        }
        
        
        // now, define these devices in the appropriate locations in GraphViz
        //
        // To try and make the connections 'better', we alternate ports above
        //   and below the device name via $cnt
        
        $cnt = 0;
        foreach( $locations as $feLocation => $feSwitches )
        {
            // define subgraph / cluster display parameters
            $digraph .= "    subgraph \"cluster_{$feLocation}\" {\n"
            . "        style=filled;\n"
            . "        color=lightgrey;\n"
            . "        node [style=filled,fillcolor=white];\n"
            . "        label = \"" . strtoupper( $feLocation ) . "\";\n\n";
        
            // now add the switches in this location to this location:
            foreach( $feSwitches as $feSwitch )
            {
                $ports = 0;
                $digraphports = "                <TR>\n";
        
                // we'll iterate over this switches neighbours to find the switch's ports that we need to graph
                foreach( $devices[ $feSwitch ] as $feNeighbour => $feConnections )
                {
                    foreach( $feConnections as $portDetails )
                    {
                        $ports++;
                        $digraphports .= "                    <TD PORT=\"{$portDetails['localPort']}\">" . $portDetails['localPortName'] . "</TD>\n";
                    }
                }
        
                $digraphports .= "                </TR>\n";
        
                $digraphname   = "                <TR>\n"
                . "                    <TD COLSPAN=\"{$ports}\">{$feSwitch}</TD>\n"
                . "                </TR>\n";
        
                $digraph .= "        \"{$feSwitch}\" [ shape=\"plaintext\", label=<\n"
                . "            <TABLE BORDER=\"0\" CELLBORDER=\"1\" CELLSPACING=\"0\" CELLPADDING=\"4\">\n";
        
                if( $cnt % 2 == 0 )
                    $digraph .= $digraphname . $digraphports;
                else
                    $digraph .= $digraphports . $digraphname;
        
                    $digraph .= "            </TABLE>\n        >];\n\n";
        
                $cnt++;
            }
        
            $digraph .= "    }\n\n";
        }
        
        // now, add the interswitch connections
        //
        // if it's a LAG link, I change the colour to red
        
        
        foreach( $links as $feSwitch1 => $feConnectedSwitches )
        {
            foreach( $feConnectedSwitches as $feSwitch2 => $fePorts )
            {
                foreach( $fePorts as $feLocalPort => $feRemotePortDetails )
                {
                    $digraph .= "    \"{$feSwitch1}\":\"{$feLocalPort}\" -> \"{$feSwitch2}\":\"{$feRemotePortDetails['remotePort']}\" [ arrowhead =none, arrowtail=none";
    
                    if( $feRemotePortDetails['isLAG'] )
                        $digraph .= ", color=\"red\"";
    
                    $digraph .= " ]\n";
                }
            }
        }
    
    
        $digraph .= "\n\n}\n";
    
        file_put_contents( APPLICATION_PATH . '/../var/tmp/l2-topology.dot', $digraph );
        
        system( '/usr/bin/dot -T png -o ' . APPLICATION_PATH . '/../var/tmp/l2-topology.png ' . APPLICATION_PATH . '/../var/tmp/l2-topology.dot' );
      
        header( 'content-type: image/png' );
        readfile( APPLICATION_PATH . '/../var/tmp/l2-topology.png' );  
die();        
        
        
    }
}
