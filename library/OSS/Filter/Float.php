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
 * Filter to remove characters that wouldn't be in a floating point number
 *
 * Specifically, it removes anything not in the class [0123456789\.\-]
 *
 * @author Barry O'Donovan <barry@opensolutions.ie>
 * @category   OSS
 * @package    OSS_Filter
 * @copyright  Copyright (c) 2009 Open Source Solutions Limited <http://www.opensolutions.ie/>
 *
 */
class OSS_Filter_Float implements Zend_Filter_Interface
{

    /**
    *
    */
    public function filter($value)
    {
        return preg_replace( "/[^0123456789\.\-]/", '', (string) $value );
    }

}
