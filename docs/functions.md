# qrencode function reference

[繁體中文](functions.zh-TW.md) | English

This reference describes extension version 0.11. The extension wraps
[libqrencode](https://fukuchi.org/works/qrencode/) and provides two functions:

- [`qr_encode()`](#qr_encode) encodes a string as a QR code.
- [`qr_save()`](#qr_save) writes a QR code as a PNG image.

## Quick start

```php
<?php
$qr = qr_encode('https://example.com/');
if ($qr === false) {
    exit('Unable to encode the text.');
}

qr_save($qr, 'qrcode.png');
```

## qr_encode

Encodes a string as a QR code.

```text
qr_encode(
    string $text,
    int $version = 1,
    int $level = QR_ECLEVEL_L,
    int $mode = QR_MODE_8,
    int $casesenstitive = 1
): resource|false
```

### Parameters

| Parameter | Description |
|---|---|
| `$text` | The text to encode. It must not be empty, and encoding stops at the first NUL byte. |
| `$version` | The minimum QR version, from `1` to `40`. `0` also selects the smallest version that fits. The library uses a larger version automatically when the data does not fit. |
| `$level` | The error correction level. See [Error correction levels](#error-correction-levels). |
| `$mode` | The encoding mode. Only `QR_MODE_8` and `QR_MODE_KANJI` work. See [Encoding modes](#encoding-modes). |
| `$casesenstitive` | Used only with `QR_MODE_KANJI`. `1` keeps the letter case, and `0` converts lowercase letters to uppercase. `QR_MODE_8` ignores this parameter. |

> [!NOTE]
> The parameter name really is spelled `$casesenstitive`. Use that spelling for
> named arguments, because `casesensitive:` throws an `Error`.

### Return value

Returns a `qr` resource, which PHP frees automatically. Returns `false` without
a warning or error message when encoding fails, for example because:

- `$text` is empty or too long
- `$version` or `$level` is out of range
- `$mode` is not `QR_MODE_8` or `QR_MODE_KANJI`

### Encoding modes

| Constant | Value | Behavior |
|---|---|---|
| `QR_MODE_8` | `2` | Default. Encodes the bytes as they are. Use this mode for UTF-8 text. |
| `QR_MODE_KANJI` | `3` | Treats the text as Shift-JIS and encodes Kanji in Kanji mode. |
| `QR_MODE_NUM` | `0` | Not supported by `qr_encode()`; returns `false`. |
| `QR_MODE_AN` | `1` | Not supported by `qr_encode()`; returns `false`. |
| `QR_MODE_NUL` | `-1` | Not supported by `qr_encode()`; returns `false`. |

### Error correction levels

A higher level survives more damage but holds less data. The capacity column is
the maximum number of bytes at version 40 with `QR_MODE_8`.

| Constant | Value | Recoverable data | Maximum bytes |
|---|---|---|---|
| `QR_ECLEVEL_L` | `0` | About 7% | 2953 |
| `QR_ECLEVEL_M` | `1` | About 15% | 2331 |
| `QR_ECLEVEL_Q` | `2` | About 25% | 1663 |
| `QR_ECLEVEL_H` | `3` | About 30% | 1273 |

### Example

```php
<?php
// UTF-8 text with medium error correction, using the smallest version that fits.
$qr = qr_encode('繁體中文 Hello', 0, QR_ECLEVEL_M);

// The same call with named arguments.
$qr = qr_encode('繁體中文 Hello', level: QR_ECLEVEL_M);
```

## qr_save

Writes a QR code as a 1-bit grayscale PNG image, either to a file or to PHP's
output.

```text
qr_save(resource $qrencode): bool
qr_save(resource $qrencode, string $filename): bool
qr_save(resource $qrencode, int $size, int $margin): bool
qr_save(resource $qrencode, string $filename, int $size, int $margin): bool
```

Use one of these four forms. The meaning of the second argument depends on how
many arguments you pass.

### Parameters

| Parameter | Default | Description |
|---|---|---|
| `$qrencode` | | The resource returned by `qr_encode()`. |
| `$filename` | Output | A local file path. An existing file is overwritten. When omitted, the PNG data goes to PHP's output, which output buffering can capture. |
| `$size` | `3` | The width and height of each module (QR dot) in pixels. Must be at least `1`. |
| `$margin` | `4` | The width of the quiet zone in modules. Must be at least `0`. |

The image width and height in pixels are:

```text
(modules + 2 × margin) × size
modules = 17 + 4 × version
```

For example, a version 1 code with the default settings is
`(21 + 2 × 4) × 3 = 87` pixels square.

### Return value

Returns `true` on success.

Returns `false` and raises `E_WARNING` when the file cannot be opened, the PNG
cannot be written, or the arguments are invalid. See
[Argument restrictions](#argument-restrictions). A failed PNG write can leave an
empty or incomplete file behind.

If `$qrencode` is not a valid `qr` resource, PHP throws a `TypeError`.

### Examples

Save to a file:

```php
<?php
$qr = qr_encode('https://example.com/');
if ($qr !== false && !qr_save($qr, 'large.png', 8, 2)) {
    echo "Unable to save the image.\n";
}
```

Send the image to a browser. Send the header first, and do not output anything
else in the same response:

```php
<?php
$qr = qr_encode('https://example.com/');
if ($qr !== false) {
    header('Content-Type: image/png');
    qr_save($qr, 4, 2);
}
```

Capture the PNG data in a string:

```php
<?php
$qr = qr_encode('https://example.com/');

ob_start();
qr_save($qr);
$png = ob_get_clean();

echo '<img src="data:image/png;base64,' . base64_encode($png) . '" alt="QR code">';
```

### Argument restrictions

`qr_save()` checks all arguments before it opens the output, so a rejected call
leaves an existing file untouched. Each of these calls returns `false` and
raises `E_WARNING`:

| Call | Reason |
|---|---|
| `qr_save($qr, null, 3, 4)` or `qr_save($qr, 5, 3, 4)` | With four arguments, the second must be a string. |
| `qr_save($qr, 'a.png', '3', '4')` or `qr_save($qr, 'a.png', null, null)` | `$size` and `$margin` must be integers. Numeric strings are not converted. |
| `qr_save($qr, null)` or `qr_save($qr, 5)` | With two arguments, the second must be a string. |
| `qr_save($qr, size: 5)` | The second argument is treated as `null`, so this does not match the `$size`, `$margin` form. |
| `$size` less than `1` | The size must be at least `1`. |
| Negative `$margin` | The margin must be at least `0`. |
| Image wider than 1,000,000 pixels | This is the largest width that libpng accepts by default. |

> [!WARNING]
> Very large images that pass these checks can take a long time to generate.
> Set your own, smaller limit on `$size` and `$margin` values that come from
> user input.

### File path restrictions

`$filename` is opened directly as a local path, so:

- Stream wrappers such as `php://` and `s3://` are not supported.
- `open_basedir` does not restrict the path.

Do not build the path from untrusted input.
