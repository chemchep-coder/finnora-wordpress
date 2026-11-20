<?php /* Template Name: FINNORA — Upload Your Photo */
get_header();
$design=fin_design_record(get_page_by_path('photo-story',OBJECT,'fin_design'));
$upload_first=true;
if($design)include get_template_directory().'/template-parts/design-builder.php';
else echo '<section class="container fin-design-page"><h1>Photo designs are being prepared.</h1><a href="'.esc_url(fin_design_page_url()).'">Explore designs</a></section>';
get_footer();
