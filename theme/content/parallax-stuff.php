<?php
/**
 * what's happenin' parallax
 */
$imgs = get_bloginfo( 'template_directory' ) . '/images/';
?>

<section id="web-devices-container" style="position: relative; width:80%; height: 50%;">

<?php /*  ------------- START phone slideshow ------ */ ?>
<aside id="phone-contaner" style="position: absolute; right: 0; bottom:0; z-index: 400">
	<div class="phone phone-slideshow slideshow" style="background:url( '<?php echo esc_url( $imgs ); ?>iphone-bg.png' ) no-repeat; background-size:contain; overflow: hidden; position: relative;"
		data-start="width:89; height:179px;">
		<div class="padding" style="position:absolute; top: 47px; left: 13px; z-index: 450;">
			<figure class="slide">
				<img src="<?php echo esc_url( $imgs ); ?>iphone-crossfit100.jpg">
			</figure>
			<figure class="slide">
				<img src="<?php echo esc_url( $imgs ); ?>iphone-iamtapps.jpg">
			</figure>
			<figure class="slide">
				<img src="<?php echo esc_url( $imgs ); ?>iphone-cdlc.jpg">
			</figure>
			<figure class="slide">
				<img src="<?php echo esc_url( $imgs ); ?>iphone-revlabs.jpg">
			</figure>
		</div>
	</div>
</aside>

<?php /*  ------------- START ipad slideshow ------ */ ?>
<aside id="tablet-contaner" style="position: absolute; right: 0; bottom:0; z-index: 300; width: 140px; height: 216px;">
	<div class="tablet tablet-slideshow slideshow" style="background:url( '<?php echo esc_url( $imgs ); ?>ipad-bg.png' ) no-repeat; background-size:contain; overflow: hidden; position: relative;"
		data-start="width:140px; height:216px;">
		<div class="padding" style="position: absolute; top: 28px; left: 5px; z-index: 350; width: 124px; height: 165px;">
			<figure class="slide">
				<img src="<?php echo esc_url( $imgs ); ?>ipad-splc.jpg">
			</figure>
			<figure class="slide">
				<img src="<?php echo esc_url( $imgs ); ?>ipad-selectspineandsport.jpg">
			</figure>
			<figure class="slide">
				<img src="<?php echo esc_url( $imgs ); ?>ipad-oslc.jpg">
			</figure>
		</div>
	</div>
</aside>

<?php /*  ------------- START laptop slideshow ------ */ ?>
<aside id="laptop-contaner" style="position: absolute; right: 0; bottom:0; z-index: 200">
	<div class="laptop" style="background:url( '<?php echo esc_url( $imgs ); ?>macbook-bg.png' ) no-repeat;  background-size:contain; overflow: hidden; position: relative;"
	   data-start="width:393px; height:201px;">
		<div class="padding" style="position: absolute; top: 13px; left: 60px;">
			<div id="chrome-laptop" class="browser laptop-browser-slideshow slideshow" style="background:url( '<?php echo esc_url( $imgs ); ?>chrome-laptop-bg.png' ) no-repeat; background-size:contain; overflow: hidden; position:relative;"
				data-start="width:298px; height:177px;">
				<div class="padding" style="position: absolute; top: 22px; left: 13px; z-index: 250; width: 79px; height: 138px;">
					<figure class="slide">
						<img src="<?php echo esc_url( $imgs ); ?>chrome-laptop-oaktonavelaw.jpg">
					</figure>
					<figure class="slide">
						<img src="<?php echo esc_url( $imgs ); ?>chrome-laptop-liveatthecolomahotel.jpg">
					</figure>
					<figure class="slide">
						<img src="<?php echo esc_url( $imgs ); ?>chrome-laptop-collegelistswiki.jpg">
					</figure>
				</div>
			</div>
		</div>
	</div>
</aside>

<?php /*  ------------- START desktop slideshow ------ */ ?>
<aside id="desktop-contaner" style="position: absolute; left: 0; top:0; z-index: 100; width: 414px; height: 330px;">
	<div class="desktop" style="background:url( '<?php echo esc_url( $imgs ); ?>imac-bg.png' ) no-repeat; position: relative;  background-size:contain; overflow: hidden;"
	   data-start="width:414px;height:330px;">
		<div class="padding" style="position: absolute; top: 25px; left: 23px;">
			<div id="chrome-desktop" class="browser desktop-browser-slideshow slideshow" style="background:url( '<?php echo esc_url( $imgs ); ?>chrome-desktop-bg.png' ) no-repeat; background-size:contain; overflow: hidden; position: relative;"
				 data-start="width:323;height:232;">
				<div class="padding" style="position: absolute; top: 28px; left: 16px; z-index: 150;">
					<figure class="slide">
						<img src="<?php echo esc_url( $imgs ); ?>chrome-desktop-iamtapps.jpg">
					</figure>
					<figure class="slide">
						<img src="<?php echo esc_url( $imgs ); ?>chrome-desktop-crossfit100.jpg">
					</figure>
					<figure class="slide">
						<img src="<?php echo esc_url( $imgs ); ?>chrome-desktop-bodeeconstruction.jpg">
					</figure>
					<figure class="slide">
						<img src="<?php echo esc_url( $imgs ); ?>chrome-desktop-selectspineandsport.jpg">
					</figure>
				</div>
			</div>
		</div>
	</div>
</aside>

</section>