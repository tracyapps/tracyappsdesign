/**
 * javascript that breaks mobile. only gets loaded on desktop computers.
 */


//skrollr.... engage!
var s = skrollr.init();

var skrollHome = s.relativeToAbsolute(document.getElementById('home'), 'top', 'bottom');
var skrollServices = s.relativeToAbsolute(document.getElementById('services'), 'top', 'bottom');
var skrollAbout = s.relativeToAbsolute(document.getElementById('about'), 'top', 'bottom');
var skrollOurProcess = s.relativeToAbsolute(document.getElementById('our-process'), 'top', 'bottom');
var skrollContact = s.relativeToAbsolute(document.getElementById('contact'), 'top', 'bottom');


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