<div class="global-nav-bg">
	<div class="global-nav">
		<nav id="top-nav" class="span12">
			<span class="visuallyhidden">
				<a href="#main" title="<?php esc_attr_e( 'Skip to content', 'largo' ); ?>"><?php _e( 'Skip to content', 'largo' ); ?></a>
			</span>
			<?php
				$top_args = array(
					'theme_location' => 'global-nav',
					'depth'		 => 1,
					'container'	 => false,
				);
				largo_cached_nav_menu($top_args);
			?>
			<div class="nav-right">
				<?php if ( of_get_option( 'show_header_social') ) { ?>
					<ul id="header-social" class="social-icons visible-desktop">
						<?php largo_social_links(); ?>
					</ul>
				<?php }

				if ( of_get_option( 'show_donate_button') )
					largo_donate_button();
				?>

				<div id="header-search">
					<form class="form-search" role="search" method="get" action="<?php echo esc_url( home_url( '/' ) ); ?>">
						<div class="input-append">
							<input type="text" placeholder="<?php _e('Search', 'largo'); ?>" class="input-medium appendedInputButton search-query" value="" name="s" /><button type="submit" class="search-submit btn"><?php _e('GO', 'largo'); ?></button>
						</div>
					</form>
				</div>
			</div>
		</nav>
	</div> <!-- /.global-nav -->
</div> <!-- /.global-nav-bg -->

