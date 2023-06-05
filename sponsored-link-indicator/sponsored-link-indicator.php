<?php
/**
 * Plugin Name: Sponsored Link Indicator
 * Description: This plugin adds an icon to sponsored links.
 * Version: 1.0
 * Author: SpegalDev
 * Author URI: https://Spegal.dev
 **/

defined( 'ABSPATH' ) or die( 'No script kiddies please!' );

function sponsored_link_indicator($content) {
    $icon = get_option('sponsored_link_icon', 'fas fa-ad'); // Default to Font Awesome's ad icon
    $position = get_option('sponsored_link_position', 'before'); // Default to before the link
    $inside = get_option('sponsored_link_inside', '') === '1'; // Default to outside the link
    $space = get_option('sponsored_link_space', '1') === '1'; // Default to having a space

    // Use DOMDocument to parse the HTML
    $dom = new DOMDocument();
    @$dom->loadHTML(mb_convert_encoding($content, 'HTML-ENTITIES', 'UTF-8'));

    // Look for <a> elements with rel attribute containing "sponsored"
    $links = $dom->getElementsByTagName('a');
    foreach ($links as $link) {
        if (strpos($link->getAttribute('rel'), 'sponsored') !== false) {
            $icon_element = $dom->createElement('i');
            $icon_element->setAttribute('class', $icon);

            $space_element = $dom->createTextNode(' ');

            if ($inside) {
                if ($position === 'before') {
                    $link->insertBefore($icon_element, $link->firstChild);
                    if ($space) $link->insertBefore($space_element, $icon_element->nextSibling);
                } else {
                    if ($space) $link->appendChild($space_element);
                    $link->appendChild($icon_element);
                }
            } else {
                if ($position === 'before') {
                    if ($space) $link->parentNode->insertBefore($space_element, $link);
                    $link->parentNode->insertBefore($icon_element, $space ? $space_element : $link);
                } else {
                    if ($space) {
                        if ($link->nextSibling) {
                            $link->parentNode->insertBefore($space_element, $link->nextSibling);
                            $link->parentNode->insertBefore($icon_element, $space_element->nextSibling);
                        } else {
                            $link->parentNode->appendChild($space_element);
                            $link->parentNode->appendChild($icon_element);
                        }
                    } else {
                        if ($link->nextSibling) {
                            $link->parentNode->insertBefore($icon_element, $link->nextSibling);
                        } else {
                            $link->parentNode->appendChild($icon_element);
                        }
                    }
                }
            }
        }
    }

    // Return the modified HTML
    return $dom->saveHTML();
}

add_filter('the_content', 'sponsored_link_indicator');

function sponsored_link_indicator_menu() {
    add_options_page(
        'Sponsored Link Indicator Settings',
        'Sponsored Link Indicator',
        'manage_options',
        'sponsored-link-indicator',
        'sponsored_link_indicator_settings_page'
    );
}
add_action('admin_menu', 'sponsored_link_indicator_menu');

function sponsored_link_indicator_settings_page() {
    ?>
    <div class="wrap">
        <h1>Sponsored Link Indicator Settings</h1>
        <p>Please ensure that any link you desire to display an icon with is appropriately labeled using the attribute <code>rel="sponsored"</code></p>
        <form method="post" action="options.php">
            <?php
            settings_fields('sponsored_link_indicator_settings');
            do_settings_sections('sponsored_link_indicator');
            submit_button();
            ?>
        </form>
        <small><b>Enjoy this plugin? Consider <a href="<?php echo esc_url('https://out.spegal.dev/coffee-wordpress'); ?>" target="_blank" rel="nofollow">buying me a coffee</a> ☕</b></small>
    </div>
    <?php
}

function sponsored_link_indicator_settings() {
    add_settings_section(
        'sponsored_link_indicator_settings',
        'Settings',
        null,
        'sponsored_link_indicator'
    );

    add_settings_field(
        'sponsored_link_icon',
        'Icon Type',
        'sponsored_link_icon_callback',
        'sponsored_link_indicator',
        'sponsored_link_indicator_settings'
    );

    add_settings_field(
        'sponsored_link_position',
        'Icon Position',
        'sponsored_link_position_callback',
        'sponsored_link_indicator',
        'sponsored_link_indicator_settings'
    );

    add_settings_field(
        'sponsored_link_inside',
        'Icon Inside Link',
        'sponsored_link_inside_callback',
        'sponsored_link_indicator',
        'sponsored_link_indicator_settings'
    );

    add_settings_field(
        'sponsored_link_space',
        'Space Between Icon and Text',
        'sponsored_link_space_callback',
        'sponsored_link_indicator',
        'sponsored_link_indicator_settings'
    );

    register_setting('sponsored_link_indicator_settings', 'sponsored_link_space');
    register_setting('sponsored_link_indicator_settings', 'sponsored_link_inside');
    register_setting('sponsored_link_indicator_settings', 'sponsored_link_icon');
    register_setting('sponsored_link_indicator_settings', 'sponsored_link_position');
}
add_action('admin_init', 'sponsored_link_indicator_settings');

function sponsored_link_inside_callback() {
    echo '<input type="checkbox" name="sponsored_link_inside" value="1"' . (get_option('sponsored_link_inside', '') === '1' ? ' checked' : '') . '> Yes';
}

function sponsored_link_space_callback() {
    echo '<input type="checkbox" name="sponsored_link_space" value="1"' . (get_option('sponsored_link_space', '1') === '1' ? ' checked' : '') . '> Yes';
}

function sponsored_link_icon_callback() {
    $icons = array(
        'fas fa-ad' => 'Ad',
        'fab fa-amazon' => 'Amazon',
        'fa-solid fa-square-up-right' => 'Square Up Right',
        'fa-solid fa-arrow-up-right-from-square' => 'Arrow Up Right From Square',
    );

    echo '<select name="sponsored_link_icon">';
    $current_icon = get_option('sponsored_link_icon', 'fas fa-ad');

    foreach ($icons as $class => $name) {
        echo '<option value="' . esc_attr($class) . '"' . ($current_icon === $class ? ' selected' : '') . '>' . esc_html($name) . '</option>';
    }

    echo '</select>';
}

function sponsored_link_position_callback() {
    echo '<select name="sponsored_link_position">';
    echo '<option value="before"' . (get_option('sponsored_link_position', 'before') === 'before' ? ' selected' : '') . '>Before</option>';
    echo '<option value="after"' . (get_option('sponsored_link_position', 'before') === 'after' ? ' selected' : '') . '>After</option>';
    echo '</select>';
}

function sponsored_link_indicator_scripts() {
    // You may want to replace this URL with a more specific version
    wp_enqueue_style('font-awesome', 'https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css');
}
add_action('wp_enqueue_scripts', 'sponsored_link_indicator_scripts');