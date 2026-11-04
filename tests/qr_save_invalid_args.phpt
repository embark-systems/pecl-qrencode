--TEST--
qrencode: qr_save rejects invalid arguments without crashing
--SKIPIF--
<?php if (!extension_loaded("qrencode")) print "skip"; ?>
--FILE--
<?php
$q = qr_encode('test');
$file = __DIR__ . '/qr_save_invalid_args.png';
file_put_contents($file, 'keep');

var_dump(qr_save($q, null, 3, 4));
var_dump(qr_save($q, 5, 3, 4));
var_dump(qr_save($q, $file, '3', '4'));
var_dump(qr_save($q, $file, null, null));
var_dump(qr_save($q, 3, '4'));
var_dump(qr_save($q, $file, 0, 4));
var_dump(qr_save($q, $file, -1, 4));
var_dump(qr_save($q, 3, -1));
var_dump(qr_save($q, $file, 1000001, 0));
var_dump(qr_save($q, $file, PHP_INT_MAX, PHP_INT_MAX));
var_dump(qr_save($q, $file, 1000000, 1));

// Rejected calls must leave an existing file untouched.
var_dump(file_get_contents($file));

// Named arguments fill the skipped size with its default.
var_dump(qr_save($q, $file, margin: 0));
var_dump(getimagesize($file)[0]);
?>
--CLEAN--
<?php
@unlink(__DIR__ . '/qr_save_invalid_args.png');
?>
--EXPECTF--
Warning: qr_save(): should use type string as argument 2 in %s on line %d
bool(false)

Warning: qr_save(): should use type string as argument 2 in %s on line %d
bool(false)

Warning: qr_save(): should use type long as arguments 3 and 4 when pass 4 arguments in %s on line %d
bool(false)

Warning: qr_save(): should use type long as arguments 3 and 4 when pass 4 arguments in %s on line %d
bool(false)

Warning: qr_save(): should use type long as argument 3 when pass 3 arguments in %s on line %d
bool(false)

Warning: qr_save(): size must be greater than 0 in %s on line %d
bool(false)

Warning: qr_save(): size must be greater than 0 in %s on line %d
bool(false)

Warning: qr_save(): margin must not be negative in %s on line %d
bool(false)

Warning: qr_save(): image width must not exceed 1000000 pixels in %s on line %d
bool(false)

Warning: qr_save(): image width must not exceed 1000000 pixels in %s on line %d
bool(false)

Warning: qr_save(): image width must not exceed 1000000 pixels in %s on line %d
bool(false)
string(4) "keep"
bool(true)
int(63)
