<?php
/*
Plugin Name: Get post
Plugin URI: http://wordpress.org/extend/plugins/get-post/
Description: Get the content of post(s) matching criteria and use them in
other pages or posts via the [get-post] tag. Full documentation is available
on the plugin site.
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
* The non-reusable components specific to this plugin are in PluginGetPost.
* Remember to set DEBUG in wp-config.php if making changes, particularly to
* the parser.
*/

include_once dirname( __FILE__ ) . '/class-get-post-parser.php';
include_once dirname( __FILE__ ) . '/class-get-post-getter.php';

if ( !class_exists('PluginGetPostError') )
{
    /**
    * Exception class. This will be raised if there's an error to be displayed
    * to the user rather than a successful invocation
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
            $global_options = get_option('get_post_options');
            $post_getter->set_template($global_options['template']);
            $errors = '';
            // if ( count($options) == 0 )
            // {
            //  throw new PluginGetPostError(__('No options specified'));
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
                    case 'template':
                        $post_getter->set_template($value);
                        break;
                    default:
                        $errors .= __('Unknown option:') . " $key ";
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
                    unset($post_getter);
                    try {
                        $post_getter = $this->read_params($params);
                    } catch (PluginGetPostError $e)
                    {
                        $error_string = __('Get-post encountered an error:');
                        $message = $e->getMessage();
                        $result = "<span style=\"color:red;\">$error_string
                            $message</span>";
                    }
                    if ( isset($post_getter) )
                    {
                        $result = $post_getter->get();
                    }
                    // Replace the tag with the result of the call
                    $content = str_replace($replace, $result, $content);
                }
            }
            return $content;
        }

        /**
        * Setup default options
        */
        public function setup_defaults()
        {
            $options = get_option('get_post_options');
            $updated = 0;
            if ( !isset($options) || !is_array($options) )
            {
                $options = array();
            }
            if ( !isset($options['template']) )
            {
                $options['template'] = <<<EOT
<div class="post" id="post-{id}">
    <h2 class="entry-title"><a href="{permalink}" rel="bookmark">{title}</a></h2>
    <div class="entry-meta">Posted on <a href="{permalink}" title="{time}"
        rel="bookmark">{date}</a> by <a href="{authorlink}">{author}</a></div>
    <div class="entry">{content}</div>
</div>
EOT;
                $updated = 1;
            }
            if ( $updated == 1 )
            {
                update_option('get_post_options', $options);
            }
        }

        /**
        * Register the admin page
        */
        public function register_admin_page()
        {
            add_options_page('Get-post plugin options', 'Get-post',
                'manage_options', 'get-post',
                array($this, 'display_admin_page'));
            add_filter('plugin_action_links', array($this,
                'plugin_actions'), 10, 2);
        }

        /**
        * Initialize admin page, defining settings
        */
        public function init_admin_page()
        {
            register_setting('get_post_options', 'get_post_options',
                array($this, 'options_validate'));
            add_settings_section('get_post_main', 'General Settings',
                array($this, 'display_main_section'), 'get_post_options');
            add_settings_field('template', 'Template',
                array($this, 'display_template_string'), 'get_post_options',
                'get_post_main');
        }

        /**
        * Display the admin page
        */
        public function display_admin_page()
        {
            ?>
            <div>
            <h2>Get-post</h2>
            <form action="options.php" method="post">
            <?php settings_fields('get_post_options'); ?>
            <?php do_settings_sections('get_post_options'); ?>

            <input name="Submit" type="submit"
                value="<?php esc_attr_e('Save Changes'); ?>" />
            </form></div>

            <?php
        }

        /**
        * Display main section text
        */
        public function display_main_section()
        {
            echo '<p>General settings controlling text rendering</p>';
        }

        /**
        * Display the template input field
        */
        public function display_template_string()
        {
            $options = get_option('get_post_options');
            ?>
            <textarea id='template' name='get_post_options[template]'
                cols='70' rows='6'><?php echo
                htmlEntities($options['template'], ENT_QUOTES);
                ?></textarea>
            <?php
        }

        /**
        * Validate the supplied options
        */
        public function options_validate($values)
        {
            // Echo, var_dump, nothing really works here
            // add_settings_error('get_post_options', 'value',
            //     var_export($values, 1), 'error');
            $validated_options = array();
            $validated_options['template'] =
                $values['template'];
            // add_settings_error('get_post_options', 'value',
            //     var_export($validated_options, 1), 'error');
            return $validated_options;
        }

        /**
        * Add a settings link to the plugins page
        */
        public function plugin_actions($links, $file)
        {
            static $this_plugin;
            if( ! $this_plugin )
            {
                $this_plugin = plugin_basename(__FILE__);
            }
            if( $file == $this_plugin )
            {
                $settings_link = '<a href="options-general.php?page=get-post">'
                    . __('Settings') . '</a>';
                array_unshift( $links, $settings_link );
            }
            return $links;
        }
    } // Class PluginGetPost
}

if ( class_exists('PluginGetPost') )
{
    $get_post_plugin = new PluginGetPost();
}

if ( isset($get_post_plugin) )
{
    // Register a new filter with WordPress to receive all post content
    add_filter('the_content', array($get_post_plugin, 'scan_content'));
    // Add settings page to admin menu
    add_action('admin_menu', array($get_post_plugin, 'register_admin_page'));
    add_action('admin_init', array($get_post_plugin, 'init_admin_page'));
    // Configure plugin defaults
    add_action('wp_loaded', array($get_post_plugin, 'setup_defaults'));
}
