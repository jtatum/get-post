<?php
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

if ( !class_exists('PluginGetPostGetter') )
{
    /**
    * This class does the actual work of getting the post. Create a new
    * instance, use the setters to set the option, and call get() to return
    * the post content.
    */
    class PluginGetPostGetter
    {
        private $tag = '';
        private $category = '';
        private $show_posts = 1;
        private $random = 0;
        private $template = '
        <div class="post" id="post-{id}">
            <h2><a href="{permalink}" rel="bookmark">{title}</a></h2>
            <div class="entry">{content}</div>
        </div>';

        /**
        * Set the desired tag
        */
        public function set_tag($tag)
        {
            $this->tag = $tag;
        }

        /**
        * Set the desired category
        */
        public function set_category($category)
        {
            $this->category = $category;
        }

        /**
        * Set the number of posts to show
        */
        public function set_show_posts($num)
        {
            $this->show_posts = intval($num);
        }

        /**
        * Set the template
        */
        public function set_template($template)
        {
            $this->template = $template;
        }

        /**
        * Display a random post/random posts with the specified settings
        */
        public function set_random($boolean)
        {
            $this->random = intval($boolean);
        }

        /**
        * Return the string needed for the query object given the various
        * options set. A string is used rather than an array because the query
        * seems to be more forgiving with strings - for instance, sometimes
        * specifying the full tag name rather than the stub works with the
        * query, but not with the array. At least, it used to.
        */
        public function build_query()
        {
            $query = "posts_per_page=$this->show_posts";
            if ( $this->tag != '' )
            {
                $query .= '&tag='.urlencode($this->tag);
            }
            if ( $this->category != '' )
            {
                $query .= '&category_name='.urlencode($this->category);
            }
            if ( $this->random )
            {
                $query .= '&orderby=rand';
            }
            return $query;
        }

        /**
        * Returns post HTML with the specified options set. Renders post(s)
        * using the nice template specified in the parameter.
        */
        public function get()
        {
            // If we're already running, let's just return an empty string
            // This would be a good sign that we've been called recursively.
            // Perhaps in the distant future someone will want this, and then
            // we can be smarter about whether we're being invoked recursively
            // or not.
            global $get_post_getter_running;
            if ( isset($get_post_getter_running) && $get_post_getter_running )
            {
                return '';
            }
            $get_post_getter_running = 1;
            // One would think that using a new instance of WP_Query would
            // make storing these variables unnecessary, but indeed it's
            // needed. Or it was in the last version of WP that I thoroughly
            // debugged. The docs for The Loop reference $wp_query but it turns
            // out that we also need to save the post object and the ID.
            global $wp_query, $post, $id, $authordata;
            $old_query = clone $wp_query;
            $old_post = clone $post;
            $old_author = clone $authordata;
            $old_id = $id;
            $my_query = new WP_Query($this->build_query());
            $content = '';
            // $content = $this->build_query();
            if ( $my_query->have_posts() ) while ( $my_query->have_posts() )
            {
                $my_query->the_post();
                // Retrieve post content and apply filters
                $post_content = $post->post_content;
                $post_content = apply_filters('the_content', $post_content);
                // Start with the template and substitute various data for
                // the tags
                $current = $this->template;
                $current = str_replace('{id}', $post->ID, $current);
                $current = str_replace('{permalink}', get_permalink(),
                    $current);
                $current = str_replace('{title}', $post->post_title, $current);
                $current = str_replace('{date}', get_the_date(), $current);
                $current = str_replace('{time}', get_the_time(), $current);
                $current = str_replace('{author}', get_the_author(), $current);
                $current = str_replace('{authorlink}',
                    get_author_posts_url($authordata->ID), $current);
                $current = str_replace('{content}', $post_content, $current);
                $content .= $current;
            }
            // $wp_query = clone $temp_query;
            // $post = clone $temp_post;
            $wp_query = $old_query;
            $post = $old_post;
            $authordata = $old_author;
            $id = $old_id;
            $get_post_getter_running = 0;
            return $content;
        }
    }
}
