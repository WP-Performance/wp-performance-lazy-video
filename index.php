<?php

/**
 * Plugin Name: WP Performance Lazy Video native from core/video block
 * Description: Add lazy loading on video tag
 * Update URI:  wp-performance-lazy-video
 * Version:     0.0.1
 * Author:      Faramaz Pat <info@goodmotion.fr>
 * License:     MIT License
 * Domain Path: /languages
 * Text Domain: wp-performance-lazy-video
 **/

namespace WPPerformance\LazyVideo;


defined('ABSPATH') || exit;

/**
 * parse html
 */
function parse($string)
{

    $document = new \DOMDocument();
    // hide error syntax warning
    libxml_use_internal_errors(true);

    $document->loadHTML(mb_convert_encoding($string, 'HTML-ENTITIES', 'UTF-8'));
    $xpath = new \DOMXpath($document);

    $videos = $xpath->query("//*/video");
    if (!$videos) {
        return ['content' => $string, 'src' => null];
    }

    foreach ($videos as $key => $node) {
        $parentClasses = $node->parentNode->getAttribute('class');
        if (str_contains($parentClasses, 'wp-block-video')) {
            // replace src by data-src
            $src = $node->getAttribute('src');
            $node->setAttribute('data-src', $src);
            $node->removeAttribute('src');
            // add class b-lazy
            $class = $node->getAttribute('class');
            $node->setAttribute('class', $class . ' wp-video-lazy');
        }
    }

    return ['content' => $document->saveHTML(), 'src' => $src];
}

/** find core/video block */
add_filter(
    'render_block',
    function ($block_content, $block) {
        if ($block['blockName'] === 'core/video') {
            $content = $block['innerHTML'];
            $parsing = namespace\parse($content);
            if ($parsing['src'] !== null) {
                add_action('wp_head', function () use ($parsing) {
                    echo '<link rel="preload" as="video" href="' . $parsing['src'] . '">';
                });
            }

            return $parsing['content'];
        }
        return $block_content;
    },
    10,
    2
);

/**
 * add script to front
 */
function frontend_scripts()
{
    if (has_block('core/video')) {
        wp_enqueue_script(
            'wp-performance-lazy-video-front',
            plugins_url('js/index.js', __FILE__),
            [],
            filemtime(plugin_dir_path(__FILE__) . 'js/index.js')
        );
    }
}

add_action('wp_enqueue_scripts', __NAMESPACE__ . '\frontend_scripts');
