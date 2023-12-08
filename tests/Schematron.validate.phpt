<?php


/**
 * Test: Schematron::validate()
 *
 * @author  Miloslav Hůla
 */

use Tester\Assert,
	Milo\Schematron;

require __DIR__ . '/bootstrap.php';

define('SRC_DIR', __DIR__ . '/resources');


$sch = new Schematron;
$doc = new DOMDocument;
$doc->load(SRC_DIR . '/validate-document.xml');

Assert::false($sch->isLoaded());
Assert::exception(function() use ($sch, $doc) {
	$sch->validate($doc);
}, 'RuntimeException', 'Schema has not been loaded yet. Load it before validation.');

$sch->load(SRC_DIR . '/validate-schema.xml');
Assert::true($sch->isLoaded());


# RESULT_SIMPLE
$simple = array(
	'S5 - fail',
	'S6 - fail',
	'S7 - fail',
	'S9 - fail - root',
	'S10 - fail - person',
	'S11 - fail - a:nickname',
	'S12 - fail - Miloslav Hůla milo',
	'S13 - fail - milo',
	'S14 - fail - name',
	'S15 - fail',
	'S17 - fail',
	'S19 - fail',
);
Assert::same($simple, $sch->validate($doc));


# RESULT_COMPLEX
$complex = $sch->validate($doc, $sch::RESULT_COMPLEX);
Assert::same(count($complex), 4);
Assert::true(isset($complex['#p1']->rules[0]->errors[0]->message));
Assert::same($complex['#p1']->rules[0]->errors[0]->message, 'S15 - fail');
Assert::same(reset($complex)->title, 'Pattern 1');
Assert::true(isset($complex['#let']->rules[0]->errors[0]->message));


# RESULT_EXCEPTION
Assert::exception(function() use ($sch, $doc) {
	$sch->validate($doc, $sch::RESULT_EXCEPTION);
}, 'Milo\SchematronException', reset($simple));



# <phase>
Assert::same(array('S15 - fail'), $sch->validate($doc, $sch::RESULT_SIMPLE, 'phase-1'));

Assert::exception(function() use ($sch, $doc) {
	$sch->validate($doc, $sch::RESULT_SIMPLE, 'phase-undefined');
}, 'InvalidArgumentException', "Validation phase 'phase-undefined' is not defined.");
