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

class OSS_Debug
{

   /**
    * This function will 'dump and die' - it will (if HTML) surround the
    * output with <pre> tags.
    *
    * The dump command is Zend_Debug::dump()
    *
    *
    * @param object $object The variable / object to dump
    * @param bool $html If true (default) surround the output with <pre> tags
    * @author Barry O'Donovan <barry@opensolutions.ie> 20091114
    */
    public static function dd( $object, $html = true )
    {
        if( $html ) echo '<pre>';
        Zend_Debug::dump( $object );
        if( $html ) echo '</pre>';
        die();
    }


    /**
    * A wrapper and extension for print_r(). The output looks the same in the browser as the output of print_r() in the source, as it turns the pure
    * text output of print_r() into HTML (XHTML).
    *
    * @param mixed $data the data to be printed or returned
    * @param mixed $var_name null if we don't want to display the variable name, otherwise the name of the variable
    * @param boolean $return default false; if true it returns with the result, if true then prints it
    * @param boolean $pAddPre default true adds the '<pre> ... </pre>' tags to the output, useful for HTML output
    * @param boolean $pAddDollarSign default true adds a $ sign to the $var_name if it is set to true
    * @return mixed void (null) or a string
    */
    public static function prr($data, $var_name=null, $return=false, $pAddPre=true, $pAddDollarSign=true)
    {
        $vRetVal =  ($pAddPre == true ? "\n<pre>\n" : '') .
                    ($var_name == '' ? '' : ($pAddDollarSign == true ? "\$" : '') . "{$var_name} = ") .
                    print_r($data, true) .
                    ($pAddPre == true ? "\n</pre>\n" : '');


        if ($return === false)
            print $vRetVal;
        else
            return $vRetVal;
    }


    /**
    * Returns with a simplified, easier-to-read version of the result of debug_backtrace() as an associative array.
    *
    * @param void
    * @return array
    */
    public static function compact_debug_backtrace()
    {
        $res = debug_backtrace();
        $ret_val = array();

        foreach($res as $res_val)
        {
            $xyz = array();
            if (isset($res_val['file'])) $xyz['file'] = $res_val['file'];
            if (isset($res_val['line'])) $xyz['line'] = $res_val['line'];
            if (isset($res_val['function'])) $xyz['function'] = $res_val['function'];
            if (isset($res_val['class'])) $xyz['class'] = $res_val['class'];
            if (isset($res_val['object']->name)) $xyz['object'] = $res_val['object']->name;

            $ret_val[] = $xyz;
        }

        return $ret_val;
    }


    /**
    * Returns with the inheritance tree of $pClassOrObject, which can be a class name or an object.
    * It returns with a simple indexed array, where index 0 is the class of $pClassOrObject, and
    * index N is the name of the class at the end of the whole inheritance tree. If $pClassOrObject
    * is not a string or an object, then it returns with NULL.
    *
    * @param string|object $pClassOrObject a string class name or an object
    * @return array|null
    */
    public static function getInheritanceTree($pClassOrObject)
    {
        if ( (is_string($pClassOrObject) == false) && (is_object($pClassOrObject) == false) ) return null;

        $vClassList = array();
        $vClassList[] = get_class($pClassOrObject);
        $vParentClass = get_parent_class($pClassOrObject);

        while($vParentClass)
        {
            $vClassList[] = $vParentClass;
            $vParentClass = get_parent_class($vParentClass);
        }

        return $vClassList;
    }


    public static function log($pMessage)
    {
        $vMessage = date('Y-m-d H:i:s') . ' : ' . $pMessage . "\n";
        @file_put_contents('../var/tmp/' . date('Y-m-d') . '.log', $vMessage, FILE_APPEND | LOCK_EX);
    }

}
