<?php
/**
    Copyright (c) 2012, Open Source Solutions Limited, Dublin, Ireland
    All rights reserved.

    This file is part of the phpNOCtools package.

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
