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
                        'cache_dir'   => isset( $options['cache'] ) ? $options['cache'] : null,
                        'config_dir'  => isset( $options['config'] ) ? $options['config'] : null,
                        'compile_dir' => isset( $options['compiled'] ) ? $options['compiled'] : null,
                        'plugins_dir' => isset( $options['plugins'] ) ? $options['plugins'] : null
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
