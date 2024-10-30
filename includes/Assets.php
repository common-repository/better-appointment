<?php

namespace BetterAppointment;

if(!defined('ABSPATH')) exit;

/**
 * Scripts and Styles Class
 */

class Assets
{
    protected $path;
    protected $url;

    function __construct($file)
    {
        $this->path = dirname($file);
        $this->url = plugins_url('', $file);

        if(is_admin())
        {
            add_action('admin_enqueue_scripts', [$this, 'register'], 5);
        } else {
            add_action('wp_enqueue_scripts', [$this, 'register'], 5);
        }
    }

    /**
     * Register our app scripts and styles
     *
     * @return void
     */
    public function register()
    {
        $this->register_scripts($this->get_scripts());
        $this->register_styles($this->get_styles());
    }

    /**
     * Register scripts
     *
     * @param  array $scripts
     *
     * @return void
     */
    protected function register_scripts($scripts)
    {
        foreach($scripts as $handle => $script)
        {
            $deps = isset($script['deps']) ? $script['deps'] : false;
            $in_footer = isset($script['in_footer']) ? $script['in_footer'] : false;
            $version = isset($script['version']) ? $script['version'] : false;
            wp_register_script($handle, $script['src'], $deps, $version, $in_footer);
        }
    }

    /**
     * Register styles
     *
     * @param  array $styles
     *
     * @return void
     */
    public function register_styles($styles)
    {
        foreach($styles as $handle => $style)
        {
            $deps = isset($style['deps']) ? $style['deps'] : false;
            $version = isset($style['version']) ? $style['version'] : false;
            wp_register_style($handle, $style['src'], $deps, $version);
        }
    }

    /**
     * Get all registered scripts
     *
     * @return array
     */
    public function get_scripts()
    {
        $prefix = defined('SCRIPT_DEBUG') && SCRIPT_DEBUG ? '.min' : '';

        if(!empty(get_option('ba_recaptcha_site_key')))
        {
            $scripts['recaptcha'] = [
                'src' => 'https://www.google.com/recaptcha/api.js?render=' . get_option('ba_recaptcha_site_key'),
                'in_footer' => true,
            ];
        }

        $scripts = [
            'better-appointment-admin' => [
                'src' => $this->url . '/assets/js/admin.js',
                'deps' => ['jquery'],
                'version' => filemtime($this->path . '/assets/js/admin.js'),
                'in_footer' => true,
            ],
            'better-appointment-frontend' => [
                'src' => $this->url . '/assets/js/frontend.js',
                'deps' => (!empty(get_option('ba_recaptcha_site_key')))
                    ? ['jquery', 'recaptcha']
                    : ['jquery'],
                'version' => filemtime($this->path . '/assets/js/frontend.js'),
                'in_footer' => true,
            ],
        ];

        return $scripts;
    }

    /**
     * Get registered styles
     *
     * @return array
     */
    public function get_styles()
    {
        $styles = [
            'better-appointment-frontend' => [
                'src' => $this->url . '/assets/css/frontend.css',
            ],
            'better-appointment-admin' => [
                'src' => $this->url . '/assets/css/admin.css',
            ],
        ];

        return $styles;
    }
}