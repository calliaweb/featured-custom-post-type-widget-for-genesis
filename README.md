# Featured Custom Post Type Widget for Genesis

WordPress plugin that adds a widget to display Featured Custom Post Types for the Genesis Framework

## Description

WordPress plugin that adds a widget to display Featured Custom Post Types for the Genesis Framework. Supports Custom Taxonomies.

## Requirements

Genesis 2.0+

## Installation

### Upload

1. Download the latest tagged archive (choose the "zip" option).
2. Go to the __Plugins -> Add New__ screen and click the __Upload__ tab.
3. Upload the zipped archive directly.
4. Go to the Plugins screen and click __Activate__.

### Manual

1. Download the latest tagged archive (choose the "zip" option).
2. Unzip the archive.
3. Copy the folder to your `/wp-content/plugins/` directory.
4. Go to the Plugins screen and click __Activate__.

Check out the Codex for more information about [installing plugins manually](http://codex.wordpress.org/Managing_Plugins#Manual_Plugin_Installation).

### Git

Using git, browse to your /wp-content/plugins/ directory and clone this repository:

git clone git@github.com:calliaweb/featured-custom-post-type-widget-for-genesis.git

Then go to your Plugins screen and click Activate.

## Frequently Asked Questions

If you are having styling issues with the columns, try adding this to your stylesheet:

```css
.widget {
    overflow: hidden;
}
```

### Can I add the featured image to the unordered list of more posts from this category?

Yes, now you can, by using a filter. Example:

```php
add_filter( 'featured_custom_post_type_extra_title', 'prefix_add_thumbs_extra_posts', 10, 2 );
function prefix_add_thumbs_extra_posts( $extra_title ) {
    $image = get_the_post_thumbnail( get_the_ID(), 'thumbnail', array( 'class' => 'alignleft', 'alt' => the_title_attribute( 'echo=0' ) ) );

    $extra_title = sprintf( '<a href="%s">%s%s</a>', get_permalink(), $image, get_the_title() );

    return $extra_title;
}
```

## Credits
Most of the code in this plugin is from the <a href="http://www.studiopress.com/">StudioPress</a> Genesis Featured Post Widget and I've just added Custom Post Type Support.

Thanks to <a href="https://github.com/ahnlak">Pete Favelle</a> for adding Custom Taxonomy support.

Thank you to <a href="https://github.com/robincornett">Robin Cornett</a> for all 1.2 and 2.0 improvements and bug fixes.

### Changelog

#### 2.1.0
* added filter for featured post image
* added filter for extra posts list
* bugfix: term archive link works for any term, not just categories
* bugfix: post type archive link works for posts in addition to custom post types

#### 2.0.0
* new feature: display posts in columns within the widget
* bugfix: ajax list now sorts properly

#### 1.2.0
* new option for CPT archive link
* set ajax to load conditionally

#### 1.0.0
* initial release on GH
