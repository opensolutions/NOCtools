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
