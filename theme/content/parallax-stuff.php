<?php
/**
 * what's happenin' parallax
 */
$imgs = get_bloginfo( 'template_directory' ) . '/images/';
?>

<section id="web-devices-container" style="position: fixed; bottom: -48px; left: -83px; width: 200px; height: 390px; float: left; z-index: 5;"
		 data-start="opacity:1; left: -83px; bottom: -48px;"
		 data-450-start="left: -83px; bottom: -48px;"
		 data-550-start="bottom:-110px; left:-121px;"
		 data-anchor-target="#our-process"
		 data-bottom-top="opacity:1;"
		 data-center-top="opacity:1; bottom:-110px; "
		 data-center-center="opacity:1; bottom:-110px; left: -120px;"
		 data-center-bottom="left: -2900px;"
	>

<?php /*  ------------- START phone slideshow ------ */ ?>
<aside id="phone-contaner" style="position: absolute; left: 626px; top: 197px; z-index: 400; width: 89px; height: 179px;">
	<div class="phone phone-slideshow slideshow" style="background:url( '<?php echo esc_url( $imgs ); ?>iphone-bg.png' ) no-repeat; background-size:contain; overflow: hidden; position: relative;"
		 data-start="opacity:0; width: 10px; height: 10px;"
		 data-200-start="opacity:0; width: 10px; height: 10px;"
		 data-210-start="opacity:1; width:89px; height:179px;">
		<div class="padding" style="position:absolute; top: 21px; left: 6px; z-index: 450; width: 79px; height: 140px;  z-index: 450;">
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
<aside id="tablet-contaner" style="position: absolute; left: 512px; top: 158px; z-index: 300; width: 140px; height: 216px;">
	<div class="tablet tablet-slideshow slideshow" style="background:url( '<?php echo esc_url( $imgs ); ?>ipad-bg.png' ) no-repeat; background-size:contain; overflow: hidden; position: relative;"
		 data-start="opacity:0; width: 10px; height: 10px;"
		 data-170-start="opacity:0; width: 10px; height: 10px;"
		 data-180-start="opacity:1; width:140px; height:216px;">
		<div class="padding" style="position: absolute; top: 24px; left: 8px; z-index: 350; width: 124px; height: 165px">
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
<aside id="laptop-contaner" style="position: absolute; left: 229px; top: 136px; z-index: 200">
	<div class="laptop" style="background:url( '<?php echo esc_url( $imgs ); ?>macbook-bg.png' ) no-repeat;  background-size:contain; overflow: hidden; position: relative;"
		 data-start="opacity:0; width: 10px; height: 10px;"
		 data-140-start="opacity:0; width: 10px; height: 10px;"
		 data-150-start="opacity:1; width:393px; height:201px;">
		<div class="padding" style="position: absolute; top: 10px; left: 50px;">
			<div id="chrome-laptop" class="browser laptop-browser-slideshow slideshow" style="background:url( '<?php echo esc_url( $imgs ); ?>chrome-laptop-bg.png' ) no-repeat; background-size:contain; overflow: hidden; position:relative;"
				 data-start="opacity:0; width:298px; height:177px;"
				 data-200-start="opacity:0;"
				 data-210-start="opacity:1">
				<div class="padding" style="position: absolute; top: 29px; left: 13px; z-index: 250; width:272px; height: 121px;">
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
		 data-start="opacity:0; width: 10px; height: 10px;"
		 data-120-start="opacity:0; width: 10px; height: 10px;"
		 data-130-start="opacity:1; width: 414px; height: 330px;">
		<div class="padding" style="position: absolute; top: 11px; left: 68px; width: 414px; height: 330px;">
			<div id="chrome-desktop" class="browser desktop-browser-slideshow slideshow" style="background:url( '<?php echo esc_url( $imgs ); ?>chrome-desktop-bg.png' ) no-repeat; background-size:contain; overflow: hidden; position: relative; z-index: 150; width: 299px; height: 232px;"
				 data-start="opacity:0; width:323; height:232;"
				 data-200-start="opacity:0;"
				 data-210-start="opacity:1;">
				<div class="padding" style="position: absolute; top: 29px; left: 12px; width: 275px; height: 181px;">
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