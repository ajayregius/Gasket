<?php
/**
 * The template for displaying lists of events
 *
 * Queries to do with events will default to this template if a more appropriate template cannot be found
 *
 ***************** NOTICE: *****************
 *  Do not make changes to this file. Any changes made to this file
 * will be overwritten if the plug-in is updated.
 *
 * To overwrite this template with your own, make a copy of it (with the same name)
 * in your theme directory. 
 *
 * WordPress will automatically prioritise the template in your theme directory.
 ***************** NOTICE: *****************
 *
 * @package Event Organiser (plug-in)
 * @since 1.0.0
 */

//Call the template header
get_header(); ?>

		<!-- This template follows the TwentyEleven theme-->
		<section id="primary">
			<div id="content" role="main">

			<?php if ( have_posts() ) : ?>

				<!---- Page header-->
				<header class="page-header">
					<h1 class="page-title">
						Events
					</h1>
				</header>

				<!---- Navigate between pages-->
				<!---- In TwentyEleven theme this is done by twentyeleven_content_nav-->
				<?php 
				global $wp_query;
				if ( $wp_query->max_num_pages > 1 ) : ?>
					<nav id="nav-above">
						<div class="nav-next events-nav-newer"><?php next_posts_link( __( 'Later events <span class="meta-nav">&rarr;</span>' , 'eventorganiser' ) ); ?></div>
						<div class="nav-previous events-nav-newer"><?php previous_posts_link( __( ' <span class="meta-nav">&larr;</span> Newer events', 'eventorganiser' ) ); ?></div>
					</nav><!-- #nav-above -->
				<?php endif; ?>

				<?php /* Start the Loop */ ?>

				<?php while ( have_posts() ) : the_post(); ?>

					<article id="post-<?php the_ID(); ?>" <?php post_class(); ?>>

						<header class="entry-header">
							<h1 class="entry-title"><a href="<?php the_permalink(); ?>"><?php the_title(); ?></a></h1>

							<div class="entry-meta">
								<!-- Output the date of the occurrence-->
								<?php eo_the_start('d F Y'); ?> 

								<!-- If the event has a venue saved, display this-->
								<?php if(eo_get_venue_name()):?>
									at <a href="<?php eo_venue_link();?>"><?php eo_venue_name();?></a>
								<?php endif;?>
							</div><!-- .entry-meta -->

						</header><!-- .entry-header -->

					</article><!-- #post-<?php the_ID(); ?> -->

    				<?php endwhile; ?><!----The Loop ends-->

				<!---- Navigate between pages-->
				<?php 
				global $wp_query;
				if ( $wp_query->max_num_pages > 1 ) : ?>
					<nav id="nav-below">
						<div class="nav-next events-nav-newer"><?php next_posts_link( __( 'Later events <span class="meta-nav">&larr;</span>' , 'eventorganiser' ) ); ?></div>
						<div class="nav-previous events-nav-newer"><?php previous_posts_link( __( ' <span class="meta-nav">&rarr;</span> Newer events', 'eventorganiser' ) ); ?></div>
					</nav><!-- #nav-below -->
				<?php endif; ?>

			<?php else : ?>
				<!---- If there are no events -->
				<article id="post-0" class="post no-results not-found">
					<header class="entry-header">
						<h1 class="entry-title"><?php _e( 'Nothing Found', 'twentyeleven' ); ?></h1>
					</header><!-- .entry-header -->

					<div class="entry-content">
						<p><?php _e( 'Apologies, but no results were found for the requested archive', 'twentyeleven' ); ?></p>
					</div><!-- .entry-content -->
				</article><!-- #post-0 -->

			<?php endif; ?>

			</div><!-- #content -->
		</section><!-- #primary -->

<!-- Call template sidebar and footer -->
<?php get_sidebar(); ?>
<?php get_footer(); ?>
