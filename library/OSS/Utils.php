<?php
/**
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

class OSS_Utils
{


    /**
     * A function to generate a URL with the given parameters.
     * This is a useful function as no knowledge of the application's path is required.
     * Uses Zend_Controller_Front.
     *
     * All params starting with an underscore are ignored in generating the URL as they have
     * special meaning:
     *
     *   _noHost: if true, only sends the path (ie. no http://www.example.com)
     *   _ssl: if true (and we're production!), sets https://
     *
     * @param string|bool $controller default false The controller to call.
     * @param string|bool $action default false The action to call (controller must be set if setting action)
     * @param string|bool $module default false The module to use. Set to false to ignore.
     * @param array $params default array() An array of key value pairs to add to the URL.
     * @param string $host Defaults to null. Hostname (including http[s]://) to override url with
     * @return string
     */
    public static function genUrl( $controller = false, $action = false, $module = false, $params = array(), $host = null )
    {
        $params[ '_noHost' ] = true;
        $url = Zend_Controller_Front::getInstance()->getBaseUrl();

        if( isset( $params[ '_noHost' ] ) && $params[ '_noHost' ] )
        {
            $noHost = true;
            $host = '';
            unset( $params[ '_noHost' ] );
        }
        else
            $noHost = false;

        if( $host !== null || $noHost )
        {
            // strip out http[s]://
            if( strpos( $url, 'https://' ) === 0 )
                $url = substr( $url, 8 );
            else if( strpos( $url, 'http://' ) === 0 )
                $url = substr( $url, 7 );

            $pos = strpos( $url, '/' );

            if( $pos !== false )
                $url = substr( $url, $pos );

            $url = $host . $url;
        }

        // when the webpage is directly under "xyz.com/", and not in "xyz.com/wherever"
        // an empty href attribute in an anchor tag means "the current URL", which is not always good
        //if( $url == '' )
        if( !$noHost && strpos( $url, 'http' ) !== 0 )
        {
            $tmp = 'http';

            if( isset( $_SERVER['HTTPS'] ) && ( $_SERVER['HTTPS'] == 'on' ) )
                $tmp .= 's';

            $tmp .= "://{$_SERVER['HTTP_HOST']}";

            $url = "{$tmp}{$url}";
        }

        if( $module )
            $url .= "/{$module}";

        if( $controller )
            $url .= "/{$controller}";

        if ( $action )
            $url .= "/{$action}";

        if( sizeof( $params ) > 0 )
        {
            foreach( $params as $var => $value )
                if( strpos( $var, '_' ) !== 0 )
                    $url .= "/{$var}/{$value}";
        }
        
        if( !$noHost && isset( $params['_ssl'] ) && $params['_ssl'] )
        {
            if( APPLICATION_ENV == 'production' )
            {
                if( strpos( $url, 'http:' ) === 0 )
                    $url = 'https' . substr( $url, 4 );
            }
        }

        return $url;
    }
}
