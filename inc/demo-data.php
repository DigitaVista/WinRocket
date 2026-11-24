<?php
/**
 * Demo content definitions.
 *
 * Photos are loaded from Unsplash at import time (Unsplash License,
 * https://unsplash.com/license). Each image keeps the photographer credit
 * in its caption.
 *
 * @package winrocket
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Demo categories.
 *
 * @return array slug => name.
 */
function winrocket_demo_categories() {
	return array(
		'cars-bikes'           => __( 'Cars & Bikes', 'winrocket' ),
		'cash'                 => __( 'Cash', 'winrocket' ),
		'tech-luxury'          => __( 'Tech & Luxury', 'winrocket' ),
		'holidays-experiences' => __( 'Holidays & Experiences', 'winrocket' ),
	);
}

/**
 * Demo customers (fictional) used for sample entries.
 *
 * @return array
 */
function winrocket_demo_customers() {
	return array(
		array( 'Amelia', 'Hart' ),
		array( 'Oliver', 'Brooks' ),
		array( 'Isla', 'Bennett' ),
		array( 'Noah', 'Fletcher' ),
		array( 'Grace', 'Turner' ),
		array( 'Leo', 'Marshall' ),
		array( 'Ava', 'Collins' ),
		array( 'Jack', 'Reid' ),
	);
}

/**
 * Demo competitions.
 *
 * Keys: title, cat, image (Unsplash photo id), credit, price, sale,
 * tickets, sold (% for sample entries), close (days from now),
 * start (days from now, negative = already open), featured, instant
 * (instant prize rows), winners, closed (drawn demo), text.
 *
 * @return array
 */
function winrocket_demo_products() {
	return array(
		'defender'  => array(
			'title'    => 'Land Rover Defender 110 or £95,000 Cash',
			'cat'      => 'cars-bikes',
			'image'    => 'photo-1502489597346-dad15683d4c2',
			'credit'   => 'Grant Ritchie',
			'price'    => 9.99,
			'sale'     => 7.99,
			'tickets'  => 5000,
			'sold'     => 10,
			'close'    => 21,
			'featured' => true,
			'text'     => 'Win a Land Rover Defender 110 or take £95,000 tax-free cash instead.',
		),
		'porsche'   => array(
			'title'    => 'Porsche 911 GT3 or £120,000 Cash',
			'cat'      => 'cars-bikes',
			'image'    => 'photo-1666846866060-6c958cca482c',
			'credit'   => 'Daniel Salcius',
			'price'    => 15,
			'tickets'  => 6500,
			'sold'     => 9,
			'close'    => 9,
			'featured' => true,
			'text'     => 'A track-focused Porsche 911 GT3, delivered to your door, or £120,000 cash.',
		),
		'tesla'     => array(
			'title'    => 'Tesla Model Y Long Range or £45,000 Cash',
			'cat'      => 'cars-bikes',
			'image'    => 'photo-1651311412165-74141a0d8798',
			'credit'   => 'Michal Lauko',
			'price'    => 7.5,
			'tickets'  => 4200,
			'sold'     => 10,
			'close'    => 29,
			'featured' => true,
			'text'     => 'Go electric with a Tesla Model Y Long Range or choose £45,000 cash.',
		),
		'cruiser'   => array(
			'title'    => 'Harley-Davidson Cruiser or £18,000 Cash',
			'cat'      => 'cars-bikes',
			'image'    => 'photo-1558981806-ec527fa84c39',
			'credit'   => 'Harley-Davidson',
			'price'    => 4,
			'tickets'  => 3500,
			'sold'     => 16,
			'close'    => 6,
			'text'     => 'Hit the open road on a brand new cruiser, or take £18,000 cash.',
		),
		'sporttour' => array(
			'title'    => 'Sport Tourer Motorbike or £12,000 Cash',
			'cat'      => 'cars-bikes',
			'image'    => 'photo-1558981420-87aa9dad1c89',
			'credit'   => 'Harley-Davidson',
			'price'    => 3,
			'tickets'  => 3000,
			'start'    => 4,
			'close'    => 34,
			'text'     => 'Opens soon: a sport tourer built for long weekends, or £12,000 cash.',
		),
		'friday'    => array(
			'title'    => '£10,000 Cash Every Friday',
			'cat'      => 'cash',
			'image'    => 'photo-1579621970563-ebec7560ff3e',
			'credit'   => 'micheile henderson',
			'price'    => 2,
			'sale'     => 1.5,
			'tickets'  => 8000,
			'sold'     => 7,
			'close'    => 3,
			'featured' => true,
			'instant'  => array(
				array( '£500 Cash', '25,412,1999,4040,7321' ),
				array( '£100 Cash', '88,903,2555,5050,6110,7777' ),
				array( '£25 Gift Card', '12,345,1234,2468,3690,5555,6789' ),
			),
			'text'     => 'A fresh £10,000 every Friday plus instant cash prizes to win right now.',
		),
		'taxfree'   => array(
			'title'    => '£15,000 Tax-Free Cash Straight to Your Bank',
			'cat'      => 'cash',
			'image'    => 'photo-1579706966698-cf96a97235ee',
			'credit'   => 'Toa Heftiba',
			'price'    => 4.99,
			'tickets'  => 4500,
			'sold'     => 13,
			'close'    => 2,
			'instant'  => array(
				array( '£250 Cash', '101,1500,3333' ),
				array( '£50 Cash', '77,999,2048,4444' ),
			),
			'text'     => '£15,000 paid straight into your bank account, with instant wins along the way.',
		),
		'coins'     => array(
			'title'    => '£1,000 Cash Drop',
			'cat'      => 'cash',
			'image'    => 'photo-1642961597907-fc6fbff01720',
			'credit'   => 'Sarah Agnew',
			'price'    => 0.99,
			'tickets'  => 2000,
			'sold'     => 18,
			'close'    => 1,
			'text'     => 'A quick-fire £1,000 cash draw at a pocket-money price.',
		),
		'vanfund'   => array(
			'title'    => '£30,000 Camper Van Adventure Fund',
			'cat'      => 'cash',
			'image'    => 'photo-1760612393683-1b2cda6fdbbe',
			'credit'   => 'Hasse Lossius',
			'price'    => 3.99,
			'tickets'  => 4000,
			'sold'     => 6,
			'close'    => 24,
			'text'     => '£30,000 to buy, convert or rent the camper van of your dreams.',
		),
		'macbook'   => array(
			'title'    => 'MacBook Pro Ultimate Creator Bundle',
			'cat'      => 'tech-luxury',
			'image'    => 'photo-1558895101-0b9cc10348f1',
			'credit'   => 'Denis Pavlovic',
			'price'    => 2.5,
			'sale'     => 1.99,
			'tickets'  => 2500,
			'sold'     => 11,
			'close'    => 12,
			'instant'  => array(
				array( 'AirPods Pro', '44,890,2001' ),
				array( '£50 App Store Card', '9,321,1200,2400' ),
			),
			'text'     => 'A top-spec MacBook Pro bundle for creators, plus instant tech prizes.',
		),
		'ps5'       => array(
			'title'    => 'PlayStation 5 Pro Gaming Bundle',
			'cat'      => 'tech-luxury',
			'image'    => 'photo-1755589614356-bf6d276f5ca5',
			'credit'   => 'User_Pascal',
			'price'    => 1.5,
			'tickets'  => 3000,
			'sold'     => 12,
			'close'    => 1,
			'text'     => 'PS5 Pro, two controllers and a stack of games ready to play.',
		),
		'gamingpc'  => array(
			'title'    => 'Custom Liquid-Cooled Gaming PC',
			'cat'      => 'tech-luxury',
			'image'    => 'photo-1754821305525-8517ea0737fb',
			'credit'   => 'Andrey Matveev',
			'price'    => 3,
			'tickets'  => 1500,
			'sold'     => 14,
			'close'    => 5,
			'text'     => 'A hand-built, liquid-cooled gaming PC with a top-tier graphics card.',
		),
		'drone'     => array(
			'title'    => 'Pro Camera Drone Fly More Combo',
			'cat'      => 'tech-luxury',
			'image'    => 'photo-1507582020474-9a35b7d455d9',
			'credit'   => 'Karl Greif',
			'price'    => 7.5,
			'tickets'  => 1200,
			'sold'     => 8,
			'close'    => 10,
			'text'     => 'A foldable 4K camera drone with spare batteries and a carry case.',
		),
		'camera'    => array(
			'title'    => 'Full-Frame Mirrorless Photography Kit',
			'cat'      => 'tech-luxury',
			'image'    => 'photo-1554369921-6035e1d53f63',
			'credit'   => '和国 谢',
			'price'    => 2.5,
			'tickets'  => 2000,
			'sold'     => 9,
			'close'    => 15,
			'text'     => 'A full-frame mirrorless camera and a fast zoom lens to start shooting.',
		),
		'espresso'  => array(
			'title'    => 'Home Barista Espresso Bar',
			'cat'      => 'tech-luxury',
			'image'    => 'photo-1607273177127-47ec33b5be3f',
			'credit'   => 'Nathan Dumlao',
			'price'    => 1.25,
			'tickets'  => 1500,
			'sold'     => 7,
			'close'    => 7,
			'text'     => 'Bean-to-cup espresso machine, grinder and barista kit for your kitchen.',
		),
		'maldives'  => array(
			'title'    => '7-Night Maldives Overwater Villa',
			'cat'      => 'holidays-experiences',
			'image'    => 'photo-1602217401731-1b9d5c2384f7',
			'credit'   => 'Upgraded Points',
			'price'    => 5,
			'tickets'  => 1500,
			'sold'     => 10,
			'close'    => 28,
			'text'     => 'Seven nights for two in an overwater villa, flights included.',
		),
		'newyork'   => array(
			'title'    => 'Seven Nights in New York for Two',
			'cat'      => 'holidays-experiences',
			'image'    => 'photo-1516037290137-e10ef77fcc1e',
			'credit'   => 'Matt Nelson',
			'price'    => 3,
			'tickets'  => 2200,
			'sold'     => 12,
			'close'    => 20,
			'winners'  => 3,
			'text'     => 'Flights, a Manhattan hotel and spending money for a week in New York.',
		),
		'japan'     => array(
			'title'    => 'Two Weeks in Japan with Rail Passes',
			'cat'      => 'holidays-experiences',
			'image'    => 'photo-1545569341-9eb8b30979d9',
			'credit'   => 'Su San Lee',
			'price'    => 5.5,
			'tickets'  => 2000,
			'sold'     => 8,
			'close'    => 26,
			'text'     => 'Tokyo to Kyoto by bullet train: two weeks in Japan for two people.',
		),
		'santorini' => array(
			'title'    => 'Santorini Sunset Escape',
			'cat'      => 'holidays-experiences',
			'image'    => 'photo-1497262693247-aa258f96c4f5',
			'credit'   => 'Andre Benz',
			'price'    => 4.5,
			'tickets'  => 1800,
			'sold'     => 15,
			'close'    => 9,
			'text'     => 'Five nights in a cliffside suite with caldera views.',
		),
		'trackday'  => array(
			'title'    => 'Race Track Day for Two',
			'cat'      => 'holidays-experiences',
			'image'    => 'photo-1763053628565-2d38fdca5835',
			'credit'   => 'Jackson Funk',
			'price'    => 2,
			'tickets'  => 1000,
			'sold'     => 30,
			'close'    => -3,
			'closed'   => true,
			'text'     => 'A full day of hot laps with a pro driver. This draw has finished.',
		),
		'bmwcash'   => array(
			'title'    => 'Supercar Weekend or £5,000 Cash',
			'cat'      => 'cars-bikes',
			'image'    => 'photo-1614273445055-82e54a0e8089',
			'credit'   => 'Martin Zdrazil',
			'price'    => 2.5,
			'tickets'  => 1200,
			'sold'     => 35,
			'close'    => -6,
			'closed'   => true,
			'text'     => 'A weekend with a supercar of your choice, or £5,000 cash. This draw has finished.',
		),
	);
}
