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
