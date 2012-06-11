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

/*
 * Utilities for exclusive inclusion with and use by utilities in bin/
 *
 * @author Barry O'Donovan <barry@opensolutions.ie>
 */

// load version information
require_once( dirname( __FILE__ ) . '/../library/OSS/Version.php' );

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
