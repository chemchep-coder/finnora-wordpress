<?php get_header(); ?><section class="container section"><header class="page-intro"><h1>Results for<br><em>“<?php echo esc_html(get_search_query()); ?>”</em></h1></header><?php get_search_form(); $design_term=get_search_query(false);$matching_designs=array_values(array_filter(fin_design_records(),function($d)use($design_term){return $design_term&&stripos($d['name'].' '.$d['description'].' '.implode(' ',array_merge(fin_design_term_slugs($d['id'],'fin_topic'),fin_design_term_slugs($d['id'],'fin_style'))),$design_term)!==false;}));if($matching_designs):?><h2 class="fin-search-design-title">Design ideas</h2><div class="fin-design-grid"><?php foreach(array_slice($matching_designs,0,12) as $design)fin_design_card($design);?></div><?php endif; ?><div class="fin-search-results"><?php if (have_posts()) : while (have_posts()) : the_post(); ?><article><h2><a href="<?php the_permalink(); ?>"><?php the_title(); ?></a></h2><p><?php echo esc_html(wp_trim_words(get_the_excerpt(), 30)); ?></p><a class="text-link" href="<?php the_permalink(); ?>">View details ↗</a></article><?php endwhile; else : ?><p>No matching products or stories. Try another search or <a class="text-link" href="<?php echo esc_url(fin_shop_url()); ?>">explore the collection</a>.</p><?php endif; ?></div>
<?php
$term = remove_accents(get_search_query(false));
$has_products = function_exists('wc_get_products') && wc_get_products(['status' => 'publish', 'limit' => 1]);
if ($term && !$has_products) {
    $matches = array_filter(fin_catalog(), fn($p) => stripos(remove_accents($p['vi'] . ' ' . $p['en'] . ' ' . $p['descVi']), $term) !== false);
    if ($matches) { echo '<h2 class="search-demo-heading">From the demo collection</h2><div class="product-grid">'; foreach ($matches as $p) fin_product_card($p); echo '</div>'; }
}
if ($term && !get_option('fin_studio_imported')) {
    $stories = array_filter(fin_story_data(), fn($s) => stripos(remove_accents($s['title'] . ' ' . $s['excerpt']), $term) !== false);
    if ($stories) { echo '<h2 class="search-demo-heading">From the studio journal</h2><div class="story-grid">'; foreach ($stories as $slug => $story) fin_story_card($slug, $story); echo '</div>'; }
}
the_posts_pagination(); ?></section><?php get_footer(); ?>
