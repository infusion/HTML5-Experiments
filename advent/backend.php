<?php

if (date('m') !== '12' || !isset($_GET['name']))
	exit;

$day = date('d') | 0;

$gifts = array(
	'name1' => array(
		array(
		'b' => 'i:path/image.jpg',
		'd' => 'l:http://...'
		),
		...
	),
	'name2' => ...
);

$themes = array(

	'name1' => 1,
	'name2' => 3,
	...
);


$name = $_GET['name'];

if (!isset($gifts[$name])) {
	exit;
}

Template::assign('day', $day);
Template::assign('theme', $themes[$name]);
Template::assign('gifts', array_slice($gifts[$name], 0, $day));


