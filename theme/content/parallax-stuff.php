<?php
/**
 * what's happenin' parallax
 */
$imgs = get_bloginfo( 'template_directory' ) . '/images/';
?>

<?php /*  ------------- START phone slideshow ------ */ ?>
<aside id="phone-contaner" class="phone phone-slideshow slideshow" style="background:url( '<?php echo esc_url( $imgs ); ?>iphone-bg.png' ) no-repeat; background-size:contain; overflow: hidden; position: relative;"
		data-start="width:199px;height:402px;">
	<div class="padding" style="position:absolute; top: 47px; left: 13px;">
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
</aside>

<?php /*  ------------- START ipad slideshow ------ */ ?>
<aside id="tablet-contaner" class="tablet tablet-slideshow slideshow" style="background:url( '<?php echo esc_url( $imgs ); ?>ipad-bg.png' ) no-repeat; background-size:contain; overflow: hidden; position: relative;"
		data-start="width:313px;height:484px;">
	<div class="padding" style="position: absolute; top: 54px; left: 17px;">
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
</aside>

<?php /*  ------------- START laptop slideshow ------ */ ?>
<aside id="laptop-contaner" class="laptop" style="background:url( '<?php echo esc_url( $imgs ); ?>macbook-bg.png' ) no-repeat; position: relative;"
	   data-start="width:880px;height:449px;">
	<div class="padding" style="position: absolute; top: 41px; left: 98px;">
		<div id="chrome-laptop" class="browser laptop-browser-slideshow slideshow" style="background:url( '<?php echo esc_url( $imgs ); ?>chrome-laptop-bg.png' ) no-repeat; background-size:contain; overflow: hidden; position:relative;"
			data-start="width:605px;height:334px;">
			<div class="padding" style="position: absolute; top: 57px; left: 27px;">
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
</aside>

<?php /*  ------------- START desktop slideshow ------ */ ?>
<aside id="desktop-contaner" class="desktop" style="background:url( '<?php echo esc_url( $imgs ); ?>imac-bg.png' ) no-repeat; position: relative;"
	   data-start="width:928px;height:739px;">
	<div class="padding" style="position: absolute; top: 45px; left: 93px;">
		<div id="chrome-desktop" class="browser desktop-browser-slideshow slideshow" style="background:url( '<?php echo esc_url( $imgs ); ?>chrome-desktop-bg.png' ) no-repeat; background-size:contain; overflow: hidden; position: relative;"
			 data-start="width:661px;height:455px;">
			<div class="padding" style="position: absolute; top: 58px; left: 26px;">
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
</aside>