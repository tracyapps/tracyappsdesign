/**
 * javascript that breaks mobile. only gets loaded on desktop computers.
 */


//skrollr.... engage!
var s = skrollr.init();


// better simple slideshow

var auto_ss = {
	auto : {
		speed : 3500,
		pauseOnHover : false
	}
};

var auto_ss_offset_1 = {
	auto : {
		speed : 5000,
		pauseOnHover : false
	}
};

var auto_ss_offset_2 = {
	auto : {
		speed : 4380,
		pauseOnHover : false
	}
};

var auto_ss_offset_3 = {
	auto : {
		speed : 5800,
		pauseOnHover : false
	}
};


makeBSS( '.phone-slideshow', auto_ss );
makeBSS( '.tablet-slideshow', auto_ss_offset_1 );
makeBSS( '.laptop-browser-slideshow', auto_ss_offset_2 );
makeBSS( '.desktop-browser-slideshow', auto_ss_offset_3 );