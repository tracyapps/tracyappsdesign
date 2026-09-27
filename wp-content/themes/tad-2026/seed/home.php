<?php
/**
 * Starter content for the home page — the aurora design's copy, in the same
 * order as the handoff: hero → ticker → [accent: work] → capabilities →
 * process → studio → [accent: contact].
 *
 * Used by Tools › Seed Home Page (inc/seed.php). Edit freely; nothing else reads it.
 * Not included: the Manifesto block (still available in the editor).
 *
 * @package TAD
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$tab = static function ( $title, $url, $new = true ) {
	return array(
		'title'  => $title,
		'url'    => $url,
		'target' => $new ? '_blank' : '',
	);
};

return array(
	array(
		'name'   => 'acf/tad-hero',
		'fields' => array(
			'badge_text'       => 'available for studio work · 2026',
			'badge_pulse'      => 1,
			'heading_tag'      => 'h1',
			'heading'          => 'design + technology that {speaks human}.',
			'lead'             => 'Too many good ideas get lost in jargon. We listen first, explain the technical parts in plain language, and treat you as a partner in the work — not a client to be managed. Design, web, product, brand, or something stranger entirely.',
			'primary_button'   => $tab( 'start a project', '#contact', false ),
			'secondary_button' => $tab( 'explore the work', '#work', false ),
			'stats'            => array(
				array( 'value' => '25+', 'label' => 'years building' ),
				array( 'value' => '100+', 'label' => 'client projects' ),
				array( 'value' => '200+', 'label' => 'bow ties & counting' ),
			),
			'show_cluster'     => 1,
			'flip_layout'      => 0,
			'cards'            => array(
				array( 'art' => 'papr', 'caption' => 'papr.world — cozy paper game', 'tilt' => -5, 'depth' => 14 ),
				array( 'art' => 'exp', 'caption' => 'EXP[design] — mac design app', 'tilt' => 4, 'depth' => 8 ),
				array( 'art' => 'coloma', 'caption' => 'visitcoloma.com — custom WordPress', 'tilt' => -2, 'depth' => 24 ),
			),
			'chips'            => array(
				array( 'text' => 'web', 'depth' => 30 ),
				array( 'text' => 'product', 'depth' => 22 ),
				array( 'text' => 'games', 'depth' => 26 ),
			),
		),
	),
	array(
		'name'   => 'acf/tad-ticker',
		'fields' => array(
			'items'          => array(
				array( 'text' => 'web design' ),
				array( 'text' => 'custom wordpress' ),
				array( 'text' => 'plugin development' ),
				array( 'text' => 'product design' ),
				array( 'text' => 'game design' ),
				array( 'text' => 'accessibility' ),
				array( 'text' => 'brand & identity' ),
			),
			'separator'      => '✦',
			'speed'          => 40,
			'direction'      => 'left',
			'pause_on_hover' => 1,
			'decorative'     => 1,
		),
	),
	array(
		'name'   => 'acf/tad-accent-section',
		'fields' => array(
			'top_transition'    => 1,
			'bottom_transition' => 1,
			'override_settings' => 0,
		),
		'inner'  => array(
			array(
				'name'   => 'acf/tad-work-grid',
				'anchor' => 'work',
				'fields' => array(
					'eyebrow'       => 'selected work',
					'heading'       => 'things we’ve made, in the wild.',
					'intro'         => 'A cozy multiplayer game, a native design tool, and custom platforms that keep running long after launch.',
					'section_space' => 'default',
					'order'             => 'random',
					'count'             => 9,
					'show_archive_link' => 1,
					'archive_link_label' => 'see all work',
				),
			),
		),
	),
	array(
		'name'   => 'acf/tad-capabilities',
		'anchor' => 'capabilities',
		'fields' => array(
			'eyebrow'       => 'capabilities',
			'heading'       => 'one studio, a very big crayon box.',
			'intro'         => '',
			'section_space' => 'default',
			'columns'       => '3',
			'items'         => array(
				array( 'icon' => 'web', 'title' => 'Web design & development', 'text' => 'Responsive, accessible builds from marketing sites to full custom platforms.' ),
				array( 'icon' => 'wordpress', 'title' => 'Custom WordPress & plugins', 'text' => 'Bespoke themes and plugins built to be handed off and run without us.' ),
				array( 'icon' => 'product', 'title' => 'Product & UX design', 'text' => 'Research, flows, prototyping, and design systems that get maintained.' ),
				array( 'icon' => 'games', 'title' => 'Game design & development', 'text' => 'Playable, delightful games — including Discord bots and social layers.' ),
				array( 'icon' => 'brand', 'title' => 'Brand & identity', 'text' => 'Names, marks, and visual systems that hold up on every surface.' ),
				array( 'icon' => 'accessibility', 'title' => 'Accessibility', 'text' => 'Audits, remediation, and inclusive design baked in from the first sketch.' ),
			),
		),
	),
	array(
		'name'   => 'acf/tad-process',
		'anchor' => 'process',
		'fields' => array(
			'eyebrow'       => 'how it goes',
			'heading'       => 'the whole messy, glorious process.',
			'intro'         => '',
			'section_space' => 'default',
			'steps'         => array(
				array( 'title' => 'Workshop it', 'text' => 'Turn a vague ask into a real problem worth solving.' ),
				array( 'title' => 'Design it', 'text' => 'Structure, flows, and interface — iterated in the open.' ),
				array( 'title' => 'Build it', 'text' => 'Prototypes first, then the real thing, tested as we go.' ),
				array( 'title' => 'Ship & teach it', 'text' => 'Clear handoff and training so your team runs it without us.' ),
			),
		),
	),
	array(
		'name'   => 'acf/tad-studio',
		'anchor' => 'studio',
		'fields' => array(
			'eyebrow'       => 'the studio',
			'heading'       => 'small by choice, broad on purpose.',
			'body'          => '<p>tracy apps design, LLC is led by Tracy Apps — @tapps online — a graphic designer, front-end developer, and lifelong Milwaukeean building for the web since 1996. She’s led design for nonprofits and communities, taught UX at the university level, and consulted for organizations of every size.</p><p>We stay small so the person you talk to is the person who does the work — and we take on projects where design and technology can genuinely improve someone’s day.</p>',
			'numbered'      => 1,
			'section_space' => 'default',
			'values'        => array(
				array( 'text' => 'Serve people — technology as a tool for human good, or not at all.' ),
				array( 'text' => 'Strengthen communities — nonprofits, faith orgs, schools, pride groups.' ),
				array( 'text' => 'Accessibility, always — not a checkbox, the whole point.' ),
				array( 'text' => 'Stay a little weird — unorthodox methods, unusually broad skills.' ),
			),
		),
	),
	array(
		'name'   => 'acf/tad-accent-section',
		'fields' => array(
			'top_transition'    => 1,
			'bottom_transition' => 0,
			'override_settings' => 0,
		),
		'inner'  => array(
			array(
				'name'   => 'acf/tad-contact',
				'anchor' => 'contact',
				'fields' => array(
					'eyebrow'       => 'say hi',
					'heading'       => 'but enough about us.',
					'intro'         => 'Projects, talks, teaching, collabs — or just to swap dad jokes. First conversation is free.',
					'section_space' => 'default',
					'aside_heading' => 'prefer to skip the form?',
					'aside_text'    => 'We’re in Milwaukee’s Central time zone, and we answer.',
					'aside_link'    => $tab( 'schedule a time →', 'https://calendly.com/tapps' ),
					'phone_text'    => 'phone / text · 414-939-4040',
					'phone_number'  => '+14149394040',
					'form_mode'     => 'builtin',
					'show_topic'    => 1,
					'topic_label'   => 'what do you need?',
					'topics'        => array(
						array( 'label' => 'a website' ),
						array( 'label' => 'custom WordPress / a plugin' ),
						array( 'label' => 'product / UX design' ),
						array( 'label' => 'a game' ),
						array( 'label' => 'brand / identity' ),
						array( 'label' => 'something else entirely' ),
					),
					'message_label' => 'the details',
					'button_label'  => 'send it over',
					'form_note'     => '',
				),
			),
		),
	),
);
