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

/*
 * Utilities for exclusive inclusion with and use by utilities in bin/
 *
 * @author Barry O'Donovan <barry@opensolutions.ie>
 */

// load version information
require_once( dirname( __FILE__ ) . '/../library/NOCtools/Version.php' );

/**
 * Parses public/.htaccess for application environment
 *
 * die()'s if not found
 */
function scriptutils_get_application_env()
{
    $htaccess_path = dirname( __FILE__ ) . "/../public/.htaccess";

    if( !is_readable( $htaccess_path ) )
    {
        die( "ERROR: public/.htaccess does not exist / is not readable - set this up first!\n" );
    }

    $htaccess = file_get_contents( $htaccess_path );

    $matches = array();
    preg_match_all( '/SetEnv\s+APPLICATION_ENV\s+(\w+)/i', $htaccess, $matches );

    if( isset( $matches[1][0] ) && strlen( $matches[1][0] ) )
        return trim( $matches[1][0] );

    die( "ERROR: Could not parse or find APPLICATION_ENV in $htaccess_path\n" );
}
