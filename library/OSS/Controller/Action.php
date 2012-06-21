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
 * OSS's version of Zend's Zend_Controller_Action implemented custom functionality.
 *
 * All application controlers subclass this rather than Zend's version directly.
 *
 * @author Barry O'Donovan <barry@opensolutions.ie>
 * @category   OSS
 * @package    OSS_Controller
 * @copyright  Copyright (c) 2010 Open Source Solutions Limited <http://www.opensolutions.ie/>
 *
 */
class OSS_Controller_Action extends Zend_Controller_Action
{

   /**
    * A variable to hold an instance of the bootstrap object
    *
    * @var object An instance of the bootstrap object
    */
    protected $_bootstrap;

    /**
    * A variable to hold an instance of the configuration object
    *
    * @var object An instance of the configuration object
    */
    protected $_config = null;

    /**
    * Zend_Config_Ini of device hostnames / IP addresses to populate dropdowns with / etc
    *
    * @var object Zend_Config_Ini of device hostnames / IP addresses to populate dropdowns with / etc
    */
    protected $_devices = null;

    /**
     * A variable to hold the invoked controller name
     *
     * @var string The invoked controller name
     */
    protected $_controller = null;

    /**
     * A variable to hold the invoked action name
     *
     * @var string The invoked action name
     */
    protected $_action = null;

    /**
    * A variable to hold an instance of the logger object
    *
    * @var object An instance of the logger object
    */
    static private $_logger = null;

    /**
     * A variable to hold the mailer
     *
     * @var object An instance of the mailer
     */
    private $_mailer = null;

    /**
     * A variable to hold the session namespace
     *
     * @var object An instance of the session namespace
     */
    private $_session = null;

    /**
     * @var array an array representation of the application.ini
     */
    protected $_options = null;

    /**
    * Override the Zend_Controller_Action's constructor (which is called
    * at the very beginning of this function anyway).
    *
    * @param object $request See Parent class constructor
    * @param object $response See Parent class constructor
    * @param object $invokeArgs See Parent class constructor
    */
    public function __construct(
        Zend_Controller_Request_Abstract  $request,
        Zend_Controller_Response_Abstract $response,
        array $invokeArgs = null )
    {
        // get the bootstrap object
        $this->_bootstrap = $invokeArgs['bootstrap'];
        Zend_Registry::set( 'bootstrap', $this->_bootstrap );

        $this->_options = $this->_bootstrap->getOptions();

        // and from the bootstrap, we can get other resources:

        $this->_config   = $this->_bootstrap->getResource('config');

        // Smarty must be set during bootstrap
        try
        {
            $this->view = $this->createView();

            $this->view->options = $this->_options;
            $this->view->session = $this->getSessionNamespace();
            $this->view->APPLICATION_PATH = APPLICATION_PATH;
        }
        catch( Zend_Exception $e )
        {
            echo "Caught exception: " . get_class( $e ) . "\n";
            echo "Message: " . $e->getMessage() . "\n";

            die( "\n\nYou must set-up Smarty in the bootstrap code.\n\n" );
        }


        // $this->view->addHelperPath('OSS/View/Helper', 'OSS_View_Helper');

        // call the parent's version where all the Zend magic happens
        parent::__construct( $request, $response, $invokeArgs );

        $this->view->controller = $this->_controller = $this->getRequest()->getControllerName();
        $this->view->action     = $this->_action     = $this->getRequest()->getActionName();

        $this->view->doctype( 'HTML5' );
        $this->view->headMeta()->appendHttpEquiv('Content-Type', 'text/html;charset=utf-8');

        // FIXME for ajax requests, we shouldn't even bother with Smarty
        if( substr( $this->getRequest()->getActionName(), 0, 4 ) == 'ajax'
                || substr( $this->getRequest()->getActionName(), 0, 3 ) == 'img'
                || substr( $this->getRequest()->getActionName(), 0, 3 ) == 'cli' )
            Zend_Controller_Action_HelperBroker::removeHelper( 'viewRenderer' );

        if( substr( $this->getRequest()->getActionName(), 0, 3 ) == 'cli' && PHP_SAPI != 'cli' )
        {
            die( "Attempt to access CLI function from non-CLI SAPI\n" );
        }

        // if we issue a redirect, we want it to exit immediatly
        $this->getHelper( 'Redirector' )->setExit( true );

        // load device / node array
        if( is_readable( APPLICATION_PATH . '/configs/devices.ini' ) )
        {
            $this->_devices = new Zend_Config_Ini( APPLICATION_PATH . '/configs/devices.ini' );

            $this->view->devices_ini = $this->_devices;
            $this->view->_devices = $this->_devices->devices;
        }

        // make sure the controller is enabled
        if( isset( $this->_options['controller'][$this->_controller]['enabled'] ) && !$this->_options['controller'][$this->_controller]['enabled'] )
        {
            $this->addMessage( 'The requested component has been disabled by the administrator.', OSS_Message::ERROR );
            $this->_redirect();
            die();
        }
    }


    /**
     * A utility method to get a named resource.
     *
     * @param string $resource
     */
    public function getResource( $resource )
    {
        return $this->_bootstrap->getResource( $resource );
    }


    /**
    * Creates and returns with a new view object.
    *
    * @param void
    * @return object
    */
    public function createView()
    {
        $view = $this->_bootstrap->getResource( 'smarty' );

        $view->pagebase = '';

        if( isset( $_SERVER['SERVER_NAME'] ) )
            $view->pagebase = 'http' . ( isset( $_SERVER['HTTPS'] ) ? 's' : '' ) . '://'
                . $_SERVER['SERVER_NAME']
                . Zend_Controller_Front::getInstance()->getBaseUrl();

        $view->basepath = Zend_Controller_Front::getInstance()->getBaseUrl();

        return $view;
    }

    /**
     * Bootstraps and returns a Zend_Mail object
     *
     * @return Zend_Mail The Zend_Mail object
     */
    public function getMailer()
    {
        if( $this->_mailer === null )
            $this->_mailer   = $this->_bootstrap->getResource('mailer');

        $mailer = new Zend_Mail();
        $mailer->setMessageId( true );
        return $mailer;
    }

    /**
     * Load a configuration value
     *
     * @param string $key The associate array key to get from the config array
     * @return mixed The configuration value for the given key
     */
    public function getConfigValue( $key )
    {
        return $this->_config[$key];
    }

    /**
    * Adds a message to the session. Useful when you need a message to be displayed after a _redirect(), which normally gets rid of all messages as the messages by default
    * go to a view variable, while this goes into the session, and the Smarty function will clear it out just after showing the message.
    *
    * @param string $pMessage the message text
    * @param string $pClass the message class, OSS_Message::INFO|ALERT|SUCCESS|ERROR|...
    * @return void
    */
    public function addMessage( $message, $class, $type = OSS_Message::TYPE_MESSAGE )
    {
        $msg = null;

        switch( $type )
        {
            case OSS_Message::TYPE_BLOCK:
                $msg = new OSS_Message_Block( $message, $class );
                break;

            default:
                $msg = new OSS_Message( $message, $class );
        }

        $this->getSessionNamespace()->OSS_Messages[] = $msg;
        return $msg;
    }


    /**
    * Adds messages to the session.
    *
    * @see addMessage
    * @param string $pMessagesArray the array of messages
    * @param string $pClass the message class, OSS_Message::INFO|ALERT|SUCCESS|ERROR|...
    * @return void
    */
    public function addMessages( $messages, $class, $type = OSS_Message::TYPE_MESSAGE )
    {
        if( !is_array( $messages ) )
            $messages = array( $messages );

        foreach( $messages as $msg )
            $this->addMessage( $msg, $class, $type );
    }



    /**
     * Get the namespace (session).
     *
     * @return Zend_Session_Namespace The session namespace.
     */
    protected function getSessionNamespace()
    {
        if( $this->_session === null )
        {
            $this->_session  = $this->_bootstrap->getResource('namespace');

            // add to the view also
            $this->view->session = $this->_session;
        }

        // add a random ID for this user
        if( !isset( $this->_session->userRndId ) )
            $this->_session->userRndId = OSS_String::random();

        return $this->_session;
    }


    /**
     * Returns an instance of the Logger resource
     *
     * @return Zend_Log The Zend logger
     */
    public static function getLoggerStatic()
    {
        if( self::$_logger === null )
            self::$_logger = Zend_Registry::get( 'bootstrap' )->getResource( 'logger' );

        return self::$_logger;
    }

    /**
     * Returns the logger object
     *
     * @return Zend_Log The Zend_Log object
     */
    public function getLogger()
    {
        if( self::$_logger === null )
            return self::getLoggerStatic();

        return self::$_logger;
    }

    /**
     * Echo a message to the screen during CLI operations
     * @param string $msg The message to print
     */
    protected function cliVerbose( $msg )
    {
        if( $this->_getParam( 'cli-verbose', false ) )
            echo '[' . date( 'Y-m-d H:i:s' ) . "] {$msg}\n";
    }


    /**
     * Create a file name for a graph via controller / action and some other parameters.
     *
     */
    protected function generateGraphFilename( $params )
    {
        $n = 'dotimg-' . $this->_controller . '-' . $this->_action;

        if( isset( $this->_session->userRndId ) )
            $n .= '-' . $this->_session->userRndId;

        foreach( $params as $p )
            $n .= '-' . $p;

        return $n;
    }

    protected function generateDotGraph( $file, $dot, $tmp = null )
    {
        if( $tmp === null )
            $tmp = APPLICATION_PATH . '/../var/tmp';

        // auto clean up
        $this->autoCleanGraphFiles( $tmp, 'dotimg-' );

        $dotFile = "{$tmp}/{$file}.dot";
        $pngFile = "{$tmp}/{$file}.png";

        if( is_readable( $dotFile ) && file_get_contents( $dotFile ) == $dot )
            return $pngFile;

        file_put_contents( $dotFile, $dot );

        system( escapeshellcmd( "{$this->_options['cmd']['dot']} -T png -o {$pngFile} {$dotFile}" ) );

        return $pngFile;
    }


    protected function autoCleanGraphFiles( $tmp, $prefix )
    {
        $files = scandir( $tmp );

        if( $files && count( $files ) )
        {
            foreach( $files as $file )
            {
                if( is_file( "{$tmp}/{$file}" ) && is_writable( "{$tmp}/{$file}" ) && substr( $file, 0, strlen( $prefix ) ) == $prefix )
                {
                    if( time() - @stat( "{$tmp}/{$file}" )['atime'] > 60 * 60 * 24 )
                        @unlink( "{$tmp}/{$file}" );
                }
            }
        }
    }

}
