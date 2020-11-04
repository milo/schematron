<?php

/**
 * Test: Schematron::load()
 *
 * @author  Miloslav Hůla
 */

use Milo\Schematron;
use Tester\Assert;

require __DIR__ . '/bootstrap.php';

define('SRC_DIR', __DIR__ . '/resources');



Assert::exception(function() {
	new Schematron('unknown:namespace');
}, 'InvalidArgumentException', "Unsupported schema namespace 'unknown:namespace'.");



# Malformed XML
$sch = new Schematron;
$e = Assert::exception(function() use ($sch) {
	$sch->load(SRC_DIR . '/empty.xml');
}, 'Milo\SchematronException', "Cannot load schema from file '%a%/empty.xml'.");

Assert::exception(function() use ($e) {
	throw $e->getPrevious();
}, 'ErrorException', 'Document is empty');



# Version and Title
$sch = new Schematron;
$sch->load(SRC_DIR . '/schema-attrs.xml');
Assert::same( '1.0', $sch->getSchemaVersion() );
Assert::same( 'Schema Title', $sch->getSchemaTitle() );



# Query Binding
$sch = new Schematron;
Assert::exception(function() use ($sch) {
	$sch->load(SRC_DIR . '/bad-binding.xml');
}, 'Milo\SchematronException', "Query binding 'unknown' is not supported.");



# Multiple <schema>
$sch = new Schematron;
Assert::exception(function() use ($sch) {
	$sch->load(SRC_DIR . '/multiple-schema.xml');
}, 'Milo\SchematronException', 'Only one <schema> element in document is allowed, but 2 found.');



# Without <schema>
$sch = new Schematron;
Assert::exception(function() use ($sch) {
	$sch->load(SRC_DIR . '/no-schema.xml');
}, 'Milo\SchematronException', '<schema> element not found.');

$sch->setOptions(Schematron::ALLOW_MISSING_SCHEMA_ELEMENT);
$sch->load(SRC_DIR . '/no-schema.xml');



# <schema> without <pattern>
$sch = new Schematron;
Assert::exception(function() use ($sch) {
	$sch->load(SRC_DIR . '/empty-schema.xml');
}, 'Milo\SchematronException', 'None <sch:pattern> found in schema.');

$sch->setOptions(Schematron::ALLOW_EMPTY_SCHEMA);
$sch->load(SRC_DIR . '/empty-schema.xml');



# <pattern> without <rule>
$sch = new Schematron;
Assert::exception(function() use ($sch) {
	$sch->load(SRC_DIR . '/empty-pattern.xml');
}, 'Milo\SchematronException', 'Missing rules for <pattern> on line 2.');

$sch->setOptions(Schematron::ALLOW_EMPTY_PATTERN);
$sch->load(SRC_DIR . '/empty-pattern.xml');



# <rule> without <assert> or <report>
$sch = new Schematron;
Assert::exception(function() use ($sch) {
	$sch->load(SRC_DIR . '/empty-rule.xml');
}, 'Milo\SchematronException', 'Asserts nor reports not found for <rule> on line 3.');

$sch->setOptions(Schematron::ALLOW_EMPTY_RULE);
$sch->load(SRC_DIR . '/empty-rule.xml');



# <include>
$sch = new Schematron;
$sch->load(SRC_DIR . '/include.xml');

$sch->setMaxIncludeDepth(-1);
Assert::exception(function() use ($sch) {
	$sch->load(SRC_DIR . '/include.xml');
}, 'RuntimeException', "Reached maximum (-1) include depth.");


$sch = new Schematron;
$sch->setAllowedInclude(0);
Assert::exception(function() use ($sch) {
	$sch->load(SRC_DIR . '/include.xml');
}, 'RuntimeException', "Including URI of type 'Relative file path' referenced by <include> on line 4 is not allowed.");


$sch->setOptions(Schematron::FORBID_INCLUDE);
Assert::exception(function() use ($sch) {
	$sch->load(SRC_DIR . '/include.xml');
}, 'RuntimeException', 'Include functionality is disabled. Found 1 <include> elements, first on line 4.');


$sch->setOptions(Schematron::IGNORE_INCLUDE);
Assert::exception(function() use ($sch) {
	$sch->load(SRC_DIR . '/include.xml');
}, 'Milo\SchematronException', 'Asserts nor reports not found for <rule> on line 3.');


$dom = new DOMDocument;
$dom->load(SRC_DIR . '/include.xml');
$sch = new Schematron;
Assert::exception(function() use ($sch, $dom) {
	$sch->loadDom($dom);
}, 'RuntimeException', "Cannot evaluate relative URI 'include-assert.xml' referenced by <include> on line 4, schema has not been loaded from file. Set schema directory by setIncludeDir() method.");



# Duplicate <ns>
$sch = new Schematron;
Assert::exception(function() use ($sch) {
	$sch->load(SRC_DIR . '/duplicate-ns.xml');
}, 'Milo\SchematronException', "Namespace prefix 'one' on line 3 is alredy declared on line 2.");



# Required attributes or their combination
$sch = new Schematron;
$messages = array(
	# <ns> attributes
	'ns-missing-prefix.xml' => "Missing required attribute 'prefix' for element <ns> on line 2.",
	'ns-missing-uri.xml' => "Missing required attribute 'uri' for element <ns> on line 2.",

	# <pattern> attributes
	'pattern-abstract-missing-id.xml' => "Missing required attribute 'id' for element <pattern> on line 2.",
	'pattern-bad-abstract-ref.xml' => "<pattern> on line 2 references to undefined abstract pattern by ID 'invalid'.",
	'pattern-bad-attr-combination.xml' => "An abstract <pattern> on line 2 shall not have a 'is-a' attribute.",

	# <param> attributes
	'param-missing-name.xml' => "Missing required attribute 'name' for element <param> on line 9.",
	'param-missing-value.xml' => "Missing required attribute 'value' for element <param> on line 9.",
	'param-duplicate-name.xml' => "Parameter 'foo' is already defined on line 9.",

	# <rule> attributes
	'rule-missing-context.xml' => "Missing required attribute 'context' for element <rule> on line 3.",
	'rule-abstract-missing-id.xml' => "Missing required attribute 'id' for element <rule> on line 3.",
	'rule-bad-attr-combination.xml' => "An abstract rule on line 3 shall not have a 'context' attribute.",

	# <extends> attributes
	'extends-missing-rule.xml' => "Missing required attribute 'rule' for element <extends> on line 4.",
	'extends-bad-rule-ref.xml' => "<extends> on line 4 references to undefined abstract rule by ID 'invalid'.",

	# <assert> and <report> attributes
	'assert-missing-test.xml' => "Missing required attribute 'test' for element <assert> on line 4.",
	'report-missing-test.xml' => "Missing required attribute 'test' for element <report> on line 4.",

	# <include> attributes
	'include-missing-href.xml' => "Missing required attribute 'href' for element <include> on line 4.",

	# <phase> attributes
	'phase-missing-id.xml' => "Missing required attribute 'id' for element <phase> on line 2.",
	'phase-duplicate-id.xml' => "<phase> with id 'foo' is already defined on line 2.",
	'phase-invalid-default.xml' => "Default validation phase 'invalid' is not defined.",

	# <active>
	'active-missing-pattern.xml' => "Missing required attribute 'pattern' for element <active> on line 3.",
	'active-bad-pattern-ref.xml' => "<active> on line 3 references to undefined pattern by ID 'invalid'.",
);
foreach ($messages as $file => $message) {
	Assert::exception(function() use ($sch, $file) {
		$sch->load(SRC_DIR . '/' . $file);
	}, 'Milo\SchematronException', $message);
}
