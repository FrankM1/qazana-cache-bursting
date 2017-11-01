<?php
/*
Plugin Name: Qazana Cache bursting
Plugin URI: http://qazana.net
Description: Purge caches from various plugins when qazana pages are saved.
Author: Qazana
Author URI: http://qazana.net
Version: 1.0.0
License: GPL2
*/

if ( function_exists( 'fvm_purge_all' )) {
    add_action( 'qazana/editor/after_save', 'qcb_fvm_purge_all' );
} else {
    add_action( 'qazana/editor/after_save', 'qcb_fastvelocity_purge_others' );
}

function qcb_fvm_purge_all() {
    fvm_purge_all(); # purge all
    fastvelocity_purge_others(); # purge third party caches
}

function qcb_purge_others() {

    # wodpress default cache
    if (function_exists('wp_cache_flush')) {
        wp_cache_flush();
    }

    # Purge all W3 Total Cache
    if (function_exists('w3tc_pgcache_flush')) {
        w3tc_pgcache_flush();
        return __('All caches from W3 Total Cache have been purged.');
    }

    # Purge WP Super Cache
    if (function_exists('wp_cache_clear_cache')) {
        wp_cache_clear_cache();
        return __('All caches from WP Super Cache have been purged.');
    }

    # Purge WP Rocket
    if (function_exists('rocket_clean_domain')) {
        rocket_clean_domain();
        return __('All caches from WP Rocket have been purged.');
    }

    # Purge Wp Fastest Cache
    if (isset($GLOBALS['wp_fastest_cache']) && method_exists($GLOBALS['wp_fastest_cache'], 'deleteCache')) {
        $GLOBALS['wp_fastest_cache']->deleteCache();
        return __('All caches from Wp Fastest Cache have been purged.');
    }

    # Purge Cachify
    if (function_exists('cachify_flush_cache')) {
        cachify_flush_cache();
        return __('All caches from Cachify have been purged.');
    }

    # Purge Comet Cache
    if (class_exists("comet_cache")) {
        comet_cache::clear();
        return __('All caches from Comet Cache have been purged.');
    }

    # Purge Zen Cache
    if (class_exists("zencache")) {
        zencache::clear();
        return __('All caches from Sen Cache have been purged.');
    }

    # Purge LiteSpeed Cache 
    if (class_exists('LiteSpeed_Cache_Tags')) {
        LiteSpeed_Cache_Tags::add_purge_tag('*');
        return __('All caches from LiteSpeed Cache have been purged.');
    }

    # Purge SG Optimizer
    if (function_exists('sg_cachepress_purge_cache')) {
        sg_cachepress_purge_cache();
        return __('All caches from SG Optimizer have been purged.');
    }

    # Purge Godaddy Managed WordPress Hosting (Varnish + APC)
    if (class_exists('WPaaS\Plugin')) {
        qcb_godaddy_request('BAN');
        return __('All caches from WP Engine have been purged.');
    }

    # Purge WP Engine
    if (class_exists("WpeCommon")) {
        if (method_exists('WpeCommon', 'purge_memcached')) {
            WpeCommon::purge_memcached();
        }
        if (method_exists('WpeCommon', 'clear_maxcdn_cache')) {
            WpeCommon::clear_maxcdn_cache();
        }
        if (method_exists('WpeCommon', 'purge_varnish_cache')) {
            WpeCommon::purge_varnish_cache();
        }
    }
}

# Purge Godaddy Managed WordPress Hosting (Varnish)
# https://github.com/wp-media/wp-rocket/blob/master/inc/3rd-party/hosting/godaddy.php
function qcb_godaddy_request( $method, $url = null ) {
	$url  = empty( $url ) ? home_url() : $url;
	$host = parse_url( $url, PHP_URL_HOST );
	$url  = set_url_scheme( str_replace( $host, WPaas\Plugin::vip(), $url ), 'http' );
	wp_cache_flush();
	update_option( 'gd_system_last_cache_flush', time() ); # purge apc
	wp_remote_request( esc_url_raw( $url ), array('method' => $method, 'blocking' => false, 'headers' => array('Host' => $host)) );
}
