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
 * A Router for CLI Scripts
 *
 * See: http://webfractor.wordpress.com/2008/08/14/using-zend-framework-from-the-command-line/
 *
 */
class OSS_Controller_Router_Cli extends Zend_Controller_Router_Abstract implements Zend_Controller_Router_Interface
{

    public function assemble( $userParams, $name = null, $reset = false, $encode = true )
    { }

    public function route( Zend_Controller_Request_Abstract $dispatcher )
    {}

}
