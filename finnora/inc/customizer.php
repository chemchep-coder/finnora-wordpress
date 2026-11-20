<?php
defined('ABSPATH') || exit;
add_action('customize_register', function ($wp_customize) {
    $wp_customize->add_section('fin_studio', ['title' => 'FINNORA — Design & contact', 'priority' => 30]);
    $fields = [
        'fin_announcement' => ['Announcement bar', 'Thoughtful gifts. A little Christmas magic.', 'text'],
        'fin_hero_title' => ['Homepage headline', 'Christmas styles.', 'text'],
        'fin_hero_emphasis' => ['Second headline line', 'Gifts made personal.', 'text'],
        'fin_hero_description' => ['Homepage introduction', 'Discover festive clothing, everyday accessories and thoughtful gifts, with personalization on selected products.', 'textarea'],
        'fin_contact_email' => ['Contact email (leave empty to disable sending)', '', 'email'],
        'fin_contact_location' => ['Studio location', 'An independent online studio · Vietnam', 'text'],
        'fin_footer_note' => ['Footer note', 'A print-on-demand demonstration. Images are illustrative; ordering depends on the store settings.', 'textarea'],
    ];
    foreach ($fields as $id => [$label, $default, $type]) {
        $wp_customize->add_setting($id, ['default' => $default, 'sanitize_callback' => $type === 'email' ? 'sanitize_email' : 'sanitize_textarea_field']);
        $wp_customize->add_control($id, ['section' => 'fin_studio', 'label' => $label, 'type' => $type]);
    }
    $wp_customize->add_setting('fin_hero_image', ['sanitize_callback' => 'esc_url_raw']);
    $wp_customize->add_control(new WP_Customize_Image_Control($wp_customize, 'fin_hero_image', ['label' => 'Homepage image', 'section' => 'fin_studio']));
});
