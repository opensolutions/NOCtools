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
 * Class to instantiate loggers
 *
 * @category OSS
 * @package OSS_Bootstrap_Resources
 * @copyright Copyright (c) 2009 Open Source Solutions Limited <http://www.opensolutions.ie/>
 */
class OSS_Resource_Logger extends Zend_Application_Resource_ResourceAbstract
{
    protected $_session;

    /**
     * Holds the Logger instance
     *
     * @var
     */
    protected $_logger;


    public function init()
    {
        // Return logger so bootstrap will store it in the registry
        return $this->getLogger();
    }


    public function getLogger()
    {
        if( null === $this->_logger )
        {
            // Get Doctrine configuration options from the application.ini file
            $options = $this->getOptions();

            $logger = new OSS_Log();

            if( $options['enabled'] )
            {
                foreach( $options['writers'] as $writer => $writerOptions )
                {
                    switch( $writer )
                    {
                        case 'stream':
                            $log_path = $writerOptions['path']
                                            . DIRECTORY_SEPARATOR .  date( 'Y' )
                                            . DIRECTORY_SEPARATOR . date( 'm' );

                            $log_file = $log_path . DIRECTORY_SEPARATOR . date( 'Ymd') . '.log';

                            if (file_exists($log_path) == false)
                            {
                                mkdir(  $log_path, 0755, true              );
                                @chmod( $log_path, 0755                    );
                                @chown( $log_path, $writerOptions['owner'] );
                                @chgrp( $log_path, $writerOptions['group'] );
                            }

                            if (file_exists($log_file) == false)
                            {
                                touch(  $log_file                          );
                                @chmod( $log_file, 0777                    );
                                @chown( $log_file, $writerOptions['owner'] );
                                @chgrp( $log_file, $writerOptions['group'] );
                            }

                            $streamWriter = new Zend_Log_Writer_Stream( $log_file );
                            $streamWriter->setFormatter(
                                new Zend_Log_Formatter_Simple(
                                    '%timestamp% %priorityName% (%priority%) ' . (isset($_SERVER['REMOTE_ADDR']) == true ? "[{$_SERVER['REMOTE_ADDR']}]" : "") . ': %message%' . PHP_EOL
                                )
                            );

                            $logger->addWriter( $streamWriter );

                            if ( isset($writerOptions['level']) ) $logger->addFilter( (int)$writerOptions['level'] );

                            break;

                        case 'email':
                            $this->getBootstrap()->bootstrap( 'Mailer' );

                            $mail = new Zend_Mail();
                            $mail->setFrom( $writerOptions['from'] )
                                 ->addTo( $writerOptions['to'] );

                            $mailWriter = new Zend_Log_Writer_Mail( $mail );

                            // Set subject text for use; summary of number of errors is appended to the
                            // subject line before sending the message.
                            $mailWriter->setSubjectPrependText( "[{$writerOptions['prefix']}]" );

                            // Only email entries with level requested and higher.
                            $mailWriter->addFilter( (int)$writerOptions['level'] );

                            $logger->addWriter( $mailWriter );
                            break;

                        case 'firebug':
                            if( $writerOptions['enabled'] )
                            {
                                $firebugWriter = new Zend_Log_Writer_Firebug();
                                $firebugWriter->addFilter( (int)$writerOptions['level'] );
                                $logger->addWriter( $firebugWriter );
                            }
                            break;

                        default:
                            try {
                                $logger->log( "Unknown log writer: {$writer}", Zend_Log::WARN );
                            } catch( Zend_Log_Exception $e ) {
                                die( "Unknown log writer [{$writer}] during application bootstrap" );
                            }
                            break;
                    }
                }

            }
            else
            {
                $logger->addWriter( new Zend_Log_Writer_Null() );
            }

            try
            {
                //$logger->log( 'Logger instantiated', Zend_Log::INFO );
            }
            catch( Zend_Log_Exception $e )
            {
                die( "Unknown log writer [{$writer}] during application bootstrap" );
            }

            $this->_logger = $logger;
        }

        return $this->_logger;
    }

}
