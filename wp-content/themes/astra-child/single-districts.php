<?php
get_header(); ?>
<script type="text/javascript" src="https://cdn.jsdelivr.net/momentjs/latest/moment.min.js"></script>
<script type="text/javascript" src="https://cdn.jsdelivr.net/npm/daterangepicker/daterangepicker.min.js"></script>
<script type="text/javascript" src="<?php echo get_stylesheet_directory_uri();?>'/assets/js/atc.min.js'"></script>
<link rel="stylesheet" type="text/css" href="https://cdn.jsdelivr.net/npm/daterangepicker/daterangepicker.css" />


<?php if ( have_posts() ) : while ( have_posts() ) : the_post(); 
$postId = get_the_ID();
$post = get_post($postId); 
$slug = $post->post_name;
?>
<main class="singleDistWarp <?php echo 'district-'.$slug;?>">
<section class="districtHeader">
    <div class="distBreadcrumb">
    <div class="container">
        <?php the_title("<h1>", "</h1>"); ?>
        <p id="breadcrumbs"><span><span><a href="<?php echo home_url(); ?>">Home</a></span> »
        <span><a href="/district/districts-list/">Districts List</a></span>
        <span class="breadcrumb_last" aria-current="page"> » <?php echo get_the_title($postId); ?></span>
        </span>
       </p>
<div id="dis_id" style="display: none;"><?php echo ordinalToIntAjax(get_the_title()); ?></div>
    </div>
    </div>
</section>
<section class="dist-content">
    <div class="sinDistLeft">
    <div class="captainDetails">
    <?php 
    $captain_name = get_field('captain_name');
    $district_address =  get_field('district_hq_address');
    $division_name = get_field('division_name');

$image = get_field('captains_image');
if( !empty( $image ) ): ?>
<figure>
    <img src="<?php echo $image ; ?>" alt="<?php the_field('captain_name'); ?>" />
    </figure>
<?php endif; ?>
        <div class="captainMeta">
        <?php if(!empty($captain_name)) { ?>
            <label><?php the_field('captain_name'); ?> </label>
            <?php } ?>
            <?php if(!empty($district_address)) { ?>
            <span class="capAdd"><?php the_field('district_hq_address'); ?></span>
            <?php } ?>
            <?php if(!empty($division_name)) { ?>
            <span class="capdivision"><?php the_field('division_name'); ?> Division</span>
            <?php } ?>
        </div>
    </div>
    <div class="distContact">
        <?php 
        $emailfield= get_field('email');
        $phonefield= get_field('phone');
        $twitterlink= get_field('twitter_link');
        ?>
        <?php if(!empty($emailfield)) { ?>
        <p><strong>Email:</strong> <a href="mailto:<?php the_field('email'); ?>"><?php the_field('email'); ?></a></p>
        <?php } 
        if(!empty($phonefield)) { 
        ?>
        <p><strong>Phone Number:</strong> <a href="tel:<?php the_field('phone'); ?>"><?php the_field('phone'); ?></a></p>
        <?php } 
        if(!empty($twitterlink)) { ?>
        <a href="<?php the_field('twitter_link'); ?>" target="_blank">Twitter (X)</a>
        <?php } ?>
        <div class="distFile">
            <div class="leftFileDis">
                <strong>Do you have a neighborhood complaint?</strong>
            </div>
            <a href="<?php bloginfo('url')?>/neighborhood-concern-roll-call-complaint?dist=<?php the_title(); ?>">File a Roll Call Complaint</a>
        </div>
    </div>
    </div>
    
    <div class="sinDistRight">
        <div class="distEventDetailsPopup"></div>
        <div class="eventHead">
             <input type="hidden" id="districtIdForEvent" name="districtIdForEvent" value="<?php echo get_the_ID(); ?>">
            <label>Events</label>
            <input id="getDateRange" type="text" name="dates" placeholder="Date" value="" />
        </div>
<?php   

global $wpdb;
    
    // Custom SQL query to fetch posts
    $query = "
        SELECT p.*
        FROM {$wpdb->posts} p
        LEFT JOIN {$wpdb->postmeta} pm1 ON p.ID = pm1.post_id AND pm1.meta_key = 'start_Date'
        LEFT JOIN {$wpdb->postmeta} pm2 ON p.ID = pm2.post_id AND pm2.meta_key = 'start_time'
        LEFT JOIN {$wpdb->postmeta} pm3 ON p.ID = pm3.post_id AND pm3.meta_key = 'district_relation'
        WHERE p.post_type = 'district_events'
        AND p.post_status = 'publish'  -- Ensure only published posts are retrieved
        AND pm1.meta_value IS NOT NULL
        AND pm2.meta_value IS NOT NULL
        AND (
            pm3.meta_value LIKE '%" . get_the_ID() . "%'  -- Match current post ID in the relation field
            OR pm3.meta_value LIKE '%\"" . get_the_ID() . "\"%'  -- Match serialized array with quotes
            OR pm3.meta_value LIKE '%i:" . get_the_ID() . ";%'  -- Match serialized integer format
        )
        ORDER BY pm1.meta_value ASC,  -- Order by start_date in ascending order
                 STR_TO_DATE(pm2.meta_value, '%H:%i:%s') ASC  -- Order by start_time in ascending order (24-hour format)
    ";
    
    $results = $wpdb->get_results($query);
    $today = $date = date('M j, Y');
    //print_r( $results);
    
         ?>
            <div class="distEventWarp"><div id='event-filterloader'></div>
            <?php if ($results) :
            $count = 0;
            $btncount = 0;
            echo "<ul class='listEvents'>";
            foreach ($results as $post) :
                if ($count >= 4) {
                    break;
                }
                setup_postdata($post); 
                $event= $post->ID;
                $startDate=get_field('start_Date',$event);
                if(!empty($startDate)){
                $dateObject = DateTime::createFromFormat('M d, Y', $startDate);
                $formattedDate = $dateObject->format('Y-m-d');
                }
                $endDate=get_field('end_date',$event);
                $sdate = new DateTime($startDate);
                $edate = new DateTime($endDate);
                $startTime=get_field('start_time',$event);
                $endTime=get_field('end_time',$event);
                
                // echo $today;
                // echo $startDate;
                // if (strtotime($today) < strtotime($startDate)) {
                //     echo "true";
                // } 
            ?>
            <?php 
             if (!empty($endDate) && strtotime($today) <= strtotime($endDate) || !empty($startDate) && strtotime($today) <= strtotime($startDate)) {
                if (($sdate->format('M') == 'Jun' || $sdate->format('M') == 'Jul') && ($edate->format('M') == 'Jun' || $edate->format('M') == 'Jul')) {
                    $dateDisplay="<em>".$sdate->format('F')."</em>".$sdate->format('j')."</br>".$sdate->format('Y');
                    echo "<li><span class='dateWarp'>".(!empty($startDate) ? $dateDisplay:'')."</span><span class='eventTitle'><span><strong>".get_the_title( $event )."</strong>".(!empty($startDate) ? $sdate->format('F j, Y'):'')." ".(!empty($startTime) ? str_replace(['am', 'pm'], ['a.m.', 'p.m.'], $startTime):'').((!empty($endDate))||(!empty($endTime))?" - ":""). (!empty($endDate)? $edate->format('F j, Y'):'')." ".(!empty($endTime) ? str_replace(['am', 'pm'], ['a.m.', 'p.m.'], $endTime):'')."</span><a href='#' class='show-event-details' data-id='".$event."'><svg height='20' width='20' aria-hidden='true' class='e-font-icon-svg e-fas-plus' viewBox='0 0 448 512' xmlns='http://www.w3.org/2000/svg'><path d='M416 208H272V64c0-17.67-14.33-32-32-32h-32c-17.67 0-32 14.33-32 32v144H32c-17.67 0-32 14.33-32 32v32c0 17.67 14.33 32 32 32h144v144c0 17.67 14.33 32 32 32h32c17.67 0 32-14.33 32-32V304h144c17.67 0 32-14.33 32-32v-32c0-17.67-14.33-32-32-32z'></path></svg></a>".do_shortcode('[event date="'.$formattedDate.' '.$startTime.'" timezone="America/New_York" title="'.get_the_title( $event ).'"]')."</span></li>"; 
                } elseif(($sdate->format('M') == 'Jun' || $sdate->format('M') == 'Jul') && ($edate->format('M') != 'Jun' || $edate->format('M') != 'Jul')) {
                    $dateDisplay="<em>".$sdate->format('F')."</em>".$sdate->format('j')."</br>".$sdate->format('Y');
                    echo "<li><span class='dateWarp'>".(!empty($startDate) ? $dateDisplay:'')."</span><span class='eventTitle'><span><strong>".get_the_title( $event )."</strong>".(!empty($startDate)?$sdate->format('F j, Y'):'')." ".(!empty($startTime) ? str_replace(['am', 'pm'], ['a.m.', 'p.m.'], $startTime):'').((!empty($endDate))||(!empty($endTime))?" - ":"").(!empty($endDate) ? $edate->format('M j, Y'):'')." ".(!empty($endTime) ? str_replace(['am', 'pm'], ['a.m.', 'p.m.'], $endTime):'')."</span><a href='#' class='show-event-details' data-id='".$event."'><svg height='20' width='20' aria-hidden='true' class='e-font-icon-svg e-fas-plus' viewBox='0 0 448 512' xmlns='http://www.w3.org/2000/svg'><path d='M416 208H272V64c0-17.67-14.33-32-32-32h-32c-17.67 0-32 14.33-32 32v144H32c-17.67 0-32 14.33-32 32v32c0 17.67 14.33 32 32 32h144v144c0 17.67 14.33 32 32 32h32c17.67 0 32-14.33 32-32V304h144c17.67 0 32-14.33 32-32v-32c0-17.67-14.33-32-32-32z'></path></svg></a>".do_shortcode('[event date="'.$formattedDate.' '.$startTime.'" timezone="America/New_York" title="'.get_the_title( $event ).'"]')."</span></li>"; 
                } elseif(($sdate->format('M') != 'Jun' || $sdate->format('M') != 'Jul') && ($edate->format('M') == 'Jun' || $edate->format('M') == 'Jul')){
                    $dateDisplay="<em>".$sdate->format('M')."</em>".$sdate->format('j')."</br>".$sdate->format('Y');
                    echo "<li><span class='dateWarp'>".(!empty($startDate) ? $dateDisplay:'')."</span><span class='eventTitle'><span><strong>".get_the_title( $event )."</strong>".(!empty($startDate)? $sdate->format('M j, Y'): '')." ".(!empty($startTime) ? str_replace(['am', 'pm'], ['a.m.', 'p.m.'], $startTime):'').((!empty($endDate))||(!empty($endTime))?" - ":"").(!empty($endDate)? $edate->format('F j, Y'): '')." ".(!empty($endTime) ? str_replace(['am', 'pm'], ['a.m.', 'p.m.'], $endTime):'')."</span><a href='#' class='show-event-details' data-id='".$event."'><svg height='20' width='20' aria-hidden='true' class='e-font-icon-svg e-fas-plus' viewBox='0 0 448 512' xmlns='http://www.w3.org/2000/svg'><path d='M416 208H272V64c0-17.67-14.33-32-32-32h-32c-17.67 0-32 14.33-32 32v144H32c-17.67 0-32 14.33-32 32v32c0 17.67 14.33 32 32 32h144v144c0 17.67 14.33 32 32 32h32c17.67 0 32-14.33 32-32V304h144c17.67 0 32-14.33 32-32v-32c0-17.67-14.33-32-32-32z'></path></svg></a>".do_shortcode('[event date="'.$formattedDate.' '.$startTime.'" timezone="America/New_York" title="'.get_the_title( $event ).'"]')."</span></li>"; 
                } else {
                    $dateDisplay="<em>".$sdate->format('M')."</em>".$sdate->format('j')."</br>".$sdate->format('Y');
                    echo "<li><span class='dateWarp'>".(!empty($startDate) ? $dateDisplay:'')."</span><span class='eventTitle'><span><strong>".get_the_title( $event )."</strong>".(!empty($startDate)? $sdate->format('M j, Y'):'')." ".(!empty($startTime) ? str_replace(['am', 'pm'], ['a.m.', 'p.m.'], $startTime):'').((!empty($endDate))||(!empty($endTime))?" - ":"").(!empty($endDate)? $edate->format('M j, Y'):'')." ".(!empty($endTime) ? str_replace(['am', 'pm'], ['a.m.', 'p.m.'], $endTime):'')."</span><a href='#' class='show-event-details' data-id='".$event."'><svg height='20' width='20' aria-hidden='true' class='e-font-icon-svg e-fas-plus' viewBox='0 0 448 512' xmlns='http://www.w3.org/2000/svg'><path d='M416 208H272V64c0-17.67-14.33-32-32-32h-32c-17.67 0-32 14.33-32 32v144H32c-17.67 0-32 14.33-32 32v32c0 17.67 14.33 32 32 32h144v144c0 17.67 14.33 32 32 32h32c17.67 0 32-14.33 32-32V304h144c17.67 0 32-14.33 32-32v-32c0-17.67-14.33-32-32-32z'></path></svg></a>".do_shortcode('[event date="'.$formattedDate.' '.$startTime.'" timezone="America/New_York" title="'.get_the_title( $event ).'"]')."</span></li>"; 
                }
                $count++;
            } 

            ?>
            <?php endforeach;
            echo "</ul>";
            foreach ($results as $post) :
                $event= $post->ID;
                $startDate=get_field('start_Date',$event);
                if (strtotime($today) <= strtotime($startDate)) {
                    $btncount++;
                } 
            endforeach;
            wp_reset_postdata();
            
            if($btncount>0):
                echo "<div class='eventLinkWarp'><a aria-label='view all events' href='".home_url()."/district-events/?eventid=".get_the_ID()."'>View All Events</a></div>";
            endif;
            if($btncount==0):
                echo "No upcoming events found";
            endif;
            
            else:
            echo "Event not found.";
            endif; ?>
            <?php wp_reset_postdata(); ?>
        </div>
    </div>
</section>
<section class="distNews">
<div class="newsTopWarp"><label><?php the_title(); ?> Crime Blotter</label> <a aria-label="view all posts" href="<?php bloginfo('url'); ?>/news-blotter-tags/<?php echo $slug; ?>">View All Posts</a></div>
    <?php $tagTitle= get_the_title();
    $query = new WP_Query( [
    'post_type'      => 'news-blotter',
    'posts_per_page' => '3',
    'tax_query' => array(
      array(
          'taxonomy' => 'news-blotter-tags',
          'field'    => 'slug',
          'terms'    => $tagTitle,
      ),
  ),
] ); ?>
<div class="distNewsRow">
<?php if ( $query->have_posts() ) : ?>
<?php while ( $query->have_posts() ) : $query->the_post(); ?>
<div class="distCol-3">
<?php if ( has_post_thumbnail() ) { ?>
 <a href=<?php the_permalink(); ?>>   
<?php the_post_thumbnail(); ?>
</a>
<?php }
?>
<?php
$post_id = get_the_ID();
$term_names = wp_get_post_terms($post_id, 'news-blotter-cat', array('fields' => 'names')); // returns an array of term names
?>

<span class="catName"><?php echo implode(', ', $term_names); ?> </span>
<div class="disNewMeta">
    <?php $u_time = get_the_time('U'); 
$u_modified_time = get_the_modified_time('U'); 
if ($u_modified_time >= $u_time + 86400) { 
echo "<p>Last Updated "; 
the_modified_time('F j, Y'); 
echo "</p> "; } else {
    echo "<p>Last Updated ".get_the_date('F j, Y')."</p>";
} ?>
   <strong><a href=<?php the_permalink(); ?>><?php the_title(); ?></a> </strong>
   <p><?php the_excerpt(); ?> </p>
</div>
</div>
<?php endwhile; ?>
<?php endif; ?>
<?php wp_reset_postdata(); ?>
</div>
</section>

<?php if(!is_single("77th-district")){ ?>
<div class="dist-crime-data">
    <label class="distcrimeHeading"><?php the_title();?> Crime Statistics</label>
<?php  echo do_shortcode("[districtwise_crime_stats_topsection]") ?>

<div class="dist-crime-data">
<div class="elementor-element elementor-element-b24564f e-con-full e-flex e-con e-child" data-id="b24564f" data-element_type="container">
   <div class="elementor-element elementor-element-bfe7dec e-grid e-con-full e-con e-child" data-id="bfe7dec" data-element_type="container">
      
        
        <div class="elementor-element elementor-element-e79d926 elementor-widget elementor-widget-html" data-id="e79d926" data-element_type="widget" data-widget_type="html.default">
            <div class="elementor-widget-container">
                <div class="date-statistic-filter">
                    <label>Select Date</label>
                    <input title="Select Date" type="text" id="singleDatePickerDistrictWise">
            </div>
         </div>
      </div>
      <div class="crimeCheckWarp">
      <div class="elementor-element elementor-element-9f48aa5 elementor-widget elementor-widget-html" data-id="9f48aa5" data-element_type="widget" data-widget_type="html.default">
         <div class="elementor-widget-container">
            <div class="custom-checkbox">
               <input title="PAST TWO WEEKS" id="weekCheckbox" type="checkbox" checked=""> PAST TWO WEEKS
            </div>
         </div>
      </div>
      <div class="elementor-element elementor-element-8dffb4f elementor-widget elementor-widget-html" data-id="8dffb4f" data-element_type="widget" data-widget_type="html.default">
         <div class="elementor-widget-container">
            <div class="custom-checkbox"><input title="PAST 28 DAYS" id="monthCheckbox" type="checkbox" checked=""> PAST 28 DAYS</div>
         </div>
      </div>
       
      <div class="elementor-element elementor-element-9ae6531 elementor-widget elementor-widget-html" data-id="9ae6531" data-element_type="widget" data-widget_type="html.default">
         <div class="elementor-widget-container">
            <div class="custom-checkbox"><input title="YEAR TO DATE" id="yearCheckbox" type="checkbox" checked=""> YEAR TO DATE</div>
         </div>
      </div>
       </div>
      <div class="elementor-element elementor-element-6fbea8f elementor-widget elementor-widget-button" data-id="6fbea8f" data-element_type="widget" data-widget_type="button.default">
         <div class="elementor-widget-container">
            <div class="elementor-button-wrapper">
               <a class="elementor-button elementor-button-link elementor-size-sm" href="#" id="export-crimedata">
               <span class="elementor-button-content-wrapper">
               <span class="elementor-button-text">Export PDF</span>
               </span>
               </a>
            </div>
         </div>
      </div>
   </div>
</div>
</div>


<div id="all-crime-data-table">
<div class="elementor-shortcode">
<?php  echo do_shortcode("[crime_statistics_data_districtwise]") ?>
</div>
</div>
<?php } ?>
</div>
</main>
<?php 
endwhile;
else :
    _e( 'Sorry, no posts were found.', 'textdomain' );
endif;
?>
<script>
    window.addeventasync = function(){
    addeventatc.settings({
        license : "aVEKSNduhzPzfWpBgmuz336229"
        });
    };


jQuery('#getDateRange').daterangepicker({
        autoUpdateInput: false,
});




</script>

<?php 

get_footer();
?>