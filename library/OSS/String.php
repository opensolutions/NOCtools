<?php
/**
    Copyright (c) 2012, Open Source Solutions Limited, Dublin, Ireland
    All rights reserved.

    This file is part of the phpNOCtools package.

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
 * A class to provide string utility functions.
 *
 * @author Roland Huszti <roland@opensolutions.ie>
 * @category   OSS
 * @package    OSS_String
 * @copyright  Copyright (c) 2010 Open Source Solutions Limited <http://www.opensolutions.ie/>
 */
class OSS_String
{

   /**
    * The Unicode version of ucfirst().
    *
    * @param string $pString the input string
    * @param string $pEncoding default null the character encoding, if omitted the the PHP internal encoding is used
    * @return string
    */
    public static function mb_ucfirst($pString, $pEncoding=null)
    {
        if ( (function_exists('mb_strtoupper') == true) && (empty($pString) == false) )
        {
            if ($pEncoding === null) $pEncoding = mb_internal_encoding();

            return mb_strtoupper(mb_substr($pString, 0, 1, $pEncoding)) . mb_substr($pString, 1, mb_strlen($pString, $pEncoding));
        }
        else
        {
            return ucfirst($pString);
        }
    }


    /**
     * The Unicode version of ucwords().
     *
     * @param string $pString the input string
     * @param string $pEncoding default null the character encoding, if omitted the the PHP internal encoding is used
     * @return string
     */
    public static function mb_ucwords($pString, $pEncoding=null)
    {
        if ($pEncoding === null) $pEncoding = mb_internal_encoding();

        return mb_convert_case($pString, MB_CASE_TITLE, $pEncoding);
    }


    /**
     * The Unicode version of str_replace().
     *
     * @param string $needle      The string portion to replace in the haystack
     * @param string $replacement The replacement for the string portion
     * @param string $haystack    The haystack
     * @return string
     */
    public static function mb_str_replace( $needle, $replacement, $haystack )
    {
        $needle_len      = mb_strlen( $needle );
        $replacement_len = mb_strlen( $replacement );
        $pos             = mb_strpos( $haystack, $needle );

        while( $pos !== false )
        {
            $haystack = mb_substr( $haystack, 0, $pos ) . $replacement . mb_substr( $haystack, $pos + $needle_len );
            $pos = mb_strpos( $haystack, $needle, $pos + $replacement_len );
        }

        return $haystack;
    }


    /**
     * Generates a random string.
     *
     * @param int     $pLength     The length of the random string we want to generate. Default: 16
     * @param boolean $pLowerCase  If true then lowercase characters will be used. Default: true
     * @param boolean $pUpperCase  If true then uppercase characters will be used. Default: true
     * @param boolean $pNumbers    If true then numbers will be used. Default: true
     * @param string  $pAdditional These characters also will be used. Default: ''
     * @param string  $pExclude    These characters will be excluded. Default: '1iIl0O'
     * @return string The random string.
     */
    public static function random( $pLength=16, $pLowerCase = true, $pUpperCase = true, $pNumbers = true, $pAdditional = '', $pExclude = '1iIl0O' )
    {
        $vStr = '';

        if( $pLength == 0 )
            return '';

        if( $pLowerCase == true )
            $vStr .= 'abcdefghijklmnopqrstuvwxyz';

        if( $pUpperCase == true )
            $vStr .= 'ABCDEFGHIJKLMNOPQRSTUVWXYZ';

        if( $pNumbers == true )
            $vStr .= '0123456789';

        $vStr .= $pAdditional;

        if( $pExclude != '' )
        {
            foreach( str_split( $pExclude ) as $vExcludeChar )
            {
                $vStr = OSS_String::mb_str_replace( $vExcludeChar, '', $vStr );
            }
        }

        $vRepeat = ceil( ( 1 + ( $pLength / mb_strlen( $vStr ) ) ) );
        $vRetVal = substr( str_shuffle( str_repeat( $vStr, $vRepeat ) ), 1, $pLength );

        return $vRetVal;
    }


    /**
    * Returns with a random string using the characters found in $pCharSet only.
    *
    * @param string $pCharSet
    * @param int $pLength
    * @return string
    */
    public static function randomFromSet( $pCharSet, $pLength=16 )
    {
        $vRepeat = ceil( ( 1 + ( $pLength / mb_strlen( $pCharSet ) ) ) );
        return substr( str_shuffle( str_repeat( $pCharSet, $vRepeat ) ), 1, $pLength );
    }


    /**
    * Creates a random password string of a given length. Not the fastest way of generating random passwords, but ensures that it contains
    * both lowercase and uppercase letters and digits, so complies with our password strength "policy".
    *
    * Some letters are excluded from the character set: 1, 0, O, I, l
    *
    * @param int $length The length of the password to be generated.
    * @return string The password string.
    */
    public static function randomPassword($pLength = 8)
    {
        $vChars = "23456789abcdefghijkmnopqrstuvwxyz23456789ABCDEFGHJKLMNPQRSTUVWXYZ23456789";

        while(true)
        {
            $vPassword = substr(str_shuffle($vChars), 0, $pLength);

            // "/[a-zA-Z0-9]/" is NOT the same!
            if ( (preg_match("/[a-z]/", $vPassword)) && (preg_match("/[A-Z]/", $vPassword)) && (preg_match("/[0-9]/", $vPassword)) ) return $vPassword;
        }
    }


    /**
    * Takes a string, converts it to lowercase and creates a valid file name from it by replacing any
    * character by an underscore which is not [0-9a-z] (so only the standard neolatin (english) alphabet is
    * supported), then replaces any consecutive underscores with only one underscore. Leading and trailing
    * underscores are also removed.
    *
    * @param string $pString
    * @return string
    */
    public static function toValidFieldName($pString)
    {
        $vString = mb_strtolower(trim($pString));
        $vString = preg_replace("/[^0-9a-z]+/u", '_', $vString);
        $vString = preg_replace("/[_]+/u", '_', $vString);

        if ($vString[0] == '_') $vString = mb_substr($vString, 1);
        if (mb_substr($vString, -1) == '_') $vString = mb_substr($vString, 0, -1);

        return 'cf_' . $vString;
    }


    /**
    * Removes any diacritic, accent and combining characters from the string.
    *
    * These settings should be set for working results:
    * mb_language('uni');
    * mb_internal_encoding('UTF-8');
    * setlocale(LC_ALL, "en_IE.utf8"); //or any other locale, as long as it's utf8
    *
    * @param string $pInput the original input string
    * @return string
    */
    public static function normalise($pInput)
    {
        iconv_set_encoding('internal_encoding', 'utf-8');
        iconv_set_encoding('input_encoding', 'utf-8');
        iconv_set_encoding('output_encoding', 'utf-8');

        /**
        * Special cases
        * AE
        * ae
        * U+00F0  ð   c3 b0   LATIN SMALL LETTER ETH
        * U+00D8  Ø   c3 98   LATIN CAPITAL LETTER O WITH STROKE
        * U+00F8  ø   c3 b8   LATIN SMALL LETTER O WITH STROKE
        * 00DF  ß  Latin Small Letter Sharp S (German)
        * 00DE  Þ  Latin Capital Letter Thorn (Icelandic)
        * 00FE  þ latin small letter thorn
        * Ł, ł, đ, Đ, €
        * @see http://www.utf8-chartable.de/
        */

        $from = array( "\xC3\x86", "\xC3\xA6", "\xC3\xB0", "\xC3\x98", "\xC3\xB8", "\xC3\x9F", "\xC3\x9E", "\xC3\xBE", "\xC5\x81", "\xC5\x82", "\xC4\x91", "\xC4\x90", "\xE2\x82\xAC");
        $to = array(   'AE',       'ae',       'd',        'O',        'o',        'ss',       'Th',       'th',       'L',        'l',        "d",        "D",        "EUR");

        $vRetVal = iconv('UTF-8', 'ASCII//TRANSLIT', str_replace($from, $to, $pInput)); // TRANSLIT does the whole job
        $vRetVal = preg_replace("/[^a-z]/", '', mb_strtolower($vRetVal));

        return $vRetVal;
    }

}
