<?php

/**
* Hook in to new uninstall mechanism.
* Wordpress should be able to delete the files, but we need to tell it about
* the options we're storing.
*/
if ( !defined('WP_UNINSTALL_PLUGIN') ) {
    exit();
}
delete_option('get_post_options');