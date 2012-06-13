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
 * A class to encapsulate messages to be displayed on the webpages.
 *
 * These are the main required elements for this:
 *
 * 1. The OSS/Message.php class (this file)
 * 2. The OSS/Smarty/functions/function.OSS_Message.php (to display the message on the view)
 * 3. The relevent CSS classes in public/css/oss.css
 *
 * To use this, add a message to the view as follows:
 *
 * public function exampleAction() {
 *     $this->view->ossAddMessage( new OSS_Message( 'This is a info message!', OSS_Message::INFO ) );
 * }
 *
 * Multiple messages can be added of different kinds (INFO, ALERT, etc).
 *
 * Then to display these messages in your view (Smarty template) just include the following
 * text (i.e. Smarty function):
 *
 * {OSS_Message}
 *
 */
class OSS_Message_Block extends OSS_Message
{

    /**
     * Elements for the action area
     */
    private $actions = null;


    public function __construct( $message = '', $class = '', $isHTML = true )
    {
        parent::__construct( $message, $class, $isHTML );
        $this->setType( self::TYPE_BLOCK );
    }

    public function addAction( $str )
    {
        if( $this->actions === null )
            $this->actions = array();

        $this->actions[] = $str;
    }

    public function getActions()
    {
        return $this->actions;
    }
}
