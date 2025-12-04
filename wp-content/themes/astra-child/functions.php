<?php
/**
 * Astra Child Theme functions and definitions
 *
 * @link https://developer.wordpress.org/themes/basics/theme-functions/
 *
 * @package Astra Child
 * @since 1.0.0
 */

/**
 * Define Constants
 */
define( 'CHILD_THEME_ASTRA_CHILD_VERSION', '1.0.0' );

/**
 * Enqueue styles
 */
function child_enqueue_styles() {

	wp_enqueue_style( 'astra-child-theme-css', get_stylesheet_directory_uri() . '/style.css', array('astra-theme-css'), CHILD_THEME_ASTRA_CHILD_VERSION, 'all' );
    wp_enqueue_style( 'astra-child-theme-css-custom1', get_stylesheet_directory_uri() . '/style-custom1.css', array('astra-theme-css'), CHILD_THEME_ASTRA_CHILD_VERSION, 'all' );
    wp_enqueue_style( 'astra-child-theme-css-custom2', get_stylesheet_directory_uri() . '/style-custom2.css', array('astra-theme-css'), CHILD_THEME_ASTRA_CHILD_VERSION, 'all' );
    //wp_enqueue_script('dist-data', get_stylesheet_directory_uri() . '/assets/js/psapolydata.js', array('jquery'), null, true);
}

add_action( 'wp_enqueue_scripts', 'child_enqueue_styles', 15 );

function ppd_search_javascript() {
    ?>
    <script>
        jQuery(document).ready(function() {
          jQuery("#search-icon-ppd").click(function(e) {
            if(jQuery("#header-search-ppd-container").is(":visible")){
                jQuery("#header-search-ppd-container").hide();  
            } else {
                jQuery("#header-search-ppd-container").show();
            }
          });

          jQuery("#header-search-ppd-container").mouseenter(function() {
            jQuery(this).show();
          });
          jQuery("#close-search-box").click(function(e) {
            if(jQuery("#header-search-ppd-container").is(":visible")){
                jQuery("#header-search-ppd-container").hide();  
            }
          });
        });
    </script>
    <?php
}
add_action('wp_head', 'ppd_search_javascript');


//District wise User for event post
function custom_enqueue_admin_scripts($hook) {
    if ($hook === 'post-new.php' && isset($_GET['post_type']) && $_GET['post_type'] === 'district_events') {
      $user_info = get_userdata(get_current_user_id()); 
      $user_id = get_current_user_id();
      $userRelation= get_field('user_district_relation', "user_{$user_id}");
      // print_r($userRelation);
      // die();
      $first_name = $user_info->first_name;
      $user_role = $user_info->roles;
      // echo $first_name;
      // die();
      wp_enqueue_script('custom-admin-script', get_stylesheet_directory_uri() . '/assets/js/event-district.js', array('jquery'), null, true);
      
  wp_localize_script('custom-admin-script', 'customAdminData', array(
      'userdistrelation' => $userRelation,
      'firstName' => $first_name,
      'userRole' => $user_role
  ));
  }
}
add_action('admin_enqueue_scripts', 'custom_enqueue_admin_scripts');



function distselect_function() {
  if (is_page('neighborhood-concern-roll-call-complaint')) { ?>
     <script>
      jQuery(document).ready(function() {
       var params = new window.URLSearchParams(window.location.search);
       var district = params.get('dist');
       var selectDist = district.split(' ');   
       var actualDist = selectDist[0];
       if(actualDist){
        jQuery('.distSelectbox select').val(actualDist);
       } else {
          
       }
      });    
     </script>
 <?php }
}
add_action('wp_footer', 'distselect_function');


function replace_commissioner_name($content) {
  $old_name = 'commissioner_name';
  $commissioner_page_id = 11764; 
  $commissioner_name = get_field('meet_the_commissioner', $commissioner_page_id);
  $updated_content = str_replace($old_name, $commissioner_name, $content);
  return $updated_content;
}
add_filter('the_content', 'replace_commissioner_name');


// Get the full path to the districtsadmin.php file in a child theme
require_once get_stylesheet_directory() . '/include/districtsadmin.php';






function check_required_taxonomy_news_blotter($post_id, $post, $update) {
    $post_type = 'news-blotter'; 
    $taxonomy = 'news-blotter-cat';

    if ($post->post_type !== $post_type) {
        return;
    }

    $terms = wp_get_post_terms($post_id, $taxonomy);

    if (empty($terms) || is_wp_error($terms)) {
        remove_action('save_post', 'check_required_taxonomy_news_blotter', 10);
        
        if ($post->post_status === 'publish') {
            wp_update_post([
                'ID' => $post_id,
                'post_status' => 'draft'
            ]);
        }

        add_filter('redirect_post_location', function($location) {
            return add_query_arg('taxonomy_error', 1, $location);
        });

        add_action('save_post', 'check_required_taxonomy_news_blotter', 10, 3);
    }
}
add_action('save_post', 'check_required_taxonomy_news_blotter', 10, 3);

function taxonomy_selection_error_notice_news_blotter() {
    if (isset($_GET['taxonomy_error']) && $_GET['taxonomy_error']) {
        echo '<div class="error"><p>Please Select News Category.</p></div>';
    }
}
add_action('admin_notices', 'taxonomy_selection_error_notice_news_blotter');


// Move the ACF field group (Notification Alert Message) before the post title
function move_acf_alert_message_before_title() {
    global $post;

    if (get_post_type($post) == 'notifications') {
        ?>
        <script type="text/javascript">
            jQuery(document).ready(function($) {
                var alertMessageGroup = $('#acf-group_6710f4ed54eb3'); 
                if (alertMessageGroup.length > 0) {
                    alertMessageGroup.insertBefore('#titlediv'); 
                }
            });
        </script>
        <style>
            #acf-group_6710f4ed54eb3 .postbox-header{
                display:none;
            }
        </style>
        <?php
    }
}
add_action('admin_footer', 'move_acf_alert_message_before_title');


// Update your custom mobile breakpoint below - like return 767;
add_filter( 'astra_mobile_breakpoint', function() {
    return 767;
});

// Update your custom tablet breakpoint below - like return 1024;
add_filter( 'astra_tablet_breakpoint', function() {
    return 1024;
});



// Show User and District Relation field on Selection of User.
function enqueue_acf_custom_script() {
    global $pagenow;
    // Check if we are on the Add User or Edit User pages
    if ( $pagenow === 'user-edit.php' || $pagenow === 'user-new.php' ) {
        wp_enqueue_script('acf-custom-script', get_stylesheet_directory_uri() . '/assets/js/showeventfield.js', array('jquery'), null, true);
    }
}
add_action('admin_enqueue_scripts', 'enqueue_acf_custom_script');



// District Events Start Date End date Validation--
function district_events_custom_acf_date_logic() {
    global $post_type;

    if ( 'district_events' === $post_type ) {
        ?>
        <script type="text/javascript">
            (function($) {
                $(document).ready(function() {
                    function parseDate(input) {
                        var parts = input.split('/');
                        return new Date(parts[2], parts[0] - 1, parts[1]); // month is 0-based
                    }
                    function syncDates(onChange) {
                        var $startDate = $('.acf-field-66cdbd57170bf input.hasDatepicker'); // Start Date visible input
                        var $endDate = $('.acf-field-66cdbd7b170c0 input.hasDatepicker');   // End Date visible input
                        var $endHidden= $('#acf-field_66cdbd7b170c0');
                        var startDateVal = $startDate.val();
                        if (startDateVal) {
                            
                            var startDate = parseDate(startDateVal);
                            $endDate.datepicker('option', 'minDate', startDate);

                            if (onChange) {
                                // $endDate.val(startDateVal); 
                                // $endHidden.val(startDateVal); 
                            }

                            var endDateVal = $endDate.val();
                            if (!endDateVal || parseDate(endDateVal) < startDate) {
                                //$endDate.val(startDateVal); 
                            }
                        }
                    }
                  
                    syncDates(false);

                    $('.acf-field-66cdbd57170bf input.hasDatepicker').on('change', function() {
                        syncDates(true); 
                    });

                });
            })(jQuery);
        </script>
        <?php
    }
}
add_action('admin_footer', 'district_events_custom_acf_date_logic');

// -----------Custom ID for CAP form

add_action('gform_after_submission', 'generate_custom_submission_id', 10, 2);
function generate_custom_submission_id($entry, $form) {
    $form_id = 12; // Replace with your Gravity Form ID
    $field_id = 83; // Replace with the ID of your hidden field for the custom ID

    // Ensure this code runs only for the intended form
    if ((int)$form['id'] !== $form_id) {
        return;
    }

    // Get the current year in YY format
    $year = date('y');
    $option_name = 'gf_custom_id_sequence_' . $year;

    // Retrieve and increment the sequence number
    $sequence = get_option($option_name, 0) + 1;

    // Update the sequence in the database
    update_option($option_name, $sequence);

    // Format the sequence number as four digits
    $sequence_formatted = str_pad($sequence, 4, '0', STR_PAD_LEFT);

    // Construct the custom ID
    $custom_id = 'W-IAD-' . $year . '-' . $sequence_formatted;

    // Save the custom ID back to the hidden field in the entry
    GFAPI::update_entry_field($entry['id'], $field_id, $custom_id);
}

add_filter('gform_pre_submission_filter', 'populate_hidden_field');
function populate_hidden_field($form) {
    $form_id = 12; // Replace with your Gravity Form ID
    $field_id = 83; // Replace with the ID of your hidden field for the custom ID

    // Ensure this code runs only for the intended form
    if ((int)$form['id'] !== $form_id) {
        return $form;
    }

    // Get the current year in YY format
    $year = date('y');
    $option_name = 'gf_custom_id_sequence_' . $year;

    // Retrieve and increment the sequence number
    $sequence = get_option($option_name, 0) + 1;

    // Format the sequence number as four digits
    $sequence_formatted = str_pad($sequence, 4, '0', STR_PAD_LEFT);

    // Construct the custom ID
    $custom_id = 'W-IAD-' . $year . '-' . $sequence_formatted;

    // Populate the hidden field
    foreach ($form['fields'] as &$field) {
        if ($field->id == $field_id) {
            $_POST['input_' . $field_id] = $custom_id;
        }
    }

    return $form;
}


// add_action('init', function() {
//     delete_option('gf_custom_id_sequence_25');
// });


function create_ppd_user_role() {
    // Get the Administrator role
    $admin_role = get_role('administrator');

    // Create a new role with the same capabilities as Administrator
    add_role('ppd_user', 'PPD User', $admin_role->capabilities);
}
add_action('init', 'create_ppd_user_role');


// function restrict_user_roles_for_ppd_user($roles) {
//     // Get the current user
//     $current_user = wp_get_current_user();

//     // Check if the current user has the 'ppd_user' role
//     if (in_array('ppd_user', $current_user->roles)) {
//         // Restrict roles to only 'ppd_user' and 'districteventuser'
//         $allowed_roles = ['ppd_user', 'districteventuser','district_rollcall','cao_user','yac_user','sat_user'];

//         // Filter the roles to include only the allowed ones
//         $roles = array_filter($roles, function($role_key) use ($allowed_roles) {
//             return in_array($role_key, $allowed_roles);
//         }, ARRAY_FILTER_USE_KEY);
//     }

//     return $roles;
// }
// add_filter('editable_roles', 'restrict_user_roles_for_ppd_user');

// function restrict_user_list_for_ppd_user($query) {
//     // Ensure this runs in the admin area on the Users list page and for the main query
//     if (is_admin() && isset($_GET['page']) === false && current_user_can('ppd_user')) {
//         global $pagenow;
        
//         // Only apply this to the Users page
//         if ($pagenow === 'users.php') {
//             // Restrict the query to the roles 'ppd_user' and 'districteventuser'
//             $query->set('role__in', ['ppd_user','districteventuser','district_rollcall','cao_user','yac_user','sat_user']);
//         }
//     }
// }
// add_action('pre_get_users', 'restrict_user_list_for_ppd_user');


// Restrict roles For Subscriber
function restrict_user_roles_for_subscriber_user($roles) {
    $current_user = wp_get_current_user();
    if (in_array('subscriber', $current_user->roles)) {
        $allowed_roles = ['subscriber'];
        $roles = array_filter($roles, function($role_key) use ($allowed_roles) {
            return in_array($role_key, $allowed_roles);
        }, ARRAY_FILTER_USE_KEY);
    }

    return $roles;
}
add_filter('editable_roles', 'restrict_user_roles_for_subscriber_user');

function restrict_user_list_for_subscriber_user($query) {
    if (is_admin() && isset($_GET['page']) === false && current_user_can('subscriber')) {
        global $pagenow;
        if ($pagenow === 'users.php') {
            $query->set('role__in', ['subscriber']);
        }
    }
}
add_action('pre_get_users', 'restrict_user_list_for_subscriber_user');



// Populate the hidden field with the user's IP address
add_filter('gform_field_value_user_ip', function () {
    return $_SERVER['REMOTE_ADDR']; // Get the user's IP address
});

// Customize the message
add_filter('gpb_validation_message', function($message, $form_id, $field_id) {
    
    return 'Form submission is not allowed - Please contact PPD.';
}, 10, 3);


//District Wise Roll call form entry view.
add_filter('gform_get_entries_args_entry_list', function($args) {
    $current_user = wp_get_current_user();
    if (in_array('district_rollcall', $current_user->roles)) {
        $user_district_ids = get_user_meta($current_user->ID, 'roll_call_district_form_view', true);
        
        if (!empty($user_district_ids) && is_array($user_district_ids)) {
            $district_shorts = [];

            foreach ($user_district_ids as $district_id) {
                if ($district_id) {
                    $district_shorts[] = $district_id;
                }
            }

            if (!empty($district_shorts)) {
                $current_form_id = isset($args['form_id']) ? (int)$args['form_id'] : 0;
                $restricted_form_ids = [3];

                if (in_array($current_form_id, $restricted_form_ids)) {
                    if (!isset($args['search_criteria']['field_filters'])) {
                        $args['search_criteria']['field_filters'] = [];
                    }

                    // Filter entries where field 23 matches any of the district short names
                    $args['search_criteria']['field_filters'][] = [
                        'key'      => '23',
                        'value'    => $district_shorts,
                        'operator' => 'in',
                    ];
                }
            }
        }
    }

    return $args;
});

    add_action('admin_menu', function () {
        $current_user = wp_get_current_user();
        $restricted_roles = ['district_rollcall'];

        if (array_intersect($restricted_roles, $current_user->roles)) {
            remove_submenu_page("gf_edit_forms","gf_export");
            echo '<style>
            .gf_form_toolbar_settings { display: none !important; }
            </style>';
        }
           
    }, 11);

    add_action('admin_menu', function () {
        $current_user = wp_get_current_user();
        $restricted_roles = ['district_rollcall', 'cao_user', 'yac_user','sat_user','subscriber'];
        if (array_intersect($restricted_roles, $current_user->roles)) {
            echo '<style>
            #notifications, #gravityformsrecaptcha, .entry-notes div[data-type="notification"], .inside .actions { display: none !important; }
            </style>';
        }
           
    }, 11);







add_filter('login_redirect', 'custom_login_redirect_with_2fa_check', 10, 3);
   function custom_login_redirect_with_2fa_check($redirect_to, $request, $user) {
       if (!is_wp_error($user) && isset($user->roles) && in_array('specific_role', $user->roles)) {
           // Ensure 2FA is completed before redirecting
           if (get_user_meta($user->ID, 'wp_2fa_enabled', true)) {
               // Redirect only after 2FA is completed
               if (!isset($_SESSION['2fa_authenticated']) || $_SESSION['2fa_authenticated'] !== true) {
                   return wp_login_url(); // Redirect back to login if 2FA is not completed
               }
           }
           return home_url('/custom-page/');
       }
       return $redirect_to;
   }
 // Redirect district roll call user to view roll call form page.
 add_filter('login_redirect', function($redirect_to, $requested_redirect_to, $user) {
    if (isset($user->roles) && is_array($user->roles) && in_array('district_rollcall', $user->roles)) {
        return admin_url('admin.php?page=gf_entries&id=3');
    }
    return $redirect_to;
}, 10, 3);


 // Redirect CAO. form page

add_filter('login_redirect', function($redirect_to, $requested_redirect_to, $user) {
    if (isset($user->roles) && is_array($user->roles) && in_array('cao_user', $user->roles)) {
        return admin_url('admin.php?page=gf_entries&id=1');
    }
    return $redirect_to;
}, 10, 3);

 // Redirect YAC form page
add_filter('login_redirect', function($redirect_to, $requested_redirect_to, $user) {
    if (isset($user->roles) && is_array($user->roles) && in_array('yac_user', $user->roles)) {
        return admin_url('admin.php?page=gf_entries&id=8');
    }
    return $redirect_to;
}, 10, 3);

 // Redirect SAT form page
add_filter('login_redirect', function($redirect_to, $requested_redirect_to, $user) {
    if (isset($user->roles) && is_array($user->roles) && in_array('sat_user', $user->roles)) {
        return admin_url('admin.php?page=gf_entries&id=2');
    }
    return $redirect_to;
}, 10, 3);


//Hide Profile Menu and dashboard for 3 users
add_action('admin_menu', function() {
    $current_user = wp_get_current_user();
    $restricted_roles = ['district_rollcall', 'cao_user', 'yac_user','sat_user'];

    if (array_intersect($restricted_roles, $current_user->roles)) {
        remove_menu_page('index.php'); // Hides the Dashboard menu
        remove_menu_page('profile.php'); // Hides the Profile menu
    }
});


/**
 * Get the client's IP address.
 */
function get_client_ip() {
    if (!empty($_SERVER['HTTP_CLIENT_IP'])) {
        return $_SERVER['HTTP_CLIENT_IP']; // IP from shared internet
    } elseif (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
        return explode(',', $_SERVER['HTTP_X_FORWARDED_FOR'])[0]; // IP passed from proxy
    } else {
        return $_SERVER['REMOTE_ADDR']; // Direct IP
    }
}

/**
 * Check if an IP matches any of the allowed patterns.
 */
function is_ip_whitelisted($ip) {
    // Define the whitelist patterns
    $patterns = [
        '/^170\.115\.248\.\d{1,3}$/', // Pattern for 170.115.248.xxx PPD NAT Protocol
        '/^45\.64\.221\.\d{1,3}$/'  // Pattern for 45.64.221.xxx Aliance Broadband
    ];

    // Check if the IP matches any of the patterns
    foreach ($patterns as $pattern) {
        if (preg_match($pattern, $ip) === 1) {
            return true; // IP matches one of the patterns
        }
    }

    return false; // IP does not match any pattern
}

/**
 * Log the client's IP address in the database.
 */
function log_client_ip($route) {
    global $wpdb;

    static $already_logged = false; // Prevent duplicate logging

    if ($already_logged) {
        return; // Skip logging if already executed
    }

    $already_logged = true;

    // Get the client's IP address
    $client_ip = get_client_ip();

    // Log the IP address, route, and timestamp in the database
    $wpdb->insert(
        'wp_api_request_logs',
        [
            'ip_address' => $client_ip,
            'route' => $route,
            'request_time' => current_time('mysql'),
        ]
    );
}

/**
 * Validate Consumer Key, Consumer Secret, HTTPS, and IP Whitelist.
 */
function validate_consumer_key_and_secret($request) {
    // Ensure the request is made over HTTPS
    if (!is_ssl()) {
        return new WP_Error(
            'unauthorized',
            __('Requests must be made over HTTPS.', 'safecam'),
            ['status' => 403]
        );
    }

    // Get the client's IP address
    $client_ip = get_client_ip();

    // Restrict access to specific IP patterns
    if (!is_ip_whitelisted($client_ip)) {
        return new WP_Error(
            'unauthorized',
            __('Your IP is not allowed to access this API.', 'safecam'),
            ['status' => 403]
        );
    }

    // Log the client's IP address and request route
    log_client_ip($request->get_route());

    // Expected Consumer Key and Secret
    $expected_consumer_key = 'ck_5a5e7373cbcd8cd4e44a385fdbc8138bf8ed283f';
    $expected_consumer_secret = 'cs_e4b560767d896bdce802327ca842d4c8b9ab708b';

    // Retrieve headers from the request
    $consumer_key = $request->get_header('consumer-key');
    $consumer_secret = $request->get_header('consumer-secret');

    // Validate the Consumer Key and Secret
    if ($consumer_key !== $expected_consumer_key || $consumer_secret !== $expected_consumer_secret) {
        return new WP_Error(
            'unauthorized',
            __('Invalid Consumer Key or Secret.', 'safecam'),
            ['status' => 403]
        );
    }

    return true; // HTTPS, IP, and authentication validated
}

/**
 * Register the REST route with HTTPS validation and IP restriction.
 */
add_action('rest_api_init', function () {
    register_rest_route('safecam/v1', '/forms/(?P<form_id>\\d+)/entries', [
        'methods'  => 'GET',
        'callback' => 'get_gravity_form_entries',
        'permission_callback' => 'validate_consumer_key_and_secret',
    ]);
});

/**
 * Handle the API request to get Gravity Forms entries.
 */
function get_gravity_form_entries($request) {
    $form_id = (int) $request['form_id']; // Extract form ID from the request

    // Enforce restriction to form ID 9
    if ($form_id !== 9) {
        return new WP_Error(
            'forbidden_form_id',
            __('You are not authorized to access this form.', 'gravityforms'),
            ['status' => 403]
        );
    }

    // Get query parameters for `_field_ids` and `_labels`
    $field_ids = $request->get_param('_field_ids');
    $labels = $request->get_param('_labels');

    try {
        // Fetch entries for the form
        $search_criteria = [];
        $paging = ['offset' => 0, 'page_size' => 100];
        $sorting = ['key' => 'date_created', 'direction' => 'DESC'];

        $entries = GFAPI::get_entries($form_id, $search_criteria, $sorting, $paging);

        // Fetch the form metadata to get field information
        $form = GFAPI::get_form($form_id);

        // Map field IDs to labels
        $field_labels = [];
        if ($form && isset($form['fields'])) {
            foreach ($form['fields'] as $field) {
                if ($field->type === 'address') {
                    // Add labels for address subfields
                    $address_subfields = [
                        '.1' => 'Street',
                        '.2' => 'Street Line 2',
                        '.3' => 'City',
                        '.4' => 'State',
                        '.5' => 'Zip',
                        '.6' => 'Country',
                    ];
                    foreach ($address_subfields as $suffix => $label) {
                        $field_labels[$field->id . $suffix] = $field->label . ' (' . $label . ')';
                    }
                } else {
                    $field_labels[$field->id] = $field->label;
                }
            }
        }

        // If `_field_ids` is specified, filter the entries
        if (!empty($field_ids)) {
            $fields_to_include = explode(',', $field_ids);
            foreach ($entries as &$entry) {
                $entry = array_intersect_key($entry, array_flip($fields_to_include));
            }
        }

        // If `_labels` is specified, include labels and reorder geolocation data
        if (!empty($labels) && (int)$labels === 1) {
            foreach ($entries as &$entry) {
                $reordered_entry = [];
                foreach ($entry as $field_id => $value) {
                    // Add labels to fields, excluding latitude and longitude
                    if (!in_array($field_id, ['7.geolocation_latitude', '7.geolocation_longitude'])) {
                        $reordered_entry[$field_id] = isset($field_labels[$field_id])
                            ? ['label' => $field_labels[$field_id], 'value' => $value]
                            : $value;
                    }

                    // Add latitude and longitude after the last address subfield
                    if ($field_id === '7.6') {
                        $reordered_entry['7.7'] = [
                            'label' => 'Latitude',
                            'value' => $entry['7.geolocation_latitude'] ?? '',
                        ];
                        $reordered_entry['7.8'] = [
                            'label' => 'Longitude',
                            'value' => $entry['7.geolocation_longitude'] ?? '',
                        ];
                    }
                }
                $entry = $reordered_entry; // Replace the original entry with reordered data
            }
        }

        return rest_ensure_response($entries);
    } catch (Exception $e) {
        return new WP_Error(
            'entry_fetch_error',
            __('Unable to fetch entries.', 'gravityforms'),
            ['status' => 500, 'error' => $e->getMessage()]
        );
    }
}





//Gravity From Character Count
function gf_fix_character_count() {
    if (function_exists('wp_enqueue_script')) {
        wp_enqueue_script(
            'gf-character-count-fix',
            get_stylesheet_directory_uri() . '/assets/js/gf-character-count-fix.js',
            array('jquery'),
            null,
            true
        );
    }
}
add_action('wp_enqueue_scripts', 'gf_fix_character_count');


//Gravity From allow file upload
add_filter('gform_file_upload_whitelisting_disabled', '__return_true');





// ----------------News Blotter Admin Search --------------------
add_filter('posts_search', function($search, $query) {
    global $wpdb;
    if (is_admin() && $query->is_main_query() && $query->get('post_type') === 'news-blotter' && !empty($query->get('s'))) {
        $search_term = esc_sql($query->get('s'));
        $acf_field = 'news_blotter_description'; 

        $search = "
            AND (
                {$wpdb->posts}.post_title LIKE '%{$search_term}%'
                OR {$wpdb->posts}.post_content LIKE '%{$search_term}%'
                OR EXISTS (
                    SELECT 1 FROM {$wpdb->postmeta} 
                    WHERE {$wpdb->postmeta}.post_id = {$wpdb->posts}.ID 
                    AND {$wpdb->postmeta}.meta_key = '{$acf_field}' 
                    AND {$wpdb->postmeta}.meta_value LIKE '%{$search_term}%'
                )
            )
        ";
    }

    return $search;
}, 10, 2);
// ----------------News Blotter Admin Search --------------------

add_filter( 'gfpdf_pdf_field_content', function( $value, $field, $entry, $form ) {
    if ( $form['id'] == 3 ) {
        if( $field->id === 27 ) {
        return '';
    }
    }
        return $value;
    }, 10, 4 );

    add_filter( 'gfpdf_field_label', function( $label, $field, $entry ) {
            if( $field->id === 27 ) {
            return '';
        }
        return $label;
    }, 10, 3 );



    add_filter( 'gfpdf_pdf_field_content', function( $value, $field, $entry, $form ) {
        if ( $form['id'] == 3 ) {
            if( $field->id === 27 ) {
            return '';
        }
        }
            return $value;
        }, 10, 4 );
    
        add_filter( 'gfpdf_field_label', function( $label, $field, $entry ) {
                if( $field->id === 27 ) {
                return '';
            }
            return $label;
        }, 10, 3 );
    
 // Gravity form filter entry date
        add_action('admin_footer', function () {
            if (isset($_GET['page']) && $_GET['page'] === 'gf_entries') {
                ?>
                <script>
                    (function($) {
        $(document).ready(function() {
            let filterDropdown = $('.gform-filter-field');
            let operatorDropdown = $('.gform-filter-operator');
            let searchButton = $('#entry_search_button');
    
            function updateOperatorOptions() {
                if (filterDropdown.val() === 'date_created') { 
                    $('.gform-filter-operator').append('<option value="contains">contains</option>');
                } else {
                    
                }
            }
            filterDropdown.on('change', function() {
                    setTimeout(updateOperatorOptions, 500); 
              });
    
            searchButton.on('click', function() {
                let selectedField = filterDropdown.val();
                let selectedOperator = operatorDropdown.val();
    
                let searchParams = new URLSearchParams(window.location.search);
                searchParams.set('field_id', selectedField);
                searchParams.set('operator', selectedOperator);
                
                
                window.history.replaceState(null, "", `${window.location.pathname}?${searchParams.toString()}`);
            });
    
            setTimeout(function() {
                let urlParams = new URLSearchParams(window.location.search);
                let operatorFromURL = urlParams.get('operator');
    
                if (operatorFromURL) {
                    $('.gform-filter-operator').val(operatorFromURL);
                }
            }, 500); 
    
            updateOperatorOptions();
        });
    })(jQuery);
    
                </script>
                <?php
    
            }
        });

 // Gravity form Field Disable
 add_action('admin_footer', function () {
    $current_user = wp_get_current_user();
    $restricted_roles = ['district_rollcall', 'cao_user', 'yac_user','sat_user','subscriber'];
    if (array_intersect($restricted_roles, $current_user->roles)) {
    if (isset($_GET['page']) && $_GET['page'] === 'gf_entries' && isset($_GET['view']) && $_GET['view'] === 'entry') {
        ?>
        <script>
            (function($) {
                $(document).ready(function() {
                    setTimeout(function() { // Ensure fields are loaded before applying changes
                        $('.detail-view').each(function() {
                            let label = $(this).find('.detail-label').text().trim();
                            
                            if (label !== "Status") {
                                // Make other fields read-only (excluding Status)
                                $(this).find('input, textarea').prop('readonly', true);
                                $(this).find('input, textarea, select').css('background', '#e9e9e9')
                                $(this).find('select').css('pointer-events', 'none'); // Prevent select changes
                                $(this).find('input[type="file"').css('pointer-events', 'none');
                                $(this).find('.gform-icon--circle-delete').css('pointer-events', 'none');
                                
                            }
                        });
                    }, 200); // Delay to ensure Gravity Forms fully loads
                });
            })(jQuery);
        </script>
        <?php
    }
}
});

// -----Rest Api for crime table
add_action('rest_api_init', function () {
    register_rest_route('ppdcrime/v1', '/data', array(
        'methods'  => WP_REST_Server::READABLE,
        'callback' => function (WP_REST_Request $request) {
            global $wpdb;
            $table = $wpdb->prefix . 'crimedata';

            // Get year parameter
            $year = (int) $request->get_param('year');

            // Validate year
            if (!$year || strlen($year) !== 4) {
                return new WP_REST_Response(['error' => 'Invalid or missing year.'], 400);
            }

            // Query data for that year
            $query = $wpdb->prepare(
                "SELECT ID, primary_id, date, type, crime_category, police_district 
                 FROM $table 
                 WHERE YEAR(date) = %d",
                $year
            );

            $results = $wpdb->get_results($query);

            return new WP_REST_Response($results, 200);
        },
        'permission_callback' => '__return_true',
        'args' => [
            'year' => [
                'required' => true,
                'validate_callback' => function ($param) {
                    return is_numeric($param) && strlen($param) === 4;
                }
            ]
        ]
    ));
});


add_action('rest_api_init', function () {
    register_rest_route('homicides/v1', '/data', array(
        'methods'  => WP_REST_Server::READABLE,
        'callback' => function (WP_REST_Request $request) {
            global $wpdb;
            $table = $wpdb->prefix . 'homicidescrime';

            // Get year from query parameter
            $year = (int) $request->get_param('year');

            // Basic validation
            if (!$year || strlen($year) !== 4) {
                return new WP_REST_Response(['error' => 'Invalid or missing year.'], 400);
            }

            // Query data from the specified year (assumes crime_date is in YYYY-MM-DD format)
            $query = $wpdb->prepare(
                "SELECT * FROM $table WHERE YEAR(crime_date) = %d",
                $year
            );

            $results = $wpdb->get_results($query);
            $total = count($results);

            return new WP_REST_Response($results, 200);
        },
        'permission_callback' => '__return_true',
        'args' => [
            'year' => [
                'required' => true,
                'validate_callback' => function($param) {
                    return is_numeric($param) && strlen($param) === 4;
                }
            ]
        ]
    ));
});

add_action('admin_menu', function () {
    if (current_user_can('phillychatuser') && !current_user_can('administrator')) {
        global $menu, $submenu;
        $allowed = [
            'philly-chatbot',
            'philly-chatbot-history',
            'philly-chatbot-pdf-manager',
            'philly-chatbot-pdf-chunks',
            'philly-chatbot-queue-manager',
            'philly-chatbot-quick-links',
        ];

        foreach ($menu as $index => $item) {
            if (!in_array($item[2], $allowed)) {
                unset($menu[$index]);
            }
        }

        foreach ($submenu as $parent => $items) {
            if (!in_array($parent, $allowed)) {
                unset($submenu[$parent]);
            }
        }
    }
}, 999);

// add_action('admin_init', function () {
//     if (!current_user_can('phillychatuser') || current_user_can('administrator')) return;

//     // Allowed slugs for phillychatuser
//     $allowed_pages = [
//         'philly-chatbot',
//         'philly-chatbot-history',
//         'philly-chatbot-pdf-manager',
//         'philly-chatbot-pdf-chunks',
//         'philly-chatbot-queue-manager',
//         'philly-chatbot-quick-links'
//     ];

//     $current_page = $_GET['page'] ?? '';

//     // Allow only if it's one of the allowed plugin pages
//     if (!in_array($current_page, $allowed_pages)) {
//         wp_redirect(admin_url('admin.php?page=philly-chatbot'));
//         exit;
//     }
// });

add_filter('login_redirect', function ($redirect_to, $request, $user) {
    if (is_wp_error($user) || !is_object($user)) return $redirect_to;

    if (in_array('phillychatuser', (array) $user->roles)) {
        return admin_url('admin.php?page=philly-chatbot');
    }

    return $redirect_to;
}, 10, 3);


// ---------- Rest API for District Events

// Allow filtering district_events by start_Date
add_action('rest_api_init', function () {
    register_rest_route('district-events/v1', '/list', [
        'methods'  => 'GET',
        'callback' => 'get_district_events_by_date',
        'args'     => [
            'start' => [
                'required' => false,
                'sanitize_callback' => 'sanitize_text_field',
            ],
            'end' => [
                'required' => false,
                'sanitize_callback' => 'sanitize_text_field',
            ],
        ],
        'permission_callback' => '__return_true', // make public
    ]);
});

function get_district_events_by_date(WP_REST_Request $request) {
    $start = $request->get_param('start'); // expected format 20250501
    $end   = $request->get_param('end');

    $meta_query = [];

    if ($start && $end) {
        $meta_query[] = [
            'key'     => 'start_Date',
            'value'   => [$start, $end],
            'compare' => 'BETWEEN',
            'type'    => 'NUMERIC',
        ];
    } elseif ($start) {
        $meta_query[] = [
            'key'     => 'start_Date',
            'value'   => $start,
            'compare' => '>=',
            'type'    => 'NUMERIC',
        ];
    } elseif ($end) {
        $meta_query[] = [
            'key'     => 'start_Date',
            'value'   => $end,
            'compare' => '<=',
            'type'    => 'NUMERIC',
        ];
    }

    $args = [
        'post_type'      => 'district_events',
        'posts_per_page' => -1,
        'meta_query'     => $meta_query,
        'orderby'        => 'meta_value_num',
        'meta_key'       => 'start_Date',
        'order'          => 'ASC',
    ];

    $query = new WP_Query($args);

    $events = [];
    while ($query->have_posts()) {
        $query->the_post();
        $dist = get_field('district_relation');
        $events[] = [
            'id'    => get_the_ID(),
            'title' => get_the_title(),
            'description'=> get_the_content(),
            'district_relation' => get_the_title(6113),
            'location' => get_field('location'),
            'agenda' => get_field('agenda'),
            'start_date' => get_field('start_Date'),
            'end_date' => get_field('end_date'),
            'start_time'=> get_field('start_time'),
            'end_time'=> get_field('end_time'),
            'event_image' => get_field('event_image')['url'],
            'link'  => get_permalink(),
        ];
    }
    wp_reset_postdata();

    return $events;
}


/*****************************************************************
 *****************************************************************
 *****************************************************************/
/*Feature to restricts domains like test.com, example.com starts*/
/*add_action('wp_footer', 'restrict_gf_resume_email_domains', 9999);
function restrict_gf_resume_email_domains() {
    if (!function_exists('gravity_form')) {
        return;
    }
    ?>
    <script type="text/javascript">
    (function() {
        'use strict';
        
        // ⚙️ CONFIGURATION - Domains to block
        var restrictedDomains = ['test.com', 'example.com'];
        
        var validationAdded = false;
        var watcherStarted = false;
        
        function addEmailValidation() {
            if (validationAdded) {
                return;
            }
            
            var emailInput = document.getElementById('gform_resume_email');
            var submitButton = document.querySelector('input[name="gform_send_resume_link_button"]');
            
            if (!emailInput || !submitButton) {
                return;
            }
            
            //console.log('✅ Resume form found, adding domain validation.');
            validationAdded = true;
            
            // Remove any existing error message
            var existingError = document.getElementById('resume-email-error');
            if (existingError) {
                existingError.remove();
            }
            
            // Create error message element
            var errorDiv = document.createElement('div');
            errorDiv.id = 'resume-email-error';
            errorDiv.style.cssText = 'color: #d9534f; margin-top: 8px; font-size: 14px; font-weight: 500; display: none;';
            
            // Insert error message after email input
            if (emailInput.parentNode) {
                var emailContainer = emailInput.closest('.ginput_container');
                if (emailContainer) {
                    emailContainer.appendChild(errorDiv);
                } else {
                    emailInput.parentNode.insertBefore(errorDiv, emailInput.nextSibling);
                }
            }
            
            // Store original button value and styles
            var originalButtonValue = submitButton.value;
            var originalButtonStyle = submitButton.style.cssText;
            
            function validateEmail() {
                var email = emailInput.value.trim().toLowerCase();
                
                if (email && email.indexOf('@') > -1) {
                    var domain = email.split('@')[1];
                    
                    if (domain && restrictedDomains.indexOf(domain) !== -1) {
                        // Disable button and show error
                        submitButton.disabled = true;
                        submitButton.style.cssText = originalButtonStyle + '; opacity: 0.5; cursor: not-allowed; background-color: #ccc !important;';
                        submitButton.value = 'Invalid Email Domain';
                        
                        errorDiv.textContent = '❌ Email addresses from "' + domain + '" are not allowed. Please use a different email address.';
                        errorDiv.style.display = 'block';
                        
                        //console.log('❌ Blocked email domain: ' + domain);
                        return false;
                    } else {
                        // Enable button and hide error
                        submitButton.disabled = false;
                        submitButton.style.cssText = originalButtonStyle;
                        submitButton.value = originalButtonValue;
                        errorDiv.style.display = 'none';
                        return true;
                    }
                } else if (email === '') {
                    // Reset if email is empty
                    submitButton.disabled = false;
                    submitButton.style.cssText = originalButtonStyle;
                    submitButton.value = originalButtonValue;
                    errorDiv.style.display = 'none';
                }
                
                return true;
            }
            
            // Validate on every keystroke
            emailInput.addEventListener('input', validateEmail);
            emailInput.addEventListener('change', validateEmail);
            emailInput.addEventListener('blur', validateEmail);
            emailInput.addEventListener('keyup', validateEmail);
            
            // Validate immediately in case there's already a value
            validateEmail();
            
            // Also prevent form submission as backup
            var resumeForm = submitButton.closest('form');
            if (resumeForm) {
                resumeForm.addEventListener('submit', function(e) {
                    if (!validateEmail()) {
                        e.preventDefault();
                        e.stopPropagation();
                        e.stopImmediatePropagation();
                        //console.log('❌ Form submission blocked');
                        return false;
                    }
                }, true);
            }
            
            // Intercept button click as final backup
            submitButton.addEventListener('click', function(e) {
                if (submitButton.disabled || !validateEmail()) {
                    e.preventDefault();
                    e.stopPropagation();
                    e.stopImmediatePropagation();
                    //console.log('❌ Button click blocked');
                    return false;
                }
            }, true);
        }
        
        function startWatching() {
            if (watcherStarted) {
                return;
            }
            watcherStarted = true;
            
            document.addEventListener('click', function(e) {
                var target = e.target;
                
                if (target && (
                    target.classList.contains('gform_save_link') || 
                    target.closest('.gform_save_link')
                )) {
                    //console.log('✅ Save & Continue clicked, waiting for email form...');
                    validationAdded = false;
                    
                    var attempts = 0;
                    var maxAttempts = 50;
                    
                    var checkInterval = setInterval(function() {
                        attempts++;
                        addEmailValidation();
                        
                        if (validationAdded || attempts >= maxAttempts) {
                            clearInterval(checkInterval);
                        }
                    }, 200);
                }
            }, true);
        }
        
        function init() {
            addEmailValidation();
            startWatching();
        }
        
        if (document.readyState === 'loading') {
            document.addEventListener('DOMContentLoaded', init);
        } else {
            init();
        }
        
        window.addEventListener('load', function() {
            setTimeout(addEmailValidation, 500);
        });
        
    })();
    </script>
    <?php
}*/
/*Feature to restricts domains like test.com, example.com ends*/

/*Feature to enable button for industry standard domains like gmail, yahoo, etc. starts*/
add_action('wp_footer', 'restrict_gf_resume_email_domains', 9999);
function restrict_gf_resume_email_domains() {
    if (!function_exists('gravity_form')) {
        return;
    }
    ?>
    <script type="text/javascript">
    (function() {
        'use strict';
        
        // ⚙️ CONFIGURATION
        // Set validateMode to either:
        // 'whitelist' - Only allow domains in the allowedDomains list
        // 'blacklist' - Block free/disposable emails, verify custom domains via DNS
        var validateMode = 'blacklist'; // Change to 'whitelist' for strict mode
        
        // Standard email providers (used for whitelist mode OR to skip DNS check in blacklist mode)
        var allowedDomains = [
            'gmail.com',
            'yahoo.com',
            'outlook.com',
            'hotmail.com',
            'icloud.com',
            'live.com',
            'msn.com',
            'aol.com',
            'protonmail.com',
            'zoho.com',
            'mail.com',
            'yandex.com',
            'gmx.com',
            'yahoo.co.in',
            'yahoo.co.uk',
            'outlook.in',
            'rediffmail.com'
        ];
        
        // Disposable/temporary email providers to block
        var blockedDomains = [
            // Temporary/Disposable Email Services
            'tempmail.com',
            'guerrillamail.com',
            '10minutemail.com',
            'throwaway.email',
            'mailinator.com',
            'maildrop.cc',
            'trashmail.com',
            'getnada.com',
            'temp-mail.org',
            'dispostable.com',
            'fakeinbox.com',
            'throwawaymail.com',
            'minutemail.com',
            'mytrashmail.com',
            'tempinbox.com',
            'sharklasers.com',
            'guerrillamail.info',
            'grr.la',
            'guerrillamail.biz',
            'guerrillamail.de',
            'spam4.me',
            'yopmail.com',
            'mailnesia.com',
            'emailondeck.com',
            '33mail.com',
            'jetable.org',
            'throwam.com',
            'spamgourmet.com',
            'mailcatch.com',
            'deadaddress.com',
            
            // Test/Example Domains (RFC 2606)
            'example.com',
            'example.org',
            'example.net',
            'test.com',
            'test.org',
            'test.net',
            'localhost.com',
            'invalid.com',
            
            // Adult Content Domains (Add more as needed)
            'pornhub.com',
            'xvideos.com',
            'xnxx.com',
            'redtube.com',
            'youporn.com',
            'tube8.com',
            'spankbang.com',
            'xhamster.com',
            'chaturbate.com',
            'onlyfans.com',
            'hello1.com',
            
            // Suspicious/Spam Domains
            'mailtemp.com',
            'fakeemail.com',
            'spambox.us',
            'trashmail.net',
            'thankyou2010.com',
            'trash2009.com',
            'mytemp.email',
            'tempmail.net',
            'getairmail.com',
            'inboxbear.com',
            'mohmal.com',
            'moakt.com',
            'anonymbox.com',
            'wegwerfmail.de',
            'tempmail.de'
        ];
        
        // Suspicious domain patterns to block (look-alike/typosquat domains)
        var suspiciousPatterns = [
            /^gmail\d+\.com$/i,           // gmail1.com, gmail123.com (but NOT gmail.com)
            /^yahoo\d+\.com$/i,           // yahoo1.com, yahoo12.com (but NOT yahoo.com)
            /^outlook\d+\.com$/i,         // outlook1.com, outlook99.com
            /^hotmail\d+\.com$/i,         // hotmail1.com, hotmail2.com
            /^icloud\d+\.com$/i,          // icloud1.com, icloud5.com
            /^gmail[a-z]+\.com$/i,        // gmailx.com, gmailz.com (but NOT gmail.com)
            /^yahoo[a-z]+\.com$/i,        // yahoox.com, yahooz.com
            /^outlook[a-z]+\.com$/i,      // outlookx.com
            /^hotmail[a-z]+\.com$/i,      // hotmailx.com
            /^[a-z]{1,2}\.(com|net|org)$/i,  // q.com, a.com, ab.net (1-2 letter domains)
            /^test\d+\.(com|net|org)$/i,  // test1.com, test123.org
            /^example\d+\.(com|net|org)$/i, // example1.com, example2.net
            /(porn|xxx|sex|adult|nude|escort|casino|viagra|cialis)/i, // Adult/spam keywords
        ];
        
        var validationAdded = false;
        var watcherStarted = false;
        var domainCache = {}; // Cache DNS validation results
        
        function addEmailValidation() {
            if (validationAdded) {
                return;
            }
            
            var emailInput = document.getElementById('gform_resume_email');
            var submitButton = document.querySelector('input[name="gform_send_resume_link_button"]');
            
            if (!emailInput || !submitButton) {
                return;
            }
            
            //console.log('✅ Resume form found, adding domain validation.');
            validationAdded = true;
            
            // Remove any existing error message
            var existingError = document.getElementById('resume-email-error');
            if (existingError) {
                existingError.remove();
            }
            
            // Create error message element
            var errorDiv = document.createElement('div');
            errorDiv.id = 'resume-email-error';
            errorDiv.style.cssText = 'color: #d9534f; margin-top: 8px; font-size: 14px; font-weight: 500; display: none;';
            
            // Insert error message after email input
            if (emailInput.parentNode) {
                var emailContainer = emailInput.closest('.ginput_container');
                if (emailContainer) {
                    emailContainer.appendChild(errorDiv);
                } else {
                    emailInput.parentNode.insertBefore(errorDiv, emailInput.nextSibling);
                }
            }
            
            // Store original button value and styles
            var originalButtonValue = submitButton.value;
            var originalButtonStyle = submitButton.style.cssText;
            
            // Disable button initially
            submitButton.disabled = true;
            submitButton.style.cssText = originalButtonStyle + '; opacity: 0.5; cursor: not-allowed; background-color: #ccc !important;';
            submitButton.value = 'Enter Valid Email';
            
            var validationTimeout = null;
            
            function checkDomainExists(domain, callback) {
                // Check cache first
                if (domainCache.hasOwnProperty(domain)) {
                    callback(domainCache[domain]);
                    return;
                }
                
                // Use DNS-over-HTTPS to check if domain has MX records
                var dohUrl = 'https://cloudflare-dns.com/dns-query?name=' + encodeURIComponent(domain) + '&type=MX';
                
                fetch(dohUrl, {
                    method: 'GET',
                    headers: {
                        'Accept': 'application/dns-json'
                    }
                })
                .then(function(response) {
                    return response.json();
                })
                .then(function(data) {
                    // Check if domain has MX records (mail servers)
                    var isValid = data.Status === 0 && data.Answer && data.Answer.length > 0;
                    domainCache[domain] = isValid;
                    callback(isValid);
                })
                .catch(function(error) {
                    //console.log('DNS check failed for ' + domain + ', assuming valid');
                    // If DNS check fails, assume valid to avoid false negatives
                    domainCache[domain] = true;
                    callback(true);
                });
            }
            
            function validateEmail() {
                var email = emailInput.value.trim().toLowerCase();
                
                if (!email || email.indexOf('@') === -1) {
                    // Email is empty or incomplete
                    submitButton.disabled = true;
                    submitButton.style.cssText = originalButtonStyle + '; opacity: 0.5; cursor: not-allowed; background-color: #ccc !important;';
                    submitButton.value = 'Enter Valid Email';
                    errorDiv.style.display = 'none';
                    return false;
                }
                
                var domain = email.split('@')[1];
                if (!domain) {
                    submitButton.disabled = true;
                    submitButton.style.cssText = originalButtonStyle + '; opacity: 0.5; cursor: not-allowed; background-color: #ccc !important;';
                    submitButton.value = 'Enter Valid Email';
                    errorDiv.style.display = 'none';
                    return false;
                }
                
                // Basic email format validation
                var emailPattern = /^[a-z0-9._%+-]+@[a-z0-9.-]+\.[a-z]{2,}$/;
                if (!emailPattern.test(email)) {
                    submitButton.disabled = true;
                    submitButton.style.cssText = originalButtonStyle + '; opacity: 0.5; cursor: not-allowed; background-color: #ccc !important;';
                    submitButton.value = 'Invalid Email Format';
                    errorDiv.textContent = '❌ Please enter a valid email address.';
                    errorDiv.style.display = 'block';
                    return false;
                }
                
                if (validateMode === 'whitelist') {
                    // Whitelist mode: Only allow specific domains
                    if (allowedDomains.indexOf(domain) !== -1) {
                        submitButton.disabled = false;
                        submitButton.style.cssText = originalButtonStyle;
                        submitButton.value = originalButtonValue;
                        errorDiv.style.display = 'none';
                        return true;
                    } else {
                        submitButton.disabled = true;
                        submitButton.style.cssText = originalButtonStyle + '; opacity: 0.5; cursor: not-allowed; background-color: #ccc !important;';
                        submitButton.value = 'Invalid Email Domain';
                        errorDiv.textContent = '❌ Please use an email from standard providers like Gmail, Yahoo, Outlook, iCloud, etc.';
                        errorDiv.style.display = 'block';
                        return false;
                    }
                } else {
                    // Blacklist mode: Check disposable domains and verify custom domains
                    
                    // First check if it's a blocked domain (disposable, test, adult, etc.)
                    if (blockedDomains.indexOf(domain) !== -1) {
                        submitButton.disabled = true;
                        submitButton.style.cssText = originalButtonStyle + '; opacity: 0.5; cursor: not-allowed; background-color: #ccc !important;';
                        submitButton.value = 'Invalid Email Domain';
                        errorDiv.textContent = '❌ This email domain is not allowed. Please use a valid email address.';
                        errorDiv.style.display = 'block';
                        return false;
                    }
                    
                    // Check for suspicious look-alike domains and inappropriate keywords
                    var isSuspicious = false;
                    for (var i = 0; i < suspiciousPatterns.length; i++) {
                        if (suspiciousPatterns[i].test(domain)) {
                            isSuspicious = true;
                            //console.log('⚠️ Suspicious pattern matched: ' + domain);
                            break;
                        }
                    }
                    
                    if (isSuspicious) {
                        submitButton.disabled = true;
                        submitButton.style.cssText = originalButtonStyle + '; opacity: 0.5; cursor: not-allowed; background-color: #ccc !important;';
                        submitButton.value = 'Invalid Domain';
                        errorDiv.textContent = '❌ This email domain is not allowed. Please use a professional email address.';
                        errorDiv.style.display = 'block';
                        //console.log('❌ Blocked suspicious domain: ' + domain);
                        return false;
                    }
                    
                    // If it's a known good domain, accept immediately
                    if (allowedDomains.indexOf(domain) !== -1) {
                        submitButton.disabled = false;
                        submitButton.style.cssText = originalButtonStyle;
                        submitButton.value = originalButtonValue;
                        errorDiv.style.display = 'none';
                        return true;
                    }
                    
                    // For custom domains, verify they exist via DNS check
                    // Show "verifying" state
                    submitButton.disabled = true;
                    submitButton.style.cssText = originalButtonStyle + '; opacity: 0.7; cursor: wait; background-color: #f0ad4e !important;';
                    submitButton.value = 'Verifying Domain...';
                    errorDiv.style.display = 'none';
                    
                    // Clear any existing timeout
                    if (validationTimeout) {
                        clearTimeout(validationTimeout);
                    }
                    
                    // Debounce the DNS check (wait 800ms after user stops typing)
                    validationTimeout = setTimeout(function() {
                        checkDomainExists(domain, function(isValid) {
                            // Verify email value hasn't changed during DNS check
                            var currentEmail = emailInput.value.trim().toLowerCase();
                            var currentDomain = currentEmail.split('@')[1];
                            
                            if (currentDomain !== domain) {
                                // Email changed during check, ignore result
                                return;
                            }
                            
                            if (isValid) {
                                submitButton.disabled = false;
                                submitButton.style.cssText = originalButtonStyle;
                                submitButton.value = originalButtonValue;
                                errorDiv.style.display = 'none';
                                //console.log('✅ Valid custom domain: ' + domain);
                            } else {
                                submitButton.disabled = true;
                                submitButton.style.cssText = originalButtonStyle + '; opacity: 0.5; cursor: not-allowed; background-color: #ccc !important;';
                                submitButton.value = 'Invalid Email Domain';
                                errorDiv.textContent = '❌ This email domain does not exist or cannot receive emails.';
                                errorDiv.style.display = 'block';
                                //console.log('❌ Invalid custom domain: ' + domain);
                            }
                        });
                    }, 800);
                    
                    return false; // Temporarily false while checking
                }
            }
            
            // Validate on every keystroke
            emailInput.addEventListener('input', validateEmail);
            emailInput.addEventListener('change', validateEmail);
            emailInput.addEventListener('blur', validateEmail);
            emailInput.addEventListener('keyup', validateEmail);
            
            // Validate immediately in case there's already a value
            validateEmail();
            
            // Also prevent form submission as backup
            var resumeForm = submitButton.closest('form');
            if (resumeForm) {
                resumeForm.addEventListener('submit', function(e) {
                    if (submitButton.disabled) {
                        e.preventDefault();
                        e.stopPropagation();
                        e.stopImmediatePropagation();
                        //console.log('❌ Form submission blocked');
                        return false;
                    }
                }, true);
            }
            
            // Intercept button click as final backup
            submitButton.addEventListener('click', function(e) {
                if (submitButton.disabled) {
                    e.preventDefault();
                    e.stopPropagation();
                    e.stopImmediatePropagation();
                    //console.log('❌ Button click blocked');
                    return false;
                }
            }, true);
        }
        
        function startWatching() {
            if (watcherStarted) {
                return;
            }
            watcherStarted = true;
            
            document.addEventListener('click', function(e) {
                var target = e.target;
                
                if (target && (
                    target.classList.contains('gform_save_link') || 
                    target.closest('.gform_save_link')
                )) {
                    //console.log('✅ Save & Continue clicked, waiting for email form...');
                    validationAdded = false;
                    
                    var attempts = 0;
                    var maxAttempts = 50;
                    
                    var checkInterval = setInterval(function() {
                        attempts++;
                        addEmailValidation();
                        
                        if (validationAdded || attempts >= maxAttempts) {
                            clearInterval(checkInterval);
                        }
                    }, 200);
                }
            }, true);
        }
        
        function init() {
            addEmailValidation();
            startWatching();
        }
        
        if (document.readyState === 'loading') {
            document.addEventListener('DOMContentLoaded', init);
        } else {
            init();
        }
        
        window.addEventListener('load', function() {
            setTimeout(addEmailValidation, 500);
        });
        
    })();
    </script>
    <?php
}
/*Feature to enable button for industry standard domains like gmail, yahoo, etc. ends*/
/*****************************************************************
 *****************************************************************
 *****************************************************************/

add_action('init', function() {
    if (defined('JWT_AUTH_SECRET_KEY')) {
        error_log('✅ JWT key loaded: ' . substr(JWT_AUTH_SECRET_KEY, 0, 10));
    } else {
        error_log('❌ JWT key not loaded');
    }
});