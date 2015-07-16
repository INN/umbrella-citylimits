<div class="sticky-nav-wrapper">
	<div class="sticky-nav-holder <?php echo (is_front_page() || is_home()) ? '' : 'show'; ?>"
		data-hide-at-top="<?php echo (is_front_page() || is_home()) ? 'true' : 'false'; ?>">

		<?php do_action( 'largo_before_sticky_nav_container' ); ?>

		<div class="sticky-nav-container">
			<nav id="sticky-nav" class="sticky-navbar navbar clearfix">
				<div class="container">
					<div class="nav-right">
					<?php if ( of_get_option( 'show_header_social') ) { ?>
						<ul id="header-social" class="social-icons visible-desktop">
							<?php largo_social_links(); ?>
						</ul>
					<?php } ?>

					<?php if ( of_get_option( 'show_donate_button') ) largo_donate_button(); ?>

						<div id="header-search">
							<form class="form-search" role="search" method="get" action="<?php echo esc_url( home_url( '/' ) ); ?>">
								<i class="icon-search toggle" title="<?php esc_attr_e('Search', 'largo'); ?>" role="button"></i>
								<div class="input-append">
									<span class="text-input-wrapper">
										<input type="text" placeholder="<?php esc_attr_e('Search', 'largo'); ?>"
											class="input-medium appendedInputButton search-query" value="" name="s" />
									</span>
									<button type="submit" class="search-submit btn"><?php _e('GO', 'largo'); ?></button>
								</div>
							</form>
						</div>
					</div>

					<!-- .btn-navbar is used as the toggle for collapsed navbar content -->
					<a class="btn btn-navbar toggle-nav-bar" title="<?php esc_attr_e('More', 'largo'); ?>">
						<div class="bars">
							<span class="icon-bar"></span>
							<span class="icon-bar"></span>
							<span class="icon-bar"></span>
						</div>
					</a>

					<div class="nav-shelf">
						<ul class="nav"><?php
							$args = array(
							'theme_location' => 'main-nav',
							'depth'		 => 0,
							'container'	 => false,
							'items_wrap' => '%3$s',
							'menu_class' => 'nav',
							'walker'	 => new Bootstrap_Walker_Nav_Menu()
							);
							largo_cached_nav_menu($args);

							if ( of_get_option( 'show_donate_button') ) {
								if ($donate_link = of_get_option('donate_link')) { ?>
								<li class="donate">
									<a class="donate-link" href="<?php echo esc_url($donate_link); ?>">
										<span><?php echo esc_html(of_get_option('donate_button_text')); ?></span>
									</a>
								</li><?php
								}
							}

							if (has_nav_menu('global-nav')) {
								$args = array(
									'theme_location' => 'global-nav',
									'depth'		 => 1,
									'container'	 => false,
									'menu_class' => 'dropdown-menu',
									'echo' => false
								);
								$global_nav = largo_cached_nav_menu($args);

								if (!empty($global_nav)) { ?>
									<li class="menu-item-has-childen dropdown">
										<a href="javascript:void(0);" class="dropdown-toggle"><?php
											//try to get the menu name from global-nav
											$menus = get_nav_menu_locations();
											$menu_title = wp_get_nav_menu_object($menus['global-nav'])->name;
											echo ( $menu_title ) ? $menu_title : __('About', 'largo');
											?> <b class="caret"></b>
										</a>
										<?php echo $global_nav; ?>
									</li>
								<?php } ?>
							<?php } ?>
						</ul>
					</div>
				</div>
			</nav>
		</div>
	</div>
</div>
