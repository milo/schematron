<?php

/**
 * Test: Schematron misc functions
 *
 * @author  Miloslav Hůla
 */

use Milo\Schematron;
use Tester\Assert;

require __DIR__ . '/bootstrap.php';



$sch = new Schematron;
Assert::same(Schematron::DEFAULT_OPTIONS, $sch->getOptions());

Assert::same(Schematron::INCLUDE_RELATIVE_PATH, $sch->getAllowedInclude());

Assert::same(10, $sch->getMaxIncludeDepth());

Assert::exception(function() use ($sch) {
	$sch->setIncludeDir(__DIR__ . '/-not-exists-');
}, RuntimeException::class, "Directory '%a%' does not exist.");

Assert::same($sch, $sch->setIncludeDir(__DIR__));

Assert::same(__DIR__, $sch->getIncludeDir());
