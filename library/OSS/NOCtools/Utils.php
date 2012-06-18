<?php
/**
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
 * OSS's version of various NOCtools utilities
 *
 * End users should subclass this and set the appropriate variable in applicaion/configs/application.ini
 *
 * @author Barry O'Donovan <barry@opensolutions.ie>
 * @category   OSS
 * @package    OSS_NOCtools
 * @copyright  Copyright (c) 2010 Open Source Solutions Limited <http://www.opensolutions.ie/>
 *
 */
class OSS_NOCtools_Utils
{
    /**
     * Return an array of locations and the devices at that location from the given
     * array where devices are named in the index.
     *
     * @see OSS_NOCtools_Utils::extractLocation()
     * @param array $devices An array indexed by device names from which a location can be extracted
     * @return array An array indexed by location names contained devices at that location
     */
    public static function extractLocations( $devices )
    {
        $locations = array();
        foreach( $devices as $name => $info )
        {
            $loc = self::extractLocation( $name );
            if( !isset( $locations[ $loc ] ) )
                $locations[ $loc ] = array();

            $locations[ $loc ][] = $name;
        }

        return $locations;
    }

    /**
     * Extract a location name from a hostname
     *
     * Many organisations embed location infroamtion in hostnames. This function extracts that location from a given hostname.
     *
     * This reference implmentation extract LOC from sw01.LOC.example.ie
     *
     * @param string $hn The hostname
     * @return string The extracted location
     */
    public static function extractLocation( $hn )
    {
        return substr( $hn, strpos( $hn, '.' ) + 1, strpos( $hn, '.', strpos( $hn, '.' ) + 1 ) - strpos( $hn, '.' ) - 1 );
    }
}
