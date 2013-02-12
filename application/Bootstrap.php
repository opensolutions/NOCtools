<?php

/*
    Copyright (c) 2012, Open Source Solutions Limited, Dublin, Ireland
    All rights reserved.

    Contact: Barry O'Donovan - barry (at) opensolutions (dot) ie
             http://www.opensolutions.ie/

    This file is part of the NOCtools package.

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

class Bootstrap extends Zend_Application_Bootstrap_Bootstrap
{


    /**
     * Register the OSS library autoloader
     *
     * This function ensures that classes from library/OSS are automatically
     * loaded from the subdirectories where subdirectories are indicated by
     * underscores in the same manner as Zend.
     *
     */
    protected function _initOSSAutoLoader()
    {
        $autoloader = Zend_Loader_Autoloader::getInstance();
        $autoloader->registerNamespace( 'OSS' );
    }


    /**
     * Register the NOCtools library autoloader
     *
     * This function ensures that classes from library/NOCtools are automatically
     * loaded from the subdirectories where subdirectories are indicated by
     * underscores in the same manner as Zend.
     *
     */
    protected function _initNOCtoolsAutoLoader()
    {
        $autoloader = Zend_Loader_Autoloader::getInstance();
        $autoloader->registerNamespace( 'NOCtools' );
    }


}
