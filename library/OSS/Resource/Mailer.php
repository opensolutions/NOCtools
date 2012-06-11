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
 * Class to set up the mail transport
 *
 * @category OSS
 * @package OSS_Bootstrap_Resources
 * @copyright Copyright (c) 2009 Open Source Solutions Limited <http://www.opensolutions.ie/>
 */
class OSS_Resource_Mailer extends Zend_Application_Resource_ResourceAbstract
{
    /**
     * Holds the Mailer instance
     *
     * @var
     */
    protected $_mailer;


    public function init()
    {
        // Return mailer so bootstrap will store it in the registry
        return $this->getMailer();
    }


    public function getMailer()
    {
        if( null === $this->_mailer )
        {
            $options = $this->getOptions();

            if( count( $options ) )
            {
                if( isset( $options['auth'] ) )
                    $config = $options;
                else
                    $config = array();

                $transport = new Zend_Mail_Transport_Smtp( $options['smtphost'], $config );
                
                if( isset( $options['helo'] ) )
                {
                    $protocol = new Zend_Mail_Protocol_Smtp( $options['smtphost'] );
                    $protocol->connect();
                    $protocol->helo( 'www.opensolutions.ie' );
                
                    $transport->setConnection( $protocol );
                }
                
                Zend_Mail::setDefaultTransport( $transport );

                $this->_mailer = $transport;
            }
        }

        return $this->_mailer;
    }


}
