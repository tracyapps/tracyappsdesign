<?php
/**
 * Starter projects (the portfolio cards from the aurora design), used by
 * Tools › Seed Home Page › "Create projects". Nothing else reads this file.
 * Layout/size are deliberately mixed so the masonry has something to show.
 *
 * @package TAD
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

return array(
	array(
		'title'   => 'papr.world',
		'excerpt' => 'A cozy multiplayer building game where everything is made of paper and nobody’s keeping score. Designed, built, and illustrated end to end — every asset drawn inside EXP[design].',
		'tags'    => 'game design, development, artwork',
		'url'     => 'https://papr.world',
		'layout'  => 'side',
		'size'    => 'feature',
		'art'     => 'papr',
		'ratio'   => '4/3',
		'year'    => '2026',
		'role'    => 'Design, development, illustration',
	),
	array(
		'title'   => 'EXP[design]',
		'excerpt' => 'A native Mac design tool shaped around how UX work actually happens.',
		'tags'    => 'product design, macOS',
		'url'     => 'https://expdesign.app',
		'label'   => 'try the beta',
		'layout'  => 'top',
		'size'    => 'standard',
		'art'     => 'exp',
		'ratio'   => '1/1',
		'role'    => 'Design, development',
	),
	array(
		'title'   => 'visitcoloma.com',
		'excerpt' => 'Custom WordPress with a bespoke events calendar a small town can run itself.',
		'tags'    => 'web, events plugin',
		'url'     => 'https://visitcoloma.com',
		'layout'  => 'top',
		'size'    => 'standard',
		'art'     => 'coloma',
		'ratio'   => '16/10',
		'role'    => 'Design, WordPress development',
	),
	array(
		'title'   => 'Stardoku',
		'excerpt' => 'Puzzle design with Discord challenges baked in.',
		'tags'    => 'game, discord',
		'url'     => 'https://stardoku.app',
		'label'   => 'play',
		'layout'  => 'top',
		'size'    => 'standard',
		'art'     => 'game',
		'ratio'   => '3/4',
	),
	array(
		'title'   => 'Draw-tionary',
		'excerpt' => 'Pictionary on Discord — design and development.',
		'tags'    => 'game, discord',
		'url'     => 'https://draw-tionary.app',
		'label'   => 'play',
		'layout'  => 'top',
		'size'    => 'standard',
		'art'     => 'wave',
		'ratio'   => '16/10',
	),
	array(
		'title'   => 'Christians for Justice in Palestine',
		'excerpt' => 'A site and petition engine a grassroots group runs without us.',
		'tags'    => 'pro bono, petition engine',
		'url'     => 'https://christiansforjusticeinpalestine.com',
		'layout'  => 'side',
		'size'    => 'wide',
		'art'     => 'ink',
		'ratio'   => '4/3',
		'role'    => 'Pro bono design & development',
	),
	array(
		'title'   => 'Loyalty Untapped',
		'excerpt' => 'Site design, development, and a custom syncing plugin.',
		'tags'    => 'wordpress, syncing plugin',
		'url'     => 'https://www.loyaltyuntapped.com',
		'layout'  => 'top',
		'size'    => 'wide',
		'art'     => 'exp',
		'ratio'   => '16/10',
	),
	array(
		'title'   => 'Web Production Studio',
		'excerpt' => 'A WordPress ecosystem for agencies and clients alike — early research.',
		'tags'    => 'in progress, wordpress',
		'url'     => 'https://webproduction.studio',
		'label'   => 'peek',
		'layout'  => 'top',
		'size'    => 'standard',
		'art'     => 'grid',
		'ratio'   => '4/3',
	),
	array(
		'title'   => 'Off Menu Gaming',
		'excerpt' => 'Site design and development.',
		'tags'    => 'web',
		'url'     => 'https://offmenugaming.com',
		'layout'  => 'top',
		'size'    => 'standard',
		'art'     => 'papr',
		'ratio'   => '1/1',
	),
	array(
		'title'   => 'tapps.design',
		'excerpt' => 'Tracy’s personal site — the other hat.',
		'tags'    => 'personal',
		'url'     => '',
		'layout'  => 'top',
		'size'    => 'standard',
		'art'     => 'coloma',
		'ratio'   => '16/10',
	),
);
