<?php
/**
 * Plugin Initializer
 *
 * @package CloudCatch/InstagramBlock
 */

/**
 * Enqueue Gutenberg block assets for both frontend + backend.
 *
 * Assets enqueued:
 * 1. blocks.style.build.css - Frontend + Backend.
 * 2. blocks.build.js - Backend.
 * 3. blocks.editor.build.css - Backend.
 *
 * @uses {wp-blocks} for block type registration & related functions.
 * @uses {wp-element} for WP Element abstraction — structure of blocks.
 * @uses {wp-i18n} to internationalize the block's text.
 * @uses {wp-editor} for WP editor styles.
 * @since 1.0.0
 */
function instagram_block_cgb_block_assets() {
	wp_register_style(
		'cc-instagram-style-css', // Handle.
		CLOUDCATCH_INSTAGRAM_BLOCK_URL . 'dist/blocks.style.build.css',
		is_admin() ? array( 'wp-editor' ) : null,
		null
	);

	wp_register_script(
		'cc-instagram-block-js',
		CLOUDCATCH_INSTAGRAM_BLOCK_URL . 'dist/blocks.build.js',
		plugins_url( '/dist/blocks.build.js', dirname( __FILE__ ) ),
		array( 'wp-blocks', 'wp-i18n', 'wp-element', 'wp-editor' ),
		null,
		true
	);

	wp_register_style(
		'cc-instagram-block-editor-css',
		CLOUDCATCH_INSTAGRAM_BLOCK_URL . 'dist/blocks.editor.build.css',
		array( 'wp-edit-blocks' ),
		null
	);

	wp_localize_script(
		'cc-instagram-block-js',
		'cgbGlobal',
		array(
			'pluginDirPath' => plugin_dir_path( __DIR__ ),
			'pluginDirUrl'  => plugin_dir_url( __DIR__ ),
		)
	);

	register_block_type(
		CLOUDCATCH_INSTAGRAM_BLOCK_DIR . 'src/block/block.json',
		array(
			'style'           => 'cc-instagram-style-css',
			'editor_script'   => 'cc-instagram-block-js',
			'editor_style'    => 'cc-instagram-block-editor-css',
			'render_callback' => 'instagram_block_render',
		)
	);

	wp_enqueue_script(
		'cc-instagram-view-js',
		CLOUDCATCH_INSTAGRAM_BLOCK_URL . 'dist/view.build.js',
		array(),
		null,
		true
	);
}
add_action( 'init', 'instagram_block_cgb_block_assets' );

function instagram_block_render( $attributes, $content, $block ) {
	$media = instagram_block_get_media();
	$user  = instagram_block_get_user();

	if ( empty( $media ) || is_wp_error( $media ) ) {
		return sprintf( '<p>%s</p>', esc_html__( 'No posts found.', 'cc-instagram' ) );
	}

	$attributes = wp_parse_args(
		$attributes,
		array(
			'columns'      => 4,
			'carousel'     => false,
			'items'        => 4,
			'centerMode'   => false,
			'infinite'     => true,
			'autoWidth'    => false,
			'autoHeight'   => false,
			'carouselJson' => '',
		) 
	);

	$classes = array(
		'columns-' . $attributes['columns'],
		( $attributes['carousel'] ? 'is-carousel' : '' ),
	);

	$wrapper_attributes = array(
		'class' => implode( ' ', $classes ),
	);

	if ( $attributes['carousel'] && $attributes['infinite'] ) {
		$wrapper_attributes['data-infinite'] = absint( $attributes['infinite'] );
	}

	if ( $attributes['carousel'] && $attributes['centerMode'] ) {
		$wrapper_attributes['data-center-mode'] = absint( $attributes['centerMode'] );
	}

	if ( $attributes['carousel'] && $attributes['autoWidth'] ) {
		$wrapper_attributes['data-auto-width'] = absint( $attributes['autoWidth'] );
	}

	if ( $attributes['carousel'] && $attributes['autoHeight'] ) {
		$wrapper_attributes['data-auto-height'] = absint( $attributes['autoHeight'] );
	}

	if ( $attributes['carousel'] && $attributes['items'] ) {
		$wrapper_attributes['data-slides-viewport'] = absint( $attributes['items'] );
	}

	if ( $attributes['carousel'] && $attributes['carouselJson'] ) {
		$wrapper_attributes['data-options'] = $attributes['carouselJson'];
	}

	$wrapper_attributes = get_block_wrapper_attributes( $wrapper_attributes );

	ob_start();
	?>

	<div <?php echo $wrapper_attributes; ?>>
		<h3 class="is-style-divider-subhead">
			<?php esc_html_e( 'Instagram', 'cc-instagram' ); ?>

			<?php if ( $attributes['carousel'] ) : ?>

			<div class="wp-block-cloudcatch-instagram__nav"><span></span><span></span><span></span></div>

			<?php endif; ?>

			<?php if ( isset( $user['username'] ) ) : ?>

			<div class="wp-block-cloudcatch-instagram__follow">
				<a href="<?php echo esc_url( trailingslashit( 'https://instagram.com/' . $user['username'] ) ); ?>" target="_blank"><?php esc_html_e( 'Follow Us', 'cc-instagram' ); ?></a>
			</div>

			<?php endif; ?>
		</h3>
		<div class="wp-block-cloudcatch-instagram__inner-container">
			<div class="wp-block-cloudcatch-instagram__content">
				<?php foreach ( $media as $media ) : ?>

				<div class="wp-block-cloudcatch-instagram__content-item">
					<?php if ( 'VIDEO' === $media['media_type'] ) : ?>
					
					<div class="wp-block-cloudcatch-instagram__content-video">
						<video controls>
							<source src="<?php echo esc_url( $media['media_url'] ); ?>" type="video/mp4" />
						</video>
						<div class="wp-block-cloudcatch-instagram__content-video-controls">
							<button data-media="play-pause"></button>
							<button data-media="mute-unmute"></button>
						</div>
					</div>

					<?php else : ?>

					<div class="wp-block-cloudcatch-instagram__content-image">
						<img src="<?php echo esc_url( $media['media_url'] ); ?>" />
					</div>

					<?php endif; ?>
				</div>

				<?php endforeach; ?>
			</div>
		</div>
	</div>

	<?php
	$content = apply_filters( 'instagram_block_render', ob_get_clean(), $attributes, $content, $block );

	return $content;
}
