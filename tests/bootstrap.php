<?php

if (!is_file(__DIR__ . '/../vendor/autoload.php')) {
	echo "Tester not found. Install Nette Tester using 'composer update --dev'.\n";
	exit(1);
}
include __DIR__ . '/../vendor/autoload.php';

Tester\Environment::setup();
