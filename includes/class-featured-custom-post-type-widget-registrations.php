<?php
/**
 * Featured Custom Post Type Widget For Genesis
 *
 * @package FeaturedCustomPostTypeWidgetForGenesis
 * @author  StudioPress
 * @author  Jo Waltham
 * @author  Pete Favelle
 * @author  Robin Cornett
 * @license GPL-2.0+
 *
 */

 /**
* Please note that most of this code is from the Genesis Featured Post Widget included in the Genesis Framework.
* I have just added support for Custom Post Types.
* Pete has added support for Custom Taxonomies.
*/

class Genesis_Featured_Custom_Post_Type extends WP_Widget {

	/**
	 * Holds widget settings defaults, populated in constructor.
	 *
	 * @var array
	 */
	protected $defaults;

	/**
	 * Constructor. Set the default widget options and create widget.
	 *
	 * @since 0.1.8
	 */
	function __construct() {

		$this->defaults = array(
			'title'                   => '',
			'post_type'               => 'post',
			'tax_term'                => '',
			'posts_num'               => 1,
			'posts_offset'            => 0,
			'orderby'                 => '',
			'order'                   => '',
			'columns'                 => 'full',
			'exclude_displayed'       => 0,
			'show_image'              => 0,
			'image_alignment'         => '',
			'image_size'              => '',
			'show_gravatar'           => 0,
			'gravatar_alignment'      => '',
			'gravatar_size'           => '',
			'show_title'              => 0,
			'show_byline'             => 0,
			'post_info'               => '[post_date] ' . __( 'By', 'featured-custom-post-type-widget-for-genesis' ) . ' [post_author_posts_link] [post_comments]',
			'show_content'            => 'excerpt',
			'content_limit'           => '',
			'more_text'               => __( '[Read More...]', 'featured-custom-post-type-widget-for-genesis' ),
			'extra_num'               => '',
			'extra_title'             => '',
			'more_from_category'      => '',
			'more_from_category_text' => __( 'More Posts from this Category', 'featured-custom-post-type-widget-for-genesis' ),
			'archive_link'            => '',
			'archive_text'            => __( 'View Custom Post Type Archive', 'featured-custom-post-type-widget-for-genesis' ),
		);

		$widget_ops = array(
			'classname'   => 'featured-content featuredpost',
			'description' => __( 'Displays featured custom post types with thumbnails', 'featured-custom-post-type-widget-for-genesis' ),
		);

		$control_ops = array(
			'id_base' => 'featured-custom-post-type',
			'width'   => 505,
			'height'  => 350,
		);

		parent::__construct( 'featured-custom-post-type', __( 'Featured Custom Post Types for Genesis', 'featured-custom-post-type-widget-for-genesis' ), $widget_ops, $control_ops );

		// Register our Ajax handler
		add_action( 'wp_ajax_tax_term_action', array( $this, 'tax_term_action_callback' ) );
		add_action( 'admin_enqueue_scripts', array( $this, 'admin_enqueue' ) );

	}

	/**
	 * Echo the widget content.
	 *
	 * @since 0.1.8
	 *
	 * @param array $args Display arguments including before_title, after_title, before_widget, and after_widget.
	 * @param array $instance The settings for the particular instance of the widget
	 */
	function widget( $args, $instance ) {

		global $wp_query, $_genesis_displayed_ids;

		extract( $args );

		//* Merge with defaults
		$instance = wp_parse_args( (array) $instance, $this->defaults );

		echo $before_widget;

		//* Set up the author bio
		if ( ! empty( $instance['title'] ) )
			echo $before_title . apply_filters( 'widget_title', $instance['title'], $instance, $this->id_base ) . $after_title;

		$query_args = array(
			'post_type' => $instance['post_type'],
			'showposts' => $instance['posts_num'],
			'offset'    => $instance['posts_offset'],
			'orderby'   => $instance['orderby'],
			'order'     => $instance['order'],
		);

		// Extract the custom tax term, if provided
		if ( 'any' !== $instance['tax_term'] ) {
			list( $post_tax, $post_term ) = explode( '/', $instance['tax_term'], 2 );
			$query_args['tax_query'] = array(
				array(
					'taxonomy' => $post_tax,
					'field'    => 'slug',
					'terms'    => $post_term,
				)
			);
		}

		//* Exclude displayed IDs from this loop?
		if ( $instance['exclude_displayed'] )
			$query_args['post__not_in'] = (array) $_genesis_displayed_ids;

		if ( 'full' !== $instance['columns'] ) {
			add_filter( 'post_class', array( $this, 'add_post_class_' . $instance['columns'] ) );
		}

		$wp_query = new WP_Query( $query_args );

		if ( have_posts() ) : while ( have_posts() ) : the_post();

			$_genesis_displayed_ids[] = get_the_ID();

			genesis_markup( array(
				'html5'   => '<article %s>',
				'xhtml'   => sprintf( '<div class="%s">', implode( ' ', get_post_class() ) ),
				'context' => 'entry',
			) );

			$size = $instance['image_size'];
			$image = apply_filters( 'featured_custom_post_type_image', genesis_get_image( array(
				'format'  => 'html',
				'size'    => $instance['image_size'],
				'context' => 'featured-post-widget',
				'attr'    => genesis_parse_attr( 'entry-image-widget' ),
			) ), $size );

			if ( $instance['show_image'] && $image )
				printf( '<a href="%s" title="%s" class="%s">%s</a>', get_permalink(), the_title_attribute( 'echo=0' ), esc_attr( $instance['image_alignment'] ), $image );

			if ( ! empty( $instance['show_gravatar'] ) ) {
				echo '<span class="' . esc_attr( $instance['gravatar_alignment'] ) . '">';
				echo get_avatar( get_the_author_meta( 'ID' ), $instance['gravatar_size'] );
				echo '</span>';
			}

			if ( $instance['show_title'] )
				echo genesis_html5() ? '<header class="entry-header">' : '';

				if ( ! empty( $instance['show_title'] ) ) {

					if ( genesis_html5() )
						printf( '<h2 class="entry-title"><a href="%s" title="%s">%s</a></h2>', get_permalink(), the_title_attribute( 'echo=0' ), get_the_title() );
					else
						printf( '<h2><a href="%s" title="%s">%s</a></h2>', get_permalink(), the_title_attribute( 'echo=0' ), get_the_title() );

				}

				if ( ! empty( $instance['show_byline'] ) && ! empty( $instance['post_info'] ) )
					printf( genesis_html5() ? '<p class="entry-meta">%s</p>' : '<p class="byline post-info">%s</p>', do_shortcode( $instance['post_info'] ) );

			if ( $instance['show_title'] )
				echo genesis_html5() ? '</header>' : '';

			if ( ! empty( $instance['show_content'] ) ) {

				echo genesis_html5() ? '<div class="entry-content">' : '';

				if ( 'excerpt' == $instance['show_content'] ) {
					the_excerpt();
				}
				elseif ( 'content-limit' == $instance['show_content'] ) {
					the_content_limit( (int) $instance['content_limit'], esc_html( $instance['more_text'] ) );
				}
				else {

					global $more;

					$orig_more = $more;
					$more = 0;

					the_content( esc_html( $instance['more_text'] ) );

					$more = $orig_more;

				}

				echo genesis_html5() ? '</div>' : '';

			}

			genesis_markup( array(
				'html5' => '</article>',
				'xhtml' => '</div>',
			) );

		endwhile; endif;

		if ( 'full' !== $instance['columns'] ) {
			remove_filter( 'post_class', array( $this, 'add_post_class_' . $instance['columns'] ) );
		}

		//* Restore original query
		wp_reset_query();

		//* The EXTRA Posts (list)
		if ( ! empty( $instance['extra_num'] ) ) {
			if ( ! empty( $instance['extra_title'] ) )
				echo $before_title . esc_html( $instance['extra_title'] ) . $after_title;

			$offset = intval( $instance['posts_num'] ) + intval( $instance['posts_offset'] );

			$query_args = array(
				'post_type' => $instance['post_type'],
				'showposts' => $instance['extra_num'],
				'offset'    => $offset,
			);

			// Extract the custom tax term, if provided
			if ( 'any' != $instance['tax_term'] ) {
				list( $post_tax, $post_term ) = explode( '/', $instance['tax_term'], 2 );
				$query_args['tax_query'] = array(
					array(
						'taxonomy' => $post_tax,
						'field'    => 'slug',
						'terms'    => $post_term,
					)
				);
			}

			$wp_query = new WP_Query( $query_args );

			$listitems = '';

			if ( have_posts() ) {
				while ( have_posts() ) {
					the_post();
					$_genesis_displayed_ids[] = get_the_ID();
					$extra_title = sprintf( '<a href="%s">%s</a>', get_permalink(), get_the_title() );
					$listitems  .= '<li>';
					$listitems  .= apply_filters( 'featured_custom_post_type_extra_title', $extra_title );
					$listitems  .= '</li>';
				}

				if ( mb_strlen( $listitems ) > 0 )
					printf( '<ul>%s</ul>', $listitems );
			}

			//* Restore original query
			wp_reset_query();
		}

		if ( ! empty( $instance['more_from_category'] ) && ! empty( $instance['more_from_category_text'] ) && 'any' !== $instance['tax_term'] ) {

			list( $post_tax, $post_term ) = explode( '/', $instance['tax_term'], 2 );

			printf(
				'<p class="more-from-category"><a href="%1$s">%2$s</a></p>',
				esc_url( get_term_link( $post_term, $post_tax ) ),
				esc_html( $instance['more_from_category_text'] )
			);
		}

		if ( ! empty( $instance['archive_link'] ) && ! empty( $instance['archive_text'] ) ) {

			$archive_url = get_post_type_archive_link( $instance['post_type'] );
			if( 'post' === $instance[ 'post_type'] ) {
				$postspage   = get_option( 'page_for_posts' );
				$archive_url = get_permalink( get_post( $postspage )->ID );
				$frontpage   = get_option( 'show_on_front' );
				if ( 'posts' === $frontpage ) {
					$archive_url = get_home_url();
				}
			}

			printf(
				'<p class="more-from-category"><a href="%1$s">%2$s</a></p>',
				esc_url( $archive_url ),
				esc_html( $instance['archive_text'] )
			);

		}

		echo $after_widget;

	}

	/**
	 * Update a particular instance.
	 *
	 * This function should check that $new_instance is set correctly.
	 * The newly calculated value of $instance should be returned.
	 * If "false" is returned, the instance won't be saved/updated.
	 *
	 * @since 0.1.8
	 *
	 * @param array $new_instance New settings for this instance as input by the user via form()
	 * @param array $old_instance Old settings for this instance
	 * @return array Settings to save or bool false to cancel saving
	 */
	function update( $new_instance, $old_instance ) {

		$new_instance['title']     = strip_tags( $new_instance['title'] );
		$new_instance['more_text'] = strip_tags( $new_instance['more_text'] );
		$new_instance['post_info'] = wp_kses_post( $new_instance['post_info'] );
		return $new_instance;

	}

	/**
	 * Echo the settings update form.
	 *
	 * @since 0.1.8
	 *
	 * @param array $instance Current settings
	 */
	function form( $instance ) {

		//* Merge with defaults
		$instance = wp_parse_args( (array) $instance, $this->defaults );
		$item     = $this->build_lists( $instance );

		?>
		<p>
			<label for="<?php echo esc_attr( $this->get_field_id( 'title' ) ); ?>"><?php _e( 'Title:', 'featured-custom-post-type-widget-for-genesis' ); ?> </label>
			<input type="text" id="<?php echo esc_attr( $this->get_field_id( 'title' ) ); ?>" name="<?php echo esc_attr( $this->get_field_name( 'title' ) ); ?>" value="<?php echo esc_attr( $instance['title'] ); ?>" class="widefat" />
		</p>

		<div class="genesis-widget-column">

			<div class="genesis-widget-column-box genesis-widget-column-box-top">

				<p>
					<label for="<?php echo esc_attr( $this->get_field_id( 'post_type' ) ); ?>"><?php _e( 'Post Type:', 'featured-custom-post-type-widget-for-genesis' ); ?> </label>
					<select id="<?php echo esc_attr( $this->get_field_id( 'post_type' ) ); ?>" name="<?php echo esc_attr( $this->get_field_name( 'post_type' ) ); ?>" onchange="tax_term_postback('<?php echo esc_attr( $this->get_field_id( 'tax_term' ) ); ?>', this.value);" >

						<?php
						echo '<option value="any" '. selected( 'any', $instance['post_type'], false ) .'>'. __( 'any', 'featured-custom-post-type-widget-for-genesis' ) .'</option>';
						foreach ( $item->post_type_list as $post_type_item ) {
							echo '<option value="'. esc_attr( $post_type_item ) .'"'. selected( esc_attr( $post_type_item ), $instance['post_type'], false ) .'>'. esc_attr( $post_type_item ) .'</option>';
						}

						?>
					</select>
				</p>

				<p>
					<label for="<?php echo esc_attr( $this->get_field_id( 'tax_term' ) ); ?>"><?php _e( 'Category/Term:', 'featured-custom-post-type-widget-for-genesis' ); ?> </label>
					<select id="<?php echo esc_attr( $this->get_field_id( 'tax_term' ) ); ?>" name="<?php echo esc_attr( $this->get_field_name( 'tax_term' ) ); ?>">

						<?php
						echo '<option value="any" '. selected( 'any', $instance['tax_term'], false ) .'>'. __( 'any', 'featured-custom-post-type-widget-for-genesis' ) .'</option>';
						foreach ( $item->tax_term_list as $tax_term_item ) {
							$tax_term_desc = $tax_term_item->taxonomy . '/' . $tax_term_item->name;
							$tax_term_slug = $tax_term_item->taxonomy . '/' . $tax_term_item->slug;
							echo '<option value="'. esc_attr( $tax_term_slug ) .'"'. selected( esc_attr( $tax_term_slug ), $instance['tax_term'], false ) .'>'. esc_attr( $tax_term_desc ) .'</option>';
						}

						?>
					</select>
				</p>

				<p>
					<label for="<?php echo esc_attr( $this->get_field_id( 'posts_num' ) ); ?>"><?php _e( 'Number of Posts to Show:', 'featured-custom-post-type-widget-for-genesis' ); ?> </label>
					<input type="text" id="<?php echo esc_attr( $this->get_field_id( 'posts_num' ) ); ?>" name="<?php echo esc_attr( $this->get_field_name( 'posts_num' ) ); ?>" value="<?php echo esc_attr( $instance['posts_num'] ); ?>" size="2" />
				</p>

				<p>
					<label for="<?php echo esc_attr( $this->get_field_id( 'posts_offset' ) ); ?>"><?php _e( 'Number of Posts to Offset:', 'featured-custom-post-type-widget-for-genesis' ); ?> </label>
					<input type="text" id="<?php echo esc_attr( $this->get_field_id( 'posts_offset' ) ); ?>" name="<?php echo esc_attr( $this->get_field_name( 'posts_offset' ) ); ?>" value="<?php echo esc_attr( $instance['posts_offset'] ); ?>" size="2" />
				</p>

				<p>
					<label for="<?php echo esc_attr( $this->get_field_id( 'orderby' ) ); ?>"><?php _e( 'Order By:', 'featured-custom-post-type-widget-for-genesis' ); ?> </label>
					<select id="<?php echo esc_attr( $this->get_field_id( 'orderby' ) ); ?>" name="<?php echo esc_attr( $this->get_field_name( 'orderby' ) ); ?>">
						<option value="date" <?php selected( 'date', $instance['orderby'] ); ?>><?php _e( 'Date', 'featured-custom-post-type-widget-for-genesis' ); ?></option>
						<option value="title" <?php selected( 'title', $instance['orderby'] ); ?>><?php _e( 'Title', 'featured-custom-post-type-widget-for-genesis' ); ?></option>
						<option value="parent" <?php selected( 'parent', $instance['orderby'] ); ?>><?php _e( 'Parent', 'featured-custom-post-type-widget-for-genesis' ); ?></option>
						<option value="ID" <?php selected( 'ID', $instance['orderby'] ); ?>><?php _e( 'ID', 'featured-custom-post-type-widget-for-genesis' ); ?></option>
						<option value="comment_count" <?php selected( 'comment_count', $instance['orderby'] ); ?>><?php _e( 'Comment Count', 'featured-custom-post-type-widget-for-genesis' ); ?></option>
						<option value="rand" <?php selected( 'rand', $instance['orderby'] ); ?>><?php _e( 'Random', 'featured-custom-post-type-widget-for-genesis' ); ?></option>
					</select>
				</p>

				<p>
					<label for="<?php echo esc_attr( $this->get_field_id( 'order' ) ); ?>"><?php _e( 'Sort Order:', 'featured-custom-post-type-widget-for-genesis' ); ?> </label>
					<select id="<?php echo esc_attr( $this->get_field_id( 'order' ) ); ?>" name="<?php echo esc_attr( $this->get_field_name( 'order' ) ); ?>">
						<option value="DESC" <?php selected( 'DESC', $instance['order'] ); ?>><?php _e( 'Descending (3, 2, 1)', 'featured-custom-post-type-widget-for-genesis' ); ?></option>
						<option value="ASC" <?php selected( 'ASC', $instance['order'] ); ?>><?php _e( 'Ascending (1, 2, 3)', 'featured-custom-post-type-widget-for-genesis' ); ?></option>
					</select>
				</p>

				<p>
					<input id="<?php echo esc_attr( $this->get_field_id( 'exclude_displayed' ) ); ?>" type="checkbox" name="<?php echo esc_attr( $this->get_field_name( 'exclude_displayed' ) ); ?>" value="1" <?php checked( $instance['exclude_displayed'] ); ?>/>
					<label for="<?php echo esc_attr( $this->get_field_id( 'exclude_displayed' ) ); ?>"><?php _e( 'Exclude Previously Displayed Posts?', 'featured-custom-post-type-widget-for-genesis' ); ?></label>
				</p>

				<p>
					<label for="<?php echo esc_attr( $this->get_field_id( 'columns' ) ); ?>"><?php _e( 'Number of Columns:', 'featured-custom-post-type-widget-for-genesis' ); ?> </label>
					<select id="<?php echo esc_attr( $this->get_field_id( 'columns' ) ); ?>" name="<?php echo esc_attr( $this->get_field_name( 'columns' ) ); ?>">
						<option value="full" <?php selected( 'full', $instance['columns'] ); ?>>1</option>
						<option value="one_half" <?php selected( 'one_half', $instance['columns'] ); ?>>2</option>
						<option value="one_third" <?php selected( 'one_third', $instance['columns'] ); ?>>3</option>
						<option value="one_fourth" <?php selected( 'one_fourth', $instance['columns'] ); ?>>4</option>
						<option value="one_sixth" <?php selected( 'one_sixth', $instance['columns'] ); ?>>6</option>
					</select>
				</p>

			</div>

			<div class="genesis-widget-column-box">

				<p>
					<input id="<?php echo esc_attr( $this->get_field_id( 'show_gravatar' ) ); ?>" type="checkbox" name="<?php echo esc_attr( $this->get_field_name( 'show_gravatar' ) ); ?>" value="1" <?php checked( $instance['show_gravatar'] ); ?>/>
					<label for="<?php echo esc_attr( $this->get_field_id( 'show_gravatar' ) ); ?>"><?php _e( 'Show Author Gravatar', 'featured-custom-post-type-widget-for-genesis' ); ?></label>
				</p>

				<p>
					<label for="<?php echo esc_attr( $this->get_field_id( 'gravatar_size' ) ); ?>"><?php _e( 'Gravatar Size:', 'featured-custom-post-type-widget-for-genesis' ); ?> </label>
					<select id="<?php echo esc_attr( $this->get_field_id( 'gravatar_size' ) ); ?>" name="<?php echo esc_attr( $this->get_field_name( 'gravatar_size' ) ); ?>">
						<option value="45" <?php selected( 45, $instance['gravatar_size'] ); ?>><?php _e( 'Small (45px)', 'featured-custom-post-type-widget-for-genesis' ); ?></option>
						<option value="65" <?php selected( 65, $instance['gravatar_size'] ); ?>><?php _e( 'Medium (65px)', 'featured-custom-post-type-widget-for-genesis' ); ?></option>
						<option value="85" <?php selected( 85, $instance['gravatar_size'] ); ?>><?php _e( 'Large (85px)', 'featured-custom-post-type-widget-for-genesis' ); ?></option>
						<option value="125" <?php selected( 125, $instance['gravatar_size'] ); ?>><?php _e( 'Extra Large (125px)', 'featured-custom-post-type-widget-for-genesis' ); ?></option>
					</select>
				</p>

				<p>
					<label for="<?php echo esc_attr( $this->get_field_id( 'gravatar_alignment' ) ); ?>"><?php _e( 'Gravatar Alignment:', 'featured-custom-post-type-widget-for-genesis' ); ?> </label>
					<select id="<?php echo esc_attr( $this->get_field_id( 'gravatar_alignment' ) ); ?>" name="<?php echo esc_attr( $this->get_field_name( 'gravatar_alignment' ) ); ?>">
						<option value="alignnone">- <?php _e( 'None', 'featured-custom-post-type-widget-for-genesis' ); ?> -</option>
						<option value="alignleft" <?php selected( 'alignleft', $instance['gravatar_alignment'] ); ?>><?php _e( 'Left', 'featured-custom-post-type-widget-for-genesis' ); ?></option>
						<option value="alignright" <?php selected( 'alignright', $instance['gravatar_alignment'] ); ?>><?php _e( 'Right', 'featured-custom-post-type-widget-for-genesis' ); ?></option>
						<option value="aligncenter" <?php selected( 'aligncenter', $instance['gravatar_alignment'] ); ?>><?php _e( 'Center', 'featured-custom-post-type-widget-for-genesis' ); ?></option>
					</select>
				</p>

			</div>

			<div class="genesis-widget-column-box">

				<p>
					<input id="<?php echo esc_attr( $this->get_field_id( 'show_image' ) ); ?>" type="checkbox" name="<?php echo esc_attr( $this->get_field_name( 'show_image' ) ); ?>" value="1" <?php checked( $instance['show_image'] ); ?>/>
					<label for="<?php echo esc_attr( $this->get_field_id( 'show_image' ) ); ?>"><?php _e( 'Show Featured Image', 'featured-custom-post-type-widget-for-genesis' ); ?></label>
				</p>

				<p>
					<label for="<?php echo esc_attr( $this->get_field_id( 'image_size' ) ); ?>"><?php _e( 'Image Size:', 'featured-custom-post-type-widget-for-genesis' ); ?> </label>
					<select id="<?php echo esc_attr( $this->get_field_id( 'image_size' ) ); ?>" class="genesis-image-size-selector" name="<?php echo esc_attr( $this->get_field_name( 'image_size' ) ); ?>">
						<?php
						$sizes = genesis_get_image_sizes();
						foreach( (array) $sizes as $name => $size )
							echo '<option value="' . esc_attr( $name ) . '"' . selected( $name, $instance['image_size'], FALSE ) . '>' . esc_html( $name ) . ' ( ' . absint( $size['width'] ) . 'x' . absint( $size['height'] ) . ' )</option>';
						?>
					</select>
				</p>

				<p>
					<label for="<?php echo esc_attr( $this->get_field_id( 'image_alignment' ) ); ?>"><?php _e( 'Image Alignment:', 'featured-custom-post-type-widget-for-genesis' ); ?> </label>
					<select id="<?php echo esc_attr( $this->get_field_id( 'image_alignment' ) ); ?>" name="<?php echo esc_attr( $this->get_field_name( 'image_alignment' ) ); ?>">
						<option value="alignnone">- <?php _e( 'None', 'featured-custom-post-type-widget-for-genesis' ); ?> -</option>
						<option value="alignleft" <?php selected( 'alignleft', $instance['image_alignment'] ); ?>><?php _e( 'Left', 'featured-custom-post-type-widget-for-genesis' ); ?></option>
						<option value="alignright" <?php selected( 'alignright', $instance['image_alignment'] ); ?>><?php _e( 'Right', 'featured-custom-post-type-widget-for-genesis' ); ?></option>
						<option value="aligncenter" <?php selected( 'aligncenter', $instance['image_alignment'] ); ?>><?php _e( 'Center', 'featured-custom-post-type-widget-for-genesis' ); ?></option>
					</select>
				</p>

			</div>

		</div>

		<div class="genesis-widget-column genesis-widget-column-right">

			<div class="genesis-widget-column-box genesis-widget-column-box-top">

				<p>
					<input id="<?php echo esc_attr( $this->get_field_id( 'show_title' ) ); ?>" type="checkbox" name="<?php echo esc_attr( $this->get_field_name( 'show_title' ) ); ?>" value="1" <?php checked( $instance['show_title'] ); ?>/>
					<label for="<?php echo esc_attr( $this->get_field_id( 'show_title' ) ); ?>"><?php _e( 'Show Post Title', 'featured-custom-post-type-widget-for-genesis' ); ?></label>
				</p>

				<p>
					<input id="<?php echo esc_attr( $this->get_field_id( 'show_byline' ) ); ?>" type="checkbox" name="<?php echo esc_attr( $this->get_field_name( 'show_byline' ) ); ?>" value="1" <?php checked( $instance['show_byline'] ); ?>/>
					<label for="<?php echo esc_attr( $this->get_field_id( 'show_byline' ) ); ?>"><?php _e( 'Show Post Info', 'featured-custom-post-type-widget-for-genesis' ); ?></label>
					<input type="text" id="<?php echo esc_attr( $this->get_field_id( 'post_info' ) ); ?>" name="<?php echo esc_attr( $this->get_field_name( 'post_info' ) ); ?>" value="<?php echo esc_attr( $instance['post_info'] ); ?>" class="widefat" />
				</p>

				<p>
					<label for="<?php echo esc_attr( $this->get_field_id( 'show_content' ) ); ?>"><?php _e( 'Content Type:', 'featured-custom-post-type-widget-for-genesis' ); ?> </label>
					<select id="<?php echo esc_attr( $this->get_field_id( 'show_content' ) ); ?>" name="<?php echo esc_attr( $this->get_field_name( 'show_content' ) ); ?>">
						<option value="content" <?php selected( 'content', $instance['show_content'] ); ?>><?php _e( 'Show Content', 'featured-custom-post-type-widget-for-genesis' ); ?></option>
						<option value="excerpt" <?php selected( 'excerpt', $instance['show_content'] ); ?>><?php _e( 'Show Excerpt', 'featured-custom-post-type-widget-for-genesis' ); ?></option>
						<option value="content-limit" <?php selected( 'content-limit', $instance['show_content'] ); ?>><?php _e( 'Show Content Limit', 'featured-custom-post-type-widget-for-genesis' ); ?></option>
						<option value="" <?php selected( '', $instance['show_content'] ); ?>><?php _e( 'No Content', 'featured-custom-post-type-widget-for-genesis' ); ?></option>
					</select>
					<br />
					<label for="<?php echo esc_attr( $this->get_field_id( 'content_limit' ) ); ?>"><?php _e( 'Limit content to', 'featured-custom-post-type-widget-for-genesis' ); ?>
						<input type="text" id="<?php echo esc_attr( $this->get_field_id( 'content_limit' ) ); ?>" name="<?php echo esc_attr( $this->get_field_name( 'content_limit' ) ); ?>" value="<?php echo esc_attr( intval( $instance['content_limit'] ) ); ?>" size="3" />
						<?php _e( 'characters', 'featured-custom-post-type-widget-for-genesis' ); ?>
					</label>
				</p>

				<p>
					<label for="<?php echo esc_attr( $this->get_field_id( 'more_text' ) ); ?>"><?php _e( 'More Text (if applicable):', 'featured-custom-post-type-widget-for-genesis' ); ?> </label>
					<input type="text" id="<?php echo esc_attr( $this->get_field_id( 'more_text' ) ); ?>" name="<?php echo esc_attr( $this->get_field_name( 'more_text' ) ); ?>" value="<?php echo esc_attr( $instance['more_text'] ); ?>" />
				</p>

			</div>

			<div class="genesis-widget-column-box">

				<p><?php _e( 'To display an unordered list of more posts from this category, please fill out the information below', 'featured-custom-post-type-widget-for-genesis' ); ?>:</p>

				<p>
					<label for="<?php echo esc_attr( $this->get_field_id( 'extra_title' ) ); ?>"><?php _e( 'Title:', 'featured-custom-post-type-widget-for-genesis' ); ?> </label>
					<input type="text" id="<?php echo esc_attr( $this->get_field_id( 'extra_title' ) ); ?>" name="<?php echo esc_attr( $this->get_field_name( 'extra_title' ) ); ?>" value="<?php echo esc_attr( $instance['extra_title'] ); ?>" class="widefat" />
				</p>

				<p>
					<label for="<?php echo esc_attr( $this->get_field_id( 'extra_num' ) ); ?>"><?php _e( 'Number of Posts to Show:', 'featured-custom-post-type-widget-for-genesis' ); ?> </label>
					<input type="text" id="<?php echo esc_attr( $this->get_field_id( 'extra_num' ) ); ?>" name="<?php echo esc_attr( $this->get_field_name( 'extra_num' ) ); ?>" value="<?php echo esc_attr( $instance['extra_num'] ); ?>" size="2" />
				</p>

			</div>

			<div class="genesis-widget-column-box">

				<p>
					<input id="<?php echo esc_attr( $this->get_field_id( 'more_from_category' ) ); ?>" type="checkbox" name="<?php echo esc_attr( $this->get_field_name( 'more_from_category' ) ); ?>" value="1" <?php checked( $instance['more_from_category'] ); ?>/>
					<label for="<?php echo esc_attr( $this->get_field_id( 'more_from_category' ) ); ?>"><?php _e( 'Show Category Archive Link', 'featured-custom-post-type-widget-for-genesis' ); ?></label>
				</p>

				<p>
					<label for="<?php echo esc_attr( $this->get_field_id( 'more_from_category_text' ) ); ?>"><?php _e( 'Link Text:', 'featured-custom-post-type-widget-for-genesis' ); ?> </label>
					<input type="text" id="<?php echo esc_attr( $this->get_field_id( 'more_from_category_text' ) ); ?>" name="<?php echo esc_attr( $this->get_field_name( 'more_from_category_text' ) ); ?>" value="<?php echo esc_attr( $instance['more_from_category_text'] ); ?>" class="widefat" />
				</p>

				<p>
					<input id="<?php echo esc_attr( $this->get_field_id( 'archive_link' ) ); ?>" type="checkbox" name="<?php echo esc_attr( $this->get_field_name( 'archive_link' ) ); ?>" value="1" <?php checked( $instance['archive_link'] ); ?>/>
					<label for="<?php echo esc_attr( $this->get_field_id( 'archive_link' ) ); ?>"><?php _e( 'Show Archive Link', 'featured-custom-post-type-widget-for-genesis' ); ?></label>
				</p>
				<p>
					<label for="<?php echo esc_attr( $this->get_field_id( 'archive_text' ) ); ?>"><?php _e( 'Link Text:', 'featured-custom-post-type-widget-for-genesis' ); ?> </label>
					<input type="text" id="<?php echo esc_attr( $this->get_field_id( 'archive_text' ) ); ?>" name="<?php echo esc_attr( $this->get_field_name( 'archive_text' ) ); ?>" value="<?php echo esc_attr( $instance['archive_text'] ); ?>" class="widefat" />
				</p>

			</div>

		</div>
		<?php

	}

	/**
	 * build post_type and taxonomy lists for widget form use
	 * @param  [type] $instance [description]
	 * @return $item           list of post_types and list of taxonomies
	 */
	function build_lists( $instance ) {

		//* Merge with defaults
		$instance = wp_parse_args( (array) $instance, $this->defaults );

		$item = new stdClass();

		//* Fetch a list of possible post types
		$args = array(
			'public'   => true,
			'_builtin' => false,
		);
		$output = 'names';
		$item->post_type_list = get_post_types( $args, $output );

		//* Add posts to that post_type_list
		$item->post_type_list['post'] = 'post';

		//* And a list of available taxonomies for the current post type
		if ( 'any' == $instance['post_type'] ) {
			$taxonomies = get_taxonomies();
		} else {
			$taxonomies = get_object_taxonomies( $instance['post_type'] );
		}

		//* And from there, a list of available terms in that tax
		$tax_args = array(
			'hide_empty' => 0,
		);
		$item->tax_term_list = get_terms( $taxonomies, $tax_args );
		usort( $item->tax_term_list, array( $this, 'tax_term_compare' ) );

		return $item;
	}

	/**
	 * build list of taxonomies for ajax revised dropdown
	 * @return $item list of taxonomies
	 */
	function build_ajax_list() {

		$item = new stdClass;
		//* Fetch a list of available taxonomies for the current post type
		if ( 'any' == $_POST['post_type'] ) {
			$taxonomies = get_taxonomies();
		} else {
			$taxonomies = get_object_taxonomies( $_POST['post_type'] );
		}

		//* And from there, a list of available terms in that tax
		$tax_args = array(
			'hide_empty' => 0,
		);
		$item->tax_term_list = get_terms( $taxonomies, $tax_args );
		usort( $item->tax_term_list, array( $this, 'tax_term_compare' ) );

		return $item;

	}

	/**
	 * Comparison function to allow custom taxonomy terms to be displayed
	 * alphabetically. Required because the display is a compound of term
	 * *and* taxonomy.
	 */
	function tax_term_compare( $a, $b ) {
		if ( $a->taxonomy == $b->taxonomy ) {
			return ( $a->slug < $b->slug ) ? -1 : 1;
		}
		return ( $a->taxonomy <  $b->taxonomy )? -1 : 1;
	}

	/**
	 * Enqueues the small bit of Javascript which will handle the Ajax
	 * callback to correctly populate the custom term dropdown.
	 */
	function admin_enqueue() {
		$screen = get_current_screen()->id;
		if ( in_array( $screen, array( 'widgets', 'customize' ) ) ) {
			wp_enqueue_script( 'tax-term-ajax-script', plugins_url( '/ajax_handler.js', __FILE__ ), array('jquery') );
			wp_localize_script( 'tax-term-ajax-script', 'ajax_object', array( 'ajax_url' => admin_url( 'admin-ajax.php' ) ) );
		}
	}

	/**
	 * Handles the callback to populate the custom term dropdown. The
	 * selected post type is provided in $_POST['post_type'], and the
	 * calling script expects a JSON array of term objects.
	 */
	function tax_term_action_callback() {

		$item = $this->build_ajax_list();

		//* Build an appropriate JSON response containing this info
		$taxes['any'] = 'any';
		foreach ( $item->tax_term_list as $tax_term_item ) {
			$taxes[$tax_term_item->taxonomy . '/' . $tax_term_item->slug] =
				$tax_term_item->taxonomy . '/' . $tax_term_item->name;
		}

		//* And emit it
		if ( function_exists( 'wp_json_encode' ) ) {
			echo wp_json_encode( $taxes );
		}
		else {
			echo json_encode( $taxes );
		}
		die();

	}

	function add_column_classes( $classes, $columns ) {
		global $wp_query;

		//* Bail if we don't have a column number or the one we do have is invalid.
		if ( ! isset( $columns ) || ! in_array( $columns, array( 2, 3, 4, 6 ) ) ) {
			return;
		}

		$classes = array();

		$column_classes = array(
			2 => 'one-half',
			3 => 'one-third',
			4 => 'one-fourth',
			6 => 'one-sixth'
		);

		//* Add the appropriate column class.
		$classes[] = 'grid';
		$classes[] = $column_classes[absint($columns)];

		//* Add an "odd" class to allow for more control of grid clollapse.
		if ( ( $wp_query->current_post + 1 ) % 2 ) {
			$classes[] = 'odd';
		}

		if ( 0 === $wp_query->current_post || 0 === $wp_query->current_post % $columns ) {
			$classes[] = 'first';
		}

		return $classes;
	}

	/**
	 * Set up a grid of one-half elements for use in a post_class filter.
	 *
	 * @since      2.0.0
	 * @category   Grid Loop
	 * @param      $classes array An array of the current post classes
	 * @return     $classes array The current post classes with the grid appended
	 * @author     Rob Neu
	 */
	function add_post_class_one_half( $classes ) {
		return array_merge( (array) $this->add_column_classes( $classes, 2 ), $classes );
	}

	/**
	 * Set up a grid of one-third elements for use in a post_class filter.
	 *
	 * @since      2.0.0
	 * @category   Grid Loop
	 * @param      $classes array An array of the current post classes
	 * @return     $classes array The current post classes with the grid appended
	 * @author     Rob Neu
	 */
	function add_post_class_one_third( $classes ) {
		return array_merge( (array) $this->add_column_classes( $classes, 3 ), $classes );
	}

	/**
	 * Set up a grid of one-fourth elements for use in a post_class filter.
	 *
	 * @since      2.0.0
	 * @category   Grid Loop
	 * @param      $classes array An array of the current post classes
	 * @return     $classes array The current post classes with the grid appended
	 * @author     Rob Neu
	 */
	function add_post_class_one_fourth( $classes ) {
		return array_merge( (array) $this->add_column_classes( $classes, 4 ), $classes );
	}

	/**
	 * Set up a grid of one-sixth elements for use in a post_class filter.
	 *
	 * @since      2.0.0
	 * @category   Grid Loop
	 * @param      $classes array An array of the current post classes
	 * @return     $classes array The current post classes with the grid appended
	 * @author     Rob Neu
	 */
	function add_post_class_one_sixth( $classes ) {
		return array_merge( (array) $this->add_column_classes( $classes, 6 ), $classes );
	}

}
