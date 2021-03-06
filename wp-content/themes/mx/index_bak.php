<?php get_header();?>
<?php 
/**
 * slidebox
 */
//if(!wp_is_mobile() && class_exists('theme_custom_slidebox')){
	theme_custom_slidebox::display_frontend();
//} 
?>
<div class="g">
	<?php if(!wp_is_mobile()){ ?>
		<div class="recomm-container hidden-sm">
			<?php
			/**
			 * recommended box
			 */
			if(method_exists('theme_functions','the_recommended')){
				theme_functions::the_recommended();
			}
			?>
		</div>
	<?php } ?>
	<div class="row">
		<div id="main" class="g-desktop-3-4">
			<?php 
			/**
			 * homebox 
			 */
			// theme_functions::the_homebox();
			?>

			<?php
			$args1 = [
				'showposts' => 12,
			];
			$query1 = new WP_Query($args1);
			if(have_posts()){
				?>
				<div class="home-recomm mod panel">
					<div class="heading">
						<h2 class="title">
							<span class="bg">
								<a href="/rank?tab=latest"> <i class="fa fa-star-o"></i>
									最新发布
								</a>
							</span>
						</h2>
						<a class="more" href="/rank?tab=latest">更多 »</a>
					</div>
				</div>
				<div class="row">
					<?php
					$loop_i = 0;
					foreach($query1->posts as $post){
						setup_postdata($post);
						theme_functions::archive_card_sm([
							'classes' => 'g-desktop-1-3 g-tablet-1-2',
							'lazyload' => $loop_i <= 8 ? false : true,
						]);
						++$loop_i;
					}
					?>
				</div>
			<?php } ?>

			<?php
			$args2 = [
				'showposts' => 9,
				'category__in' => [40],
			];
			$query2 = new WP_Query($args2);
			if(have_posts()){
				?>
				<div class="home-recomm mod panel">
					<div class="heading">
						<h2 class="title">
							<span class="bg">
								<a href="/code/"> <i class="fa fa-star-o"></i>
									精品源码
								</a>
							</span>
						</h2>
						<a class="more" href="/code/">更多 »</a>
					</div>
				</div>
				<div class="row">
					<?php
					$loop_i = 0;
					foreach($query2->posts as $post){
						setup_postdata($post);
						theme_functions::archive_card_sm([
							'classes' => 'g-desktop-1-3 g-tablet-1-2',
							'lazyload' => $loop_i <= 8 ? false : true,
						]);
						++$loop_i;
					}
					?>
				</div>
			<?php } ?>

			<?php
			$args3 = [
				'showposts' => 9,
				'category__in' => [83, 95, 90, 89, 94, 91, 92, 87, 88, 93, 86],
			];
			$query3 = new WP_Query($args3);
			if(have_posts()){
				?>
				<div class="home-recomm mod panel">
					<div class="heading">
						<h2 class="title">
							<span class="bg">
								<a href="/php/"> <i class="fa fa-star-o"></i>
									前端特效
								</a>
							</span>
						</h2>
						<a class="more" href="/web/">更多 »</a>
					</div>
				</div>
				<div class="row">
					<?php
					$loop_i = 0;
					foreach($query3->posts as $post){
						setup_postdata($post);
						theme_functions::archive_card_sm([
							'classes' => 'g-desktop-1-3 g-tablet-1-2',
							'lazyload' => $loop_i <= 8 ? false : true,
						]);
						++$loop_i;
					}
					?>
				</div>
			<?php } ?>

			<?php
			$args3 = [
				'showposts' => 9,
				'category__in' => [60],
			];
			$query3 = new WP_Query($args3);
			if(have_posts()){
				?>
				<div class="home-recomm mod panel">
					<div class="heading">
						<h2 class="title">
							<span class="bg">
								<a href="/php/"> <i class="fa fa-star-o"></i>
									最新电影
								</a>
							</span>
						</h2>
						<a class="more" href="/film/">更多 »</a>
					</div>
				</div>
				<div class="row">
					<?php
					$loop_i = 0;
					foreach($query3->posts as $post){
						setup_postdata($post);
						theme_functions::archive_card_sm([
							'classes' => 'g-desktop-1-3 g-tablet-1-2',
							'lazyload' => $loop_i <= 8 ? false : true,
						]);
						++$loop_i;
					}
					?>
				</div>
			<?php } ?>

			<?php
			$args3 = [
				'showposts' => 9,
				'category__in' => [15, 99, 98, 100, 97, 96],
			];
			$query3 = new WP_Query($args3);
			if(have_posts()){
				?>
				<div class="home-recomm mod panel">
					<div class="heading">
						<h2 class="title">
							<span class="bg">
								<a href="/php/"> <i class="fa fa-star-o"></i>
									电子书
								</a>
							</span>
						</h2>
						<a class="more" href="/book/">更多 »</a>
					</div>
				</div>
				<div class="row">
					<?php
					$loop_i = 0;
					foreach($query3->posts as $post){
						setup_postdata($post);
						theme_functions::archive_card_sm([
							'classes' => 'g-desktop-1-3 g-tablet-1-2',
							'lazyload' => $loop_i <= 8 ? false : true,
						]);
						++$loop_i;
					}
					?>
				</div>
			<?php } ?>
		</div><!-- /#main -->
		<?php get_sidebar() ;?>
	</div>
</div>
<?php get_footer();?>
