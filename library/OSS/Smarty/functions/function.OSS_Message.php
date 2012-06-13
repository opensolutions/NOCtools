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
     * Function to generate a login form based on a ZendForm object.
     *
     * @param array $params
     * @param Smarty $smarty A reference to the Smarty template object
     * @return string The login form
     */
    function smarty_function_OSS_Message( $params, &$smarty )
    {
        $ossms = $smarty->getTemplateVars( 'OSS_Messages' );

        if( $ossms === null ) $ossms = array();

        if( isset( $_SESSION['Application']['OSS_Messages'] ) && is_array( $_SESSION['Application']['OSS_Messages'] )
                && sizeof( $_SESSION['Application']['OSS_Messages'] ) > 0 )
        {
            $ossms = array_merge($ossms, $_SESSION['Application']['OSS_Messages']);
            unset($_SESSION['Application']['OSS_Messages']);
        }

        if ( $ossms == array() ) return '';

        $count = 0;

        foreach( $ossms as $ossm )
        {
            if( isset( $params['randomid'] ) && $params['randomid'] )
                $count = mt_rand();

            if( $ossm instanceof OSS_Message_Block )
            {
                $message .= <<<END_MESSAGE

    <div class="alert alert-block alert-{$ossm->getClass()} fade in" id="oss-message-{$count}">
        <a class="close" href="#" data-dismiss="alert">×</a>
        {$ossm->getMessage()}
END_MESSAGE;
                if( count( $ossm->getActions() ) )
                {
                    $message .= "        <div class=\"alert-actions\">\n";

                    foreach( $ossm->getActions() as $a )
                        $message .= $a . "\n";

                    $message .= "        </div>\n";
                }

                $message .= <<<END_MESSAGE
    </div>

END_MESSAGE;
            }
            else
            {

                $items = $ossm->getMessage();

                if( !is_array( $items ) )
                    $items = array( $items );

                foreach( $items as $item )
                {
                        $message .= <<<END_MESSAGE

        <div class="alert alert-{$ossm->getClass()} fade in" id="oss-message-{$count}">
            <a class="close" href="#" data-dismiss="alert">×</a>
            {$item}
        </div>

END_MESSAGE;
                }
            } // end inner foreach

            $count++;
        } // end foreach()


        return $message;
    }
