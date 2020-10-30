<?php

/**
 * Plugin Name:       Gutenberg Block Custom Post Types
 * Description:       Custom Gutenberg Block for Registered Custom Post Types. You can set, which Post Types will be displayed and you can set the number of listed posts as well.
 * Version:           1.0.0
 * Requires at least: 5.2
 * Author:            IshyoBoy s.r.o.
 * Author URI:        https://ishyoboy.com/
 * License URI:       https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain:       gutenberg-block-custom-post-types
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

if ( ! class_exists( 'Gutenberg_Block_Custom_Post_Types' ) ) {
	class Gutenberg_Block_Custom_Post_Types {

		public $custom_post_types = array();

		public function __construct() {
			register_activation_hook( __FILE__, array( $this, 'activate' ) );
			register_deactivation_hook( __FILE__, array( $this, 'deactivate' ) );
		}

		/**
		 * Create custom Gutenberg Block for listing all Custom Post Types.
		 */
		public function create_gutenberg_block() {

			// Get all built and not-build wordpress Post Types.
			$this->get_post_types( true );
			$this->get_post_types( false );

			// When the plugin Meta Box AIO is activated, register custom Gutenberg Block for Custom Post Types.
			if ( in_array( 'meta-box-aio/meta-box-aio.php', apply_filters( 'active_plugins', get_option( 'active_plugins' ) ) ) ) {
				//plugin is activated
				add_filter( 'rwmb_meta_boxes', function ( $meta_boxes ) {
					$meta_boxes[] = [
						'title'           => esc_html__( 'Gutenberg Block - Custom Post Type Listing', 'gutenberg-block-custom-post-types' ),
						'id'              => 'gutenberg-block-custom-post-types',
						'description'     => esc_html__( 'Custom Gutenberg Block for listing of Custom Post Types based on the selected names of the custom post types and the number of displayed posts randomly', 'gutenberg-block-custom-post-types' ),
						'type'            => 'block',
						'icon'            => 'awards',
						'context'         => 'side',
						'render_callback' => array( $this, 'gutenberg_block_custom_post_types_callback' ),
						'render_template' => array( $this, 'gutenberg_block_custom_post_types_callback' ),
						'enqueue_assets'  => function () {
							wp_enqueue_style( 'custom-gutenberg-block-stylesheet', '/wp-content/plugins/gutenberg-block-custom-post-types/blocks/custom-post-types/block-stylesheet.scss', [], '1.8.1' );
							wp_enqueue_script( 'custom-gutenberg-block-script', '/wp-content/plugins/gutenberg-block-custom-post-types/blocks/custom-post-types/block-script.js', array( 'jquery' ), '', true );
						},
						// Block fields.
						'fields'          => [
							[
								'name'            => esc_html__( 'Custom Post Types', 'gutenberg-block-custom-post-types' ),
								'id'              => 'custom-post-types-list',
								'type'            => 'select_advanced',
								'options'         => $this->custom_post_types,
								'multiple'        => true,
								'placeholder'     => esc_html__( 'Select an Item', 'gutenberg-block-custom-post-types' ),
								'select_all_none' => true,
							],
							[
								'name' => esc_html__( 'Number of posts to display', 'gutenberg-block-custom-post-types' ),
								'id'   => 'custom-post-types-number-to-display',
								'type' => 'number',

								'min'  => 1,
								'step' => 1,
								'std'  => 4
							],
						],
					];

					return $meta_boxes;
				} );
			}
		}

		public function get_post_types( $built_in ) {

			// Get all registered Custom Post Types.
			$args = array(
				'public'   => true,
				'_builtin' => $built_in
			);

			$post_types = get_post_types( $args, 'objects' );

			foreach ( $post_types as $index => $post_type ) {
				if ( ! ( $post_type->name == 'attachment' ) ) {
					$this->custom_post_types[ $post_type->name ] = $post_type->label;
				}
			}
		}

		public function run() {
			add_action( 'init', array( $this, 'create_gutenberg_block' ) );
		}

		/**
		 * Activate the plugin.
		 */
		public function activate() {
			$this->create_gutenberg_block();
		}

		/**
		 * Deactivate the plugin.
		 */
		public function deactivate() {

		}

		/**
		 * Callback function for custom Gutenberg Block.
		 *
		 * @param array $attributes All attributes for custom Gutenberg Block.
		 */
		public function gutenberg_block_custom_post_types_callback( array $attributes ) {

			$custom_post_types = apply_filters( 'gutenberg_block_custom_post_types_list', mb_get_block_field( 'custom-post-types-list' ) );

			// Render all posts for current Custom Post Type.
			foreach ( $custom_post_types as $custom_post_type ) {
				$cpt = get_post_type_object( $custom_post_type );

				$args = array(
					'post_type'      => $cpt->name,
					'post_status'    => 'publish',
					'posts_per_page' => apply_filters( 'gutenberg_block_custom_post_types_count', mb_get_block_field( 'custom-post-types-number-to-display' ), $cpt->name ),
					'order'          => 'ASC',
					'orderby'        => 'rand',
				);

				$loop_posts = new WP_Query( $args );
				do_action( 'gutenberg_block_custom_post_types_current_post_type_posts', $loop_posts );
				?>
                <div class="container" style="margin-bottom: 40px">
                    <div class="row"><h2 class="custom-post-type-name"><?php echo $cpt->label ?></h2></div>
                    <ul>
						<?php foreach ( $loop_posts->posts as $post ) {
							do_action( 'gutenberg_block_custom_post_types_current_post_content', $post ); ?>
						<?php } ?>
                    </ul>
                </div>
                </div>
			<?php }
		}
	}

	global $gutenberg_block_custom_post_types;
	$gutenberg_block_custom_post_types = new Gutenberg_Block_Custom_Post_Types();
	$gutenberg_block_custom_post_types->run();
}