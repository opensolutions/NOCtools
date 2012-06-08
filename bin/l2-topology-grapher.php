#!/usr/bin/php
<?php

require_once( dirname( __FILE__ ) . '/../OSS/SNMP.php' );

require_once( 'config.php' );


$rootDevice = new \OSS\SNMP( $confDevices[ $confTopologyRoot ], $confCommunity );

$devices = array();

print_r( $rootDevice->useCisco_CDP()->linkTopology( $rootDevice->useCisco_CDP()->crawl( $devices, null, $confTopologyIgnore ) ) );
die();



