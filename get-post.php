<?php
/*
Plugin Name: Get post
Plugin URI: http://wordpress.org/extend/plugins/get-post/
Description: Add parser functions for getting specific posts
Author: James Tatum
Version: 2.0.0
Author URI: http://thelightness.blogspot.com
*/
/*  Copyright 2008, 2011  James Tatum  (email : jtatum@gmail.com)

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

/**
* I tried to separate out reusable components into classes.
* The non-reusable components are in PluginGetPost. Remember to set DEBUG
* in wp-config.php if making changes, particularly to the parser.
*/

include_once dirname( __FILE__ ) . '/class-get-post-parser.php';
include_once dirname( __FILE__ ) . '/class-get-post-getter.php';

if ( !class_exists('PluginGetPostError') )
{
	/**
	* Exception class. This will be raised if something goes wrong in parsing
	* or getting. Caught errors should be displayed to the user.
	*/
	class PluginGetPostError extends Exception { }
}

if (!class_exists('PluginGetPost'))
{
	/**
	* Plugin class. This class contains all plugin related members.
	* The actual work is done in PluginGetPostGetter.
	*/
    class PluginGetPost
    {
		/**
		* Try to figure out what the user wants. This should either return
		* a configured PluginGetPostGetter object or raise an exception.
		*/
        private function read_params($params)
        {
			$parser = new PluginGetPostParser();
			$options = $parser->parse_params($params);
			// Create an object and pass it junk
			$post_getter = new PluginGetPostGetter();
			$errors = '';
			// if ( count($options) == 0 )
			// {
			// 	throw new PluginGetPostError('No options specified');
			// }
			foreach ( $options as $key => $value )
			{
				switch ( strtolower($key) )
				{
					case 'tag':
						$post_getter->set_tag($value);
						break;
					case 'category':
						$post_getter->set_category($value);
						break;
					case 'show':
						$post_getter->set_show_posts($value);
						break;
					case 'random':
						$post_getter->set_random(1);
						break;
					default:
						$errors .= "Unknown option: $key ";
						break;
				}
			}
			if ( $errors != '' )
			{
				throw new PluginGetPostError($errors);
			}
			return $post_getter;
        }

		/**
		* Scan post content. The plugin registers to see all post content, so
		* here's where we scan it and replace [get-post] tags with something
		* like the post they requested. Hopefully.
		*/
        public function scan_content($content = '')
        {
            $re = '/(\[get\-post(.*)\])/';
            if ( preg_match_all($re, $content, $matches, PREG_SET_ORDER) )
            {
				foreach ($matches as $match)
				{
	                $params = $match[2];
	                $replace = $match[1];
					try {
		                $post_getter = $this->read_params($params);
					} catch (PluginGetPostError $e)
					{
						$result = '<span style="color:red;">Get-post
						encountered an error: </span>';
						$result .= $e->getMessage();
					}
					$result = $post_getter->get();
					// Replace the tag with the result of the call
	                $content = str_replace($replace, $result, $content);
				}
            }
            return $content;
        }
    }
}

if ( class_exists('PluginGetPost') )
{
    $get_post_plugin = new PluginGetPost();
}

if ( isset($get_post_plugin) )
{
	// Register a new filter with WordPress to receive all post content
    add_filter('the_content', array($get_post_plugin, 'scan_content'));
}
