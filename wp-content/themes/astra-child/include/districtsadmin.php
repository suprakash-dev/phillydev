<?php

function alldistrict_menu(){
    add_menu_page('All District Events', 'All District Events', 'manage_options', 'all-district', 'all_district', 'dashicons-calendar-alt', 6);
}
add_action('admin_menu', 'alldistrict_menu');


function enqueue_admin_data_css(){
    wp_enqueue_script( 'datatabel-js', '//cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js', array( 'jquery' ), '', true );
    wp_enqueue_style( 'datatabel-style', '//cdn.datatables.net/1.13.6/css/jquery.dataTables.min.css' );
    wp_enqueue_style( 'admin-style-event-table', get_stylesheet_directory_uri() . '/assets/css/adminused.css', [], '1.0.0');
    }
add_action( 'admin_enqueue_scripts', 'enqueue_admin_data_css' );




function all_district() {
    $args = array(
        'post_type' => 'district_events',
        'posts_per_page' => -1,  // Get all posts
        'post_status' => 'publish',
    );
    
    $posts = get_posts($args);
    $district_relation_values = array();

    echo '<div class="warp">';
    echo '<h1>All Districts</h1>';
?>
<?php if ($posts) { ?>
<div class="filter-wrap">
    <div id="control-length" class="event-column"></div>
    <div class="district-filter-admin event-column">
        <label for="district_filter_admin">Filter by Districts:</label>
            <select id="district_filter_admin" name="district_filter_admin">
                <option value="all">All Districts</option>
                <?php 
                    foreach ($posts as $post) {
                        $district_relation = get_field('district_relation', $post->ID);
                        if (!empty($district_relation)) {
                            if (is_array($district_relation)) {
                                foreach ($district_relation as $relation_value) {
                                    $district_relation_values[] = get_the_title($relation_value);
                                }
                            } else {
                                $district_relation_values[] = get_the_title($district_relation);
                            }
                        }
                    }
                    $district_relation_values = array_unique($district_relation_values);
                    sort($district_relation_values);
                    foreach ($district_relation_values as $value) {
                        echo '<option value="'.$value.'">'.$value.'</option>';
                    }
                ?>
            </select>
        </div>
        <div id="control-search" class="event-column"></div>
</div>
<?php } else {
                    //echo 'No posts found.';
                } ?>

<?php
    $args = array(
        'post_type'      => 'district_events', // Custom post type
        'post_status' => 'publish',
        'posts_per_page' => -1,                // Number of posts per page
    );

    // Create a new WP_Query instance
    $district_events_query = new WP_Query($args);

    // Check if there are posts available
    if ($district_events_query->have_posts()) { ?>
                <table id="eventTable" class="display">
            <thead>
        <tr>
        <th>Event Name</th>
        <th>Location</th>
        <th>Agenda</th>
        <th>Start Date</th>
        <th>End Date</th>
        <th>District</th>
        <th>District ID</th>
        </tr>
        </thead>
            <tbody>
        <?php 
        while ($district_events_query->have_posts()) {
            $district_events_query->the_post();
            ?>
            <tr>
    <td><?php the_title(); ?></td>
    <td><?php the_field('location'); ?></td>
    <td><?php the_field('agenda'); ?></td>
    <?php
    $startdate = new DateTime(get_field('start_Date'));
     $StartDate = $startdate->format('Ymd');
    ?>
    <td><span style="display:none;"><?php echo $StartDate; ?></span><?php the_field('start_Date'); ?> <?php the_field('start_time'); ?></td>
    <?php
    $enddate = new DateTime(get_field('end_date'));
     $EndDate = $enddate->format('Ymd');
    ?>
    <td><span style="display:none;"><?php echo $EndDate; ?></span><?php the_field('end_date'); ?> <?php the_field('end_time'); ?></td>
    <?php $evtitle= get_field('district_relation')[0]; ?>
    <td><?php echo get_the_title($evtitle); ?></td>
    <td><?php echo $evtitle; ?></td>
    </tr>
            <?php
        } ?>
        </tbody>
        <table>
<?php 
    } else {
        echo '<p>' . __('No events found.') . '</p>';
    }

    // Reset post data to avoid conflicts with other queries on the page
    wp_reset_postdata(); ?>
<script>
    jQuery(document).ready( function () {
        var table =  jQuery('#eventTable').DataTable({
        "oLanguage": {
            "sLengthMenu": "Show _MENU_ ",
        },
        'columnDefs': [
            {
                'visible': false,
                'targets': [6]
            }
        ],
        initComplete: function() {
            jQuery('#eventTable_length').appendTo('#control-length');
            jQuery('#eventTable_filter').appendTo('#control-search');
        }
        });

        function filterTable() {
            var district = jQuery("#district_filter_admin").val();
            
            if (district == 'all') {
            table.columns(5).search('').draw();
        } else {
            table.column(5).search('^' + district + '$', true, false).draw();
        }

        }

        // Bind the filterTable function to both dropdowns
        jQuery("#district_filter_admin").on("change", function() {
            filterTable();
        });
        
} );


</script>
<?php 
echo '</div>';
} ?>