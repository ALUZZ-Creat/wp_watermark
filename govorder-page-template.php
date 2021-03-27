<?php
/**
 * The template for displaying all single Govt. Order posts
 *
 * @link https://developer.wordpress.org/themes/basics/template-hierarchy/#single-post
 *
 * @package WordPress
 * @subpackage Twenty_Nineteen
 * @since Twenty Nineteen 1.0
 */

get_header();
?>
	<style type="text/css">
		.pdf-embedder .pdfemb-viewer{
			margin: auto;
		}
	</style>
	<div id="primary" class="content-area">
		<main id="main" class="site-main">

			<?php

			// Start the Loop.
			while ( have_posts() ) :
				the_post();

				get_template_part( 'template-parts/content/content', 'single' );

				
				/** PDF Embedder */

				// Get the order file's wordpress id (stored as attachment in wordpress)
				$attachment_id = get_post_meta( get_the_ID(), 'file', true );
				
				// Get the attachment's wordpress url
				if (!empty($attachment_id)) {
					$attachment_url = wp_get_attachment_url($attachment_id);
				}

				?>
				<div class="pdf-embedder">
					<?php
					if (isset($attachment_url)) {
						// insert the attachment url into PDF Embedder Short Code
						// PDF Embedder need to be installed in the Wordpress Admin
						echo do_shortcode("[pdf-embedder url='".$attachment_url."' width='500']");
					}
					?>
				</div>
				<?php

				/** PDF Embedder */

				if ( is_singular( 'attachment' ) ) {
					// Parent post navigation.
					the_post_navigation(
						array(
							/* translators: %s: Parent post link. */
							'prev_text' => sprintf( __( '<span class="meta-nav">Published in</span><span class="post-title">%s</span>', 'twentynineteen' ), '%title' ),
						)
					);
				} elseif ( is_singular( 'post' ) ) {
					// Previous/next post navigation.
					the_post_navigation(
						array(
							'next_text' => '<span class="meta-nav" aria-hidden="true">' . __( 'Next Post', 'twentynineteen' ) . '</span> ' .
								'<span class="screen-reader-text">' . __( 'Next post:', 'twentynineteen' ) . '</span> <br/>' .
								'<span class="post-title">%title</span>',
							'prev_text' => '<span class="meta-nav" aria-hidden="true">' . __( 'Previous Post', 'twentynineteen' ) . '</span> ' .
								'<span class="screen-reader-text">' . __( 'Previous post:', 'twentynineteen' ) . '</span> <br/>' .
								'<span class="post-title">%title</span>',
						)
					);
				}

				// If comments are open or we have at least one comment, load up the comment template.
				if ( comments_open() || get_comments_number() ) {
					comments_template();
				}

			endwhile; // End the loop.
			?>

		</main><!-- #main -->
	</div><!-- #primary -->

<?php
get_footer();