<?php

// This Add-on's unique namespace
namespace WP2StaticBoilerplate;

// Iterating for Add-ons processing or deploying files
use RecursiveIteratorIterator;
use RecursiveDirectoryIterator;

class Boilerplate {

    private $an_encrypted_option;
    private $a_regular_option;

    public function __construct() {
        $this->an_encrypted_option = \WP2Static\CoreOptions::encrypt_decrypt(
            'decrypt',
            Controller::getValue( 'an_encrypted_option' )
        );
        $this->a_regular_option = Controller::getValue( 'boilerplateStorageZoneName' );

        $notice = 'Boilerplate class has been instantiated with' .
            "a regular option: $this->a_regular_option " .
            "and an encrypted option: $this->an_encrypted_option ";

        \WP2Static\WsLog( $notice );
    }

    /**
     * Upload processed StaticSite files
     *
     * This could be via a 3rd party API, local copy, ZIP, etc.
     * For deployment options without their own/good PHP library
     * a requests library, like Guzzle may be used (refer Netlify
     * or BunnyCDN deployment Add-ons for examples).
     */
    public function upload_files( string $processed_site_path ) : void {
        $notice = 'Boilerplate Add-on is simulating uploading files';
        \WP2Static\WsLog( $notice );

        if ( ! is_dir( $processed_site_path ) ) {
            return;
        }

        // iterate each file in ProcessedSite
        $iterator = new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator(
                $processed_site_path,
                RecursiveDirectoryIterator::SKIP_DOTS
            )
        );

        foreach ( $iterator as $filename => $file_object ) {
            $base_name = basename( $filename );
            if ( $base_name != '.' && $base_name != '..' ) {
                $real_filepath = realpath( $filename );

                $cache_key = str_replace( $processed_site_path, '', $filename );

                if ( \WP2Static\DeployCache::fileisCached( $cache_key ) ) {
                    continue;
                }

                if ( ! $real_filepath ) {
                    $err = 'Trying to deploy unknown file to Boilerplate: ' . $filename;
                    \WP2Static\WsLog::l( $err );
                    continue;
                }

                // Standardise all paths to use / (Windows support)
                // TODO: apply WP method of get_safe_path or such
                $filename = str_replace( '\\', '/', $filename );

                if ( ! is_string( $filename ) ) {
                    continue;
                }

                $remote_path = ltrim( $cache_key, '/' );

                // Note: Do your per-file or batch transfers here
                $result = true;

                if ( $result ) {
                    // Note: Add file path to DeployCache on successful transfer
                    \WP2Static\DeployCache::addFile( $cache_key );
                }
            }
        }
    }
}
