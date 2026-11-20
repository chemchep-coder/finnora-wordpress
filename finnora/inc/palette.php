<?php
defined('ABSPATH') || exit;
function fin_palette_defaults() { return ['fin_green' => '#123d2d', 'fin_snow' => '#ffffff', 'fin_red' => '#d83238']; }
add_action('customize_register', function ($customizer) {
    foreach (['fin_green' => 'Forest green — brand and banner', 'fin_snow' => 'White — background and snow', 'fin_red' => 'Christmas red — shopping buttons'] as $key => $label) {
        $customizer->add_setting($key, ['default' => fin_palette_defaults()[$key], 'sanitize_callback' => 'sanitize_hex_color']);
        $customizer->add_control(new WP_Customize_Color_Control($customizer, $key, ['section' => 'fin_studio', 'label' => $label]));
    }
    $customizer->add_setting('fin_festive_dots', ['default' => true, 'sanitize_callback' => 'fin_checkbox']);
    $customizer->add_control('fin_festive_dots', ['section' => 'fin_studio', 'label' => 'Show subtle Christmas snow dots', 'type' => 'checkbox']);
    $customizer->add_setting('fin_motion', ['default' => true, 'sanitize_callback' => 'fin_checkbox']);
    $customizer->add_control('fin_motion', ['section' => 'fin_studio', 'label' => 'Enable gentle shopping animations', 'type' => 'checkbox']);
});
add_filter('body_class', function ($classes) { if (get_theme_mod('fin_festive_dots', true)) $classes[] = 'fin-festive'; return $classes; });
add_action('wp_enqueue_scripts', function () {
    wp_enqueue_style('fin-palette', get_template_directory_uri() . '/assets/palette.css', ['fin-shopping'], filemtime(get_template_directory() . '/assets/palette.css'));
    $tokens = [];
    foreach (fin_palette_defaults() as $key => $default) $tokens[] = '--' . str_replace('fin_', 'fin-', $key) . ':' . (sanitize_hex_color(get_theme_mod($key, $default)) ?: $default);
    wp_add_inline_style('fin-palette', ':root{' . implode(';', $tokens) . '}');
}, 30);
add_action('wp_enqueue_scripts', function () {
    if (!get_theme_mod('fin_motion', true)) return;
    $dir = get_template_directory();
    wp_enqueue_style('fin-motion', get_template_directory_uri() . '/assets/motion.css', ['fin-palette'], filemtime($dir . '/assets/motion.css'));
    wp_enqueue_script('fin-motion', get_template_directory_uri() . '/assets/motion.js', ['finnora-studio'], filemtime($dir . '/assets/motion.js'), true);
}, 35);
add_action('wp_enqueue_scripts', function () {
    wp_enqueue_style('fin-refinement', get_template_directory_uri() . '/assets/refinement.css', ['fin-palette'], filemtime(get_template_directory() . '/assets/refinement.css'));
    wp_enqueue_style('fin-breathing-room', get_template_directory_uri() . '/assets/breathing-room.css', ['fin-refinement'], filemtime(get_template_directory() . '/assets/breathing-room.css'));
}, 40);
