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

class OSS_Log extends Zend_Log
{

    public function alert($pMessage)
    {
        $vMessage = $pMessage . "

           host : {$_SERVER['HTTP_HOST']}
     user agent : {$_SERVER['HTTP_USER_AGENT']}
    remote addr : {$_SERVER['REMOTE_ADDR']}:{$_SERVER['REMOTE_PORT']}
script filename : {$_SERVER['SCRIPT_FILENAME']}
 request method : {$_SERVER['REQUEST_METHOD']}
   query string : {$_SERVER['QUERY_STRING']}
    request uri : {$_SERVER['REQUEST_URI']}
";

        try
        {
            $this->log($vMessage, Zend_Log::ALERT);
        }
        catch(Exception $e)
        {
            $this->debug($e->getMessage());
        }
    }

}
