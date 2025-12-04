<?php
get_header(); 
?>
<?php 
if ( have_posts() ) : while ( have_posts() ) : the_post(); ?>
<?php 
display_news_header(); ?>
<div id="news-blotter-container" class="news-blotter-container">
    <div id="news-blotter-content" class="news-blotter-content">
    <div class="news-filter"><?php display_news_blotter_filter() ?></div>
            <div class="news-blotter-full-section">
            <h2><?php the_title(); ?></h2>
            <p class="news-blotter-content-date"><time datetime="<?php echo get_the_date('c'); ?>"><?php echo get_the_date(); ?></time></p>
            <div class="news-blotter-content-img">
            <?php if ( has_post_thumbnail() ) : ?>
                <img src="<?php echo get_the_post_thumbnail_url(get_the_ID(), 'full'); ?>" alt="<?php the_title(); ?>">
            <?php endif; ?>
            </div>

            <div class="news-blotter-content">
            <?php the_content(); ?>
            </div>

            <?php 
            // Check if the pcf_video_url field exists and is not empty
            $pcf_video_url = get_field('pcf_video_url');
            $videoTitle= get_the_title();
            $pcf_video_sound = get_field('pcf_video_sound');
            $video_embed = '';

            if ( $pcf_video_url ) {
                // Determine the video type and generate the embed code
                $video_type = get_video_type($pcf_video_url);

                if ($video_type === 'youtube') {
                    if (preg_match('/(?:youtube\.com\/(?:[^\/]+\/.+\/|(?:v|e(?:mbed)?)\/|.*[?&]v=)|youtu\.be\/)([^"&?\/\s]{11})/i', $pcf_video_url, $matches)) {
                        $youtube_id = $matches[1];
                        $video_embed = '<div class="philly-video"><iframe title="'.$videoTitle.'" width="560" height="315" src="https://www.youtube.com/embed/' . esc_attr($youtube_id) . '" frameborder="0" allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture" allowfullscreen></iframe></div>';
                    }
                }

                // Display the video embed if available
                if ( $video_embed ) : ?>
                    <div class="video-section">
                        <?php echo $video_embed; ?>
                        <?php if ( $pcf_video_sound ) : ?>
                            <p class="news-blotter-content-Sound"><?php echo $pcf_video_sound === 'With Sound' ? '' : 'Note: This video has no audio.'; ?></p>
                        <?php endif; ?>
                    </div>
                <?php endif; 
            } ?>

            <?php 
            // Display the news_blotter_description ACF field content if it exists
            $news_blotter_description = get_field('news_blotter_description');
            if ( $news_blotter_description ) : ?>
                <div class="news-blotter-description">
                    <?php echo $news_blotter_description; ?>
                </div>
            <?php endif; ?>
            
            <div class="tags">
                <span class="post_tags"><strong>Tags: </strong></span>
                <?php 
                $post_tags = get_the_terms(get_the_ID(), 'news-blotter-tags');
                if ($post_tags && !is_wp_error($post_tags)) {
                    foreach ($post_tags as $tag) {
                        echo '<span class="post_tags_show"><a aria-label="'.$tag->name.'" href="' . get_term_link($tag) . '">' . $tag->name . '</a></span>';
                    }
                }
                ?>
            </div>
    </div>
    </div>
    <?php endwhile; else : ?>

<p><?php _e('Sorry, no posts matched your criteria.'); ?></p>

<?php endif; ?>
    <?php
    // Call the function to display the sidebar
    display_news_blotter_sidebar();
    ?>
</div>

<?php
get_footer();
