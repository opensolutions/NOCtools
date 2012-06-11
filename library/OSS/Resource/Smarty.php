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
 * Class to instantiate View
 *
 * @category OSS
 * @package OSS_Bootstrap_Resources
 * @copyright Copyright (c) 2009 Open Source Solutions Limited <http://www.opensolutions.ie/>
 */
class OSS_Resource_Smarty extends Zend_Application_Resource_ResourceAbstract
{

    /**
     * Holds the View instance
     *
     * @var
     */
    protected $_view;

    public function init()
    {
        // Return view so bootstrap will store it in the registry
        return $this->getView();
    }

    public function getView()
    {
        // Get session configuration options from the application.ini file
        $options = $this->getOptions();

        if( $options['enabled'] )
        {
            if( null === $this->_view ) // this cannot be &&'d with the above!
            {
                require_once( 'Smarty' . DIRECTORY_SEPARATOR . 'Smarty.class.php' );

                // Create directories of necessary
                if( !file_exists( $options['cache'] ) )
                {
                    mkdir( $options['cache'], 0770, true );
                    chmod( $options['cache'], 0770       );
                }

                if( !file_exists( $options['compiled'] ) )
                {
                    mkdir( $options['compiled'], 0770, true );
                    chmod( $options['compiled'], 0770       );
                }

                // Initialize view
                $view = new OSS_View_Smarty(
                    $options['templates'],
                    array(
                        'cache_dir'   => $options['cache'],
                        'config_dir'  => $options['config'],
                        'compile_dir' => $options['compiled'],
                        'plugins_dir' => $options['plugins']
                    )
                );

                $view->getEngine()->debugging = $options['debugging'];

                // Add it to the ViewRenderer
                $viewRenderer = Zend_Controller_Action_HelperBroker::getStaticHelper( 'ViewRenderer' );
                $viewRenderer->setView( $view );

                $this->_view = $view;
            }

            $this->_view->OSS_Messages = array();

            return $this->_view;
        }

    }
}
