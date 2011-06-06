<?php
/*  Copyright 2011  James Tatum  (email : jtatum@gmail.com)

    This program is free software; you can redistribute it and/or modify
    it under the terms of the GNU General Public License as published by
    the Free Software Foundation; either version 2 of the License, or
    (at your option) any later version.

    This program is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.

    You should have received a copy of the GNU General Public License
    along with this program; if not, write to the Free Software
    Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
*/

if ( !class_exists('PluginGetPostParser') )
{
    /**
    * Parser for options passed in a parser tag type thing. The idea
    * is to take a parser tag like [tag a b=val c="multi word val"]
    * and to parse that out into an associative key-value array.
    */
    class PluginGetPostParser
    {
        /**
        * Read some options from a string and return an associative array.
        *
        * Test values:
        * ' tag="doesn't exist"'
        * ' tag=asdf'
        * ' category="Uncategorized"'
        * ' tag=1234 category="Some category" test test2'
        * '   '
        * ''
        */
        public function parse_params($params)
        {
            $options = array();
            $re_keys = '/[a-zA-Z0-9]/';
            // Loop through supplied params and put them in the $options
            // associative array.
            // This for loop doesn't go through each character - there is
            // a lot of manipulation of $i inside the loop, and several
            // while loops. Each nested while loop must take care to test
            // that the end of the string hasn't been reached.
            for ( $i=0; $i<strlen( $params ); $i++ )
            {
                $key = '';
                if ( preg_match($re_keys, $params[$i]) )
                {
                    // Read out a key
                    while ( $i < strlen($params) &&
                        preg_match($re_keys, $params[$i]) )
                    {
                        $key .= $params[$i];
                        $i++;
                    }
                }
                if ( $i == strlen($params) )
                {
                    // Reached the end of the params after reading a key
                    break;
                }
                if ( $params[$i] == ' ')
                {
                    // Space character - if we have a key, set it as a flag
                    if ( $key != '' )
                    {
                        $options[$key] = 1;
                    }
                    continue;
                }
                if ( $params[$i] == '=' )
                {
                    // We have a value. Let's handle it.
                    // Skip the =.
                    $i++;
                    $value = '';
                    if ( $params[$i] == '"' )
                    {
                        // Spaced value. Let's read data until we get to a
                        // closing quote.
                        // Skip the opening quote
                        $i++;
                        while ( $i < strlen($params) && $params[$i] != '"' )
                        {
                            $value .= $params[$i];
                            $i++;
                        }
                        // Skip the closing quote
                        $i++;
                        $options[$key] = $value;
                    } else {
                        // Must be an unspaced value.
                        while ( $i < strlen($params) && $params[$i] != ' ' )
                        {
                            $value .= $params[$i];
                            $i++;
                        }
                        $options[$key] = $value;
                    }
                }
            }
            // Done iterating. Handle a case where there's a flag at the end
            // and no space, such as [get-post test]
            if ( isset($key) && !isset($options[$key]) && $key != '' )
            {
                $options[$key] = 1;
            }
            return $options;
        }
    }
}
