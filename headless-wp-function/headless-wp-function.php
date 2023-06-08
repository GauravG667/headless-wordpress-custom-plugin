<?php
/**
 * Plugin Name: Headless Wordpress Function
 * Plugin URI:
 * Description: Do not delete this plugin if your delete this it's make all breaking front site
 * Version: 1.0.0
 * Author: E2M Solutions
 * Author URI:
 * License: GPL2
 */

include_once ABSPATH . "wp-admin/includes/plugin.php";

if (is_plugin_active("headless-wp-function/headless-wp-function.php")) {
    function MyHeadlessCustomFunction()
    {
        if (function_exists("acf_add_options_page")) {
            acf_add_options_page([
                "page_title" => "Theme General Settings",
                "menu_title" => "Theme Settings",
                "menu_slug" => "theme-general-settings",
                "capability" => "edit_posts",
                "show_in_graphql" => true,
                "redirect" => false,
            ]);

            acf_add_options_sub_page([
                "page_title" => "Theme Header Settings",
                "menu_title" => "Header",
                "parent_slug" => "theme-general-settings",
                "show_in_graphql" => true,
            ]);

            acf_add_options_sub_page([
                "page_title" => "Theme Footer Settings",
                "menu_title" => "Footer",
                "parent_slug" => "theme-general-settings",
                "show_in_graphql" => true,
            ]);

            acf_add_options_sub_page([
                "page_title" => "Common Settings",
                "menu_title" => "Common",
                "parent_slug" => "theme-general-settings",
                "show_in_graphql" => true,
            ]);
        }

        function cc_mime_types($mimes)
        {
            $mimes["svg"] = "image/svg+xml";
            return $mimes;
        }
        add_filter("upload_mimes", "cc_mime_types");

        /******* Disable Wordpress gutenberg Editor ******/
        add_filter("use_block_editor_for_post", "__return_false", 10);

        // Disables the block editor from managing widgets in the Gutenberg plugin.
        add_filter("gutenberg_use_widgets_block_editor", "__return_false");

        // Disables the block editor from managing widgets.
        add_filter("use_widgets_block_editor", "__return_false");

        // add_filter( 'rest_authentication_errors', function(){
        //     wp_set_current_user( 1 ); // replace with the ID of a WP user with the authorization you want
        // }, 101 );

        add_filter("excerpt_more", "__return_empty_string");

        class Headless_GravityForms
            {
            public $rest_base = 'gf/forms';

            public function __construct($namespace)
            {
                /**
                 * @api {get} /glamrock/v1/gf/forms/1
                 * @apiName GetForm
                 * @apiGroup GravityForms
                 * @apiDescription Retreive a single form
                 * @apiParam {Number} form_id ID of the form
                 *
                 * @apiSuccess {Object[]} GF_Form Object (excluding notifications)
                 */
                register_rest_route($namespace, $this->rest_base . '/(?P<form_id>[\d]+)', [
                [
                    'methods' => WP_REST_Server::READABLE,
                    'callback' => [$this, 'get_form'],
                    'args' => [
                    'context' => [
                        'default' => 'view',
                    ],
                    ],
                ],
                ]);
            }

            /**
             * Retreive a single form and all fields and options (exluding notifications)
             * @param WP_REST_Request $request
             * @return WP_Error|WP_REST_Response
             */
            public function get_form(WP_REST_Request $request)
            {
                $form_id = $request['form_id'];
                $form = GFAPI::get_form($form_id);

                if ($form) {
                // Strip data we do not want to share
                unset($form['notifications']);

                return new WP_REST_Response($form, 200);
                } else {
                return new WP_Error('not_found', 'Form not found', ['status' => 404]);
                }
            }

        }

        /**
         * Register custom API routes
         */
        add_action('rest_api_init', function () {
            $api_namespace = 'glamrock/v1';
            new Headless_GravityForms($api_namespace);
        });
    }

    add_action("plugins_loaded", "MyHeadlessCustomFunction");
}
?>