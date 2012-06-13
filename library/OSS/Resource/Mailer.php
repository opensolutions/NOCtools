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
