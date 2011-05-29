<?php
/*
Plugin Name: Get post
Plugin URI: http://wordpress.org/extend/plugins/get-post/
Description: Add parser functions for getting specific posts
Author: James Tatum
Version: 1.0.2
Author URI: http://thelightness.blogspot.com
*/
?>
<?php
/*  Copyright 2008  James Tatum  (email : jtatum@gmail.com)

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
?>
<?php
if (!class_exists('JamesGetPost'))
{
    class JamesGetPost
    {
        function JamesGetPost()
        {
        }

        function get_post($tag='')
        {
            global $wp_query, $post, $id;
            $temp_query = clone $wp_query;
            $temp_post = clone $post;
            $temp_id = $id;
            $tag=htmlentities($tag);
            $myq = new WP_Query("tag=$tag&showposts=1");
            if ( $myq->have_posts() ) while ( $myq->have_posts() )
            {
                $myq->the_post();
                $pc='<div class="post" id="post-'.$post->ID.'">';
                $pc.='<h2><a href="'.get_permalink().'" rel="bookmark">'.htmlentities($post->post_title).'</a></h2>';
                $pc.='<div class="entry">';
                $pc.=$post->post_content;
                $pc.='</div>';
                $pc.='</div>';
            }
            $wp_query = clone $temp_query;
            $post = clone $temp_post;
            $id = $temp_id;
            return $pc;
        }

        function read_params($params)
        {
            $re = '/tag="(?<tag>.+)"/';
            preg_match($re, $params, $matches);
            $tag = $matches['tag'];
            // Post content would be here
            return $this->get_post($tag);
        }
        function scan_content($content = '')
        {
            $re = '/(?<str>\[get\-post(?<params>.*)\])/';
            if (preg_match($re, $content, $matches))
            {
                $params = $matches['params'];
                $replace = $matches['str'];
                // Post content would be here
                $postcontent = $this->read_params($params);
                $content = str_replace($replace, $postcontent, $content);
            }
            return $content;
        }
    }

}

if (class_exists('JamesGetPost'))
{
    $getpostplugin = new JamesGetPost();
}

if (isset($getpostplugin))
{
    add_filter('the_content', array($getpostplugin, 'scan_content'));
}

?>
