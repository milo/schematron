[Schematron](https://github.com/milo/schematron/blob/master/Schematron.php)
===========================================================================
This library is an implementation of the [ISO Schematron](http://www.schematron.com/spec.html) (with Schematron 1.5 back compatibility). It is done by pure DOM processing and does not require any XLST sheets nor XLST PHP extension. It was a requirement for a developing.


Usage
=====
Install the Schematron by the Composer or download a release package.
```php
use Milo\Schematron;

$schematron = new Schmeatron;
$schematron->load('schema.xml');

$document = new DOMDocument;
$document->load('document.xml');
$result = $schematron->validate($document);

var_dump($result);
```


Format of the `Schematron::validate()` result depends on its second argument. E.g. an imaginary results:
```php
# Flat array of failed asserts and successful reports
$result = $schematron->validate($document, Schematron::RESULT_SIMPLE);   # default
# array (2)
#    0 => "Person must have surname."
#    1 => "Phone number is required."


# More complex structure
$result = $schematron->validate($document, Schematron::RESULT_COMPLEX);
# array (3)
#    0 => stdClass (2)
#    |  title => "Pattern 1" (9)
#    |  rules => array (3)
#    |  |  2 => stdClass (2)
#    |  |  |  context => "/"
#    |  |  |  errors => array (2)
#    |  |  |  |  0 => stdClass (3)
#    |  |  |  |  |  test => "false()" (7)
#    |  |  |  |  |  message => "S5 - fail" (9)
#    |  |  |  |  |  path => "/"
#    |  |  |  |  1 => stdClass (3)
#    |  |  |  |  |  test => "true()" (6)
#    |  |  |  |  |  message => "S6 - fail" (9)
#    |  |  |  |  |  path => "/"


# Or throws exception of first error occurence
try {
    $result = $schematron->validate($document, Schematron::RESULT_EXCEPTION);
} catch (Milo\SchematronException $e) {
    echo $e->getMessage();  # Person must have surname.
}
```


A validation phase can be passed by 3rd argument:
```php
$schematron->validate($document, Schematron::RESULT_SIMPLE, 'phase-base-rules');
```


Schematron performs a schema namespace (ISO or v1.5) autodetection, but the namespace can be passed manually:
```php
$schematron = new Schmeatron(Schematron::NS_ISO);
```


By `Schematron::setOptions($options)` you can adjust the Schematron behaviour. The $options is a mask of following flags:
```php
# Allows to schema does not contain a <sch:schema> element,
# so <pattern>s stands alone in XML, e.g. in Relax NG schema
Schematron::ALLOW_MISSING_SCHEMA_ELEMENT

# <sch:include> are ignored and do not expand
Schematron::IGNORE_INCLUDE

# <sch:include> are forbidden and loading fails if occures
Schematron::FORBID_INCLUDE = 0x0004,

# <sch:rule> with the same @context as any rule before is skipped
# This arises from official Universal Tests (http://www.schematron.com/validators/universalTests.sch)
Schematron::SKIP_DUPLICIT_RULE_CONTEXT = 0x0008,

# <sch:schema> needn't to contain <sch::pattern>s
Schematron::ALLOW_EMPTY_SCHEMA = 0x0010,

# <sch:pattern> needn't to contain <sch::rule>s
Schematron::ALLOW_EMPTY_PATTERN = 0x0020,

# <sch:rule> needn't to contain <sch:assert>s nor <sch:report>s
Schematron::ALLOW_EMPTY_RULE = 0x0040;
```


An `<sch:include>` processing is affected by setting `Schematron::setAllowedInclude($allowed)` mask which permits types of include uri and `Schematron::setMaxIncludeDepth($depth)`:
```php
# Remote URLs
Schematron::INCLUDE_URL

# Absolute and relative filesystem paths
Schematron::INCLUDE_ABSOLUTE_PATH
Schematron::INCLUDE_RELATIVE_PATH

# Any URI
Schematron::INCLUDE_ALL
```


And two basic attributes of loaded schema are accesible over:
```php
$schematron->getSchemaVersion();
$schematron->getSchemaTitle();
```



Licence
=======
You may use all files under the terms of the New BSD Licence, or the GNU Public Licence (GPL) version 2 or 3, or the MIT Licence.



Tests
=====
The Schematron tests are written for [Nette Tester](https://github.com/nette/tester). Two steps are required to run them:
```sh
# Download the Tester tool
composer.phar update --dev

# Run the tests
vendor/bin/tester tests
```



------

[![Build Status](https://travis-ci.org/milo/schematron.png?branch=master)](https://travis-ci.org/milo/schematron)
