</div> <!--main-->
</div> <!--page-->

<div id="rezone-footer">
	<div>
		<h2>About This Project</h2>
		
		<section class="rezone-overview row-fluid">
			<div class="span12">
				<?php $series_id = 891916;
					  echo apply_filters('the_content', get_post_field('post_content', $series_id)); 
				?>
			</div>
		</section>

		<div class="row-fluid">
			<div class="span12 plan-status">
				<?php
				$neighborhoods = get_terms( array( 'taxonomy' => 'neighborhoods', 'hide_empty' => false ) );
				$count = 0;
				?>
				<div class="row-fluid">	
					<?php foreach ( $neighborhoods as $neighborhood ) : ?>			
						<div class="zone-w-status"><h5><a href="<?php echo get_term_link($neighborhood); ?>"><div class="circle green"></div><?php echo $neighborhood->name; ?></a></h5></div>
					<?php endforeach; ?>
				</div>
			</div>
		</div>
<!-- </div></div> --> <!-- these divs are closed in the footer -->