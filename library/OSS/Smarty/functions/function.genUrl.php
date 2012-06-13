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
     * Function to generate a Zend Controller URL from Smarty templates.
     *
     * The URL is made up of parameters as supplied in the $params associative array.
     * 'module', 'controller' and 'action' are special parameters which indicate the module,
     * controller and action to call. Any other parameters are added as additional name / value
     * pairs.
     *
     * Calls OSS_Utils::genUrl()
     *
     * @param array $params An array of the parameters to make up the URL
     * @param Smarty $smarty A reference to the Smarty object
     * @return string The URL to use
     */
    function smarty_function_genUrl( $params, &$smarty )
    {
        if( !isset( $params['controller'] ) )
            $params['controller'] = false;

        if( !isset( $params['action'] ) )
            $params['action'] = false;

        if( !isset( $params['module'] ) )
            $params['module'] = false;

        $p = $params;
        unset( $p['controller'] );
        unset( $p['action'] );
        unset( $p['module'] );

        return OSS_Utils::genUrl( $params['controller'], $params['action'], $params['module'], $p );
    }
