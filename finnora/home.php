<?php /* Template Name: FINNORA — Journal */ get_header(); ?><section class="container section-top"><?php fin_breadcrumb('Journal'); ?><header class="page-intro"><h1>Stories from the studio.<br><em>Inspiration for everyday.</em></h1><p>Ideas, colour and the little things worth keeping.</p></header><nav class="filter-chips" aria-label="Journal categories"><a class="selected" href="<?php echo esc_url(fin_page_url('journal')); ?>">All stories</a><?php foreach (get_categories(['hide_empty' => true, 'number' => 8]) as $cat) : ?><a href="<?php echo esc_url(get_category_link($cat)); ?>"><?php echo esc_html($cat->name); ?></a><?php endforeach; ?></nav>
<?php if (is_home() && have_posts()) : ?><div class="story-grid"><?php while (have_posts()) { the_post(); get_template_part('template-parts/post-card'); } ?></div><?php the_posts_pagination(['prev_text' => '← Previous', 'next_text' => 'Next →']); ?><?php else :
$page_number = max(1, absint(fin_input('fin_pg')));
$journal = new WP_Query(['post_type' => 'post', 'posts_per_page' => 9, 'paged' => $page_number, 'ignore_sticky_posts' => true]);
if ($journal->have_posts()) {
    echo '<div class="story-grid">';
    while ($journal->have_posts()) { $journal->the_post(); get_template_part('template-parts/post-card'); }
    echo '</div><nav class="pagination" aria-label="Journal pages">' . wp_kses_post((string) paginate_links(['base' => add_query_arg('fin_pg', '%#%'), 'format' => '', 'current' => $page_number, 'total' => $journal->max_num_pages])) . '</nav>';
    wp_reset_postdata();
} elseif ($page_number === 1) { echo '<div class="story-grid">'; foreach (fin_story_data() as $slug => $story) fin_story_card($slug, $story); echo '</div>'; }
else echo '<p>There are no more stories here. <a href="' . esc_url(fin_page_url('journal')) . '">Back to the first page</a></p>';
endif; ?></section><?php get_footer(); ?>
