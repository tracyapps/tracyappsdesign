<?php
/**
 * what's happenin' parallax
 */
?>

<aside id="iphone-contaner"  data-50-start="position: fixed; z-index:2; right: -50px; bottom: -30px; opacity: 0; width: 210px; transform:rotate(7deg);" data-160-start="right:-20px; bottom: -20px; opacity: 1; transform:rotate(4deg);" data-250-end="right:-20px; bottom: -20px; opacity: 1; transform:rotate(4deg);" data-100-end="right: -50px; bottom: -30px; opacity: 0; transform:rotate(7deg);">
	<img src="<?php echo bloginfo( 'template_directory' ); ?>/images/iphoneframe.png">
	<div id="iphone-screen" data-start="position:absolute;top: 27px; right: 20px; overflow:hidden; height:100%; width:100%; padding:39px;">
		<div id="iphone-screen-1" data-20-start="opacity:0; position: absolute;" data-55-start="opacity:1; z-index:1;"><img src="<?php echo bloginfo( 'template_directory' ); ?>/images/iphone-screen-tadlogo.jpg"></div>
		<div id="iphone-screen-2" data-570-start="opacity:0; position: absolute; z-index:2" data-1150-start="opacity:1;" data-2900-start="opacity:0;" ><img src="<?php echo bloginfo( 'template_directory' ); ?>/images/iphone-screen-cf100.jpg"></div>
		<div id="iphone-screen-3" data-2700-start="opacity:0; position: absolute; z-index:3" data-3610-start="opacity:1;" data-4550-start="opacity:0;"><img src="<?php echo bloginfo( 'template_directory' ); ?>/images/iphone-screen-cdlc.jpg"></div>
	</div>
</aside>
