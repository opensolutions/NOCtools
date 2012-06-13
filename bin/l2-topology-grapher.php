#!/usr/bin/php
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

require_once( dirname( __FILE__ ) . '/../library/OSS/SNMP.php' );

require_once( 'config.php' );


$rootDevice = new \OSS\SNMP( $confDevices[ $confTopologyRoot ], $confCommunity );

$devices = array();

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

echo $digraph;

