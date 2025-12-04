<?php
/**
 * The template for displaying OIS single posts.
 *
 * @link https://developer.wordpress.org/themes/basics/template-hierarchy/#single-post
 *
 * @package Astra
 * @since 1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly.
}

get_header(); ?>

<main class="singleOisWarp">

<?php if ( have_posts() ) : while ( have_posts() ) : the_post(); ?>
<section class="oistHeader">
    <div class="container">
        <h1>Officer Involved Shootings</h1>
        <p id="breadcrumbs"><span><span><a href="<?php echo home_url(); ?>">Home</a></span> »
        <span><a href="/ois/">Philly Police Officer Involved Shootings</a></span>
        <span class="breadcrumb_last" aria-current="page"> » <?php echo 'PS'.get_the_title(); ?></span>
    </div>
</section>
    <h2>PS<?php the_title(); ?></h2>
    <p class="news-blotter-content-date"><time datetime="<?php echo get_the_date('c'); ?>"><?php echo get_the_date(); ?></time></p>
<div class="ois-content-area">
<?php the_content() ?>
</div>
<div class="oisSingleNav">
<nav id="nav-single">
    <?php
    $all_posts = get_posts(array(
        'post_type' => 'ois',
        'posts_per_page' => -1,
        'orderby' => 'title',
        'order' => 'ASC',
        'fields' => 'ids', // We only need post IDs
    ));

    $current_post_id = get_the_ID();
    $current_index = array_search($current_post_id, $all_posts);
    
    $prev_post_id = isset($all_posts[$current_index - 1]) ? $all_posts[$current_index - 1] : null;
    $next_post_id = isset($all_posts[$current_index + 1]) ? $all_posts[$current_index + 1] : null;

    $prevpermalink = $prev_post_id ? get_permalink($prev_post_id) : '';
    $prevtitle = $prev_post_id ? get_the_title($prev_post_id) : '';

    $nextpermalink = $next_post_id ? get_permalink($next_post_id) : '';
    $nexttitle = $next_post_id ? get_the_title($next_post_id) : '';
    ?>

    <span class="nav-previous">
        <?php if ($prev_post_id): ?>
            <a href="<?php echo esc_url($prevpermalink); ?>"><?php echo '<< ' .'PS'.esc_html($prevtitle); ?></a>
        <?php endif; ?>
    </span>

    <span class="nav-next">
        <?php if ($next_post_id): ?>
            <a href="<?php echo esc_url($nextpermalink); ?>"><?php echo 'PS'.esc_html($nexttitle) . ' >>'; ?></a>
        <?php endif; ?>
    </span>
</nav>
</div>


<?php endwhile; else :
    _e( 'Sorry, no posts were found.', 'textdomain' );
endif;
?>

</main>


<?php get_footer(); ?>

