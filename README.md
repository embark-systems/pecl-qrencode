# php-qrencode

A PHP extension that generates QR codes as PNG images, using
[libqrencode](https://fukuchi.org/works/qrencode/).

[繁體中文](#繁體中文)

## Requirements

- PHP. CI tests versions 8.1 to 8.6.
- libqrencode and libpng, including their development headers

On Debian or Ubuntu:

```bash
sudo apt-get install libqrencode-dev libpng-dev
```

## Installation

```bash
phpize
./configure
make
sudo make install
```

Then enable the extension in `php.ini`:

```ini
extension=qrencode
```

If libqrencode is not in a default location, use
`./configure --with-qrencode=/path/to/prefix`.

## Usage

```php
<?php
$qr = qr_encode('test for qrcode');
if ($qr !== false) {
    qr_save($qr, 'qrcode.png');
}
```

To send the image straight to a browser:

```php
<?php
$qr = qr_encode('test for qrcode');
if ($qr !== false) {
    header('Content-Type: image/png');
    qr_save($qr);
}
```

## Functions

```text
qr_encode(string $text, int $version = 1, int $level = QR_ECLEVEL_L, int $mode = QR_MODE_8, int $casesenstitive = 1): resource|false
qr_save(resource $qrencode): bool
qr_save(resource $qrencode, string $filename): bool
qr_save(resource $qrencode, int $size, int $margin): bool
qr_save(resource $qrencode, string $filename, int $size, int $margin): bool
```

See the [function reference](docs/functions.md) for parameters, constants,
argument restrictions, and examples.

## 繁體中文

這是用 [libqrencode](https://fukuchi.org/works/qrencode/) 產生 QR code PNG 圖片的 PHP extension。

### 系統需求

- PHP。CI 測試 8.1 到 8.6 版。
- libqrencode 和 libpng，以及它們的開發標頭檔

Debian 或 Ubuntu 可執行：

```bash
sudo apt-get install libqrencode-dev libpng-dev
```

### 安裝

```bash
phpize
./configure
make
sudo make install
```

接著在 `php.ini` 啟用 extension：

```ini
extension=qrencode
```

如果 libqrencode 不在預設位置，請使用 `./configure --with-qrencode=/path/to/prefix`。

### 使用方式

```php
<?php
$qr = qr_encode('test for qrcode');
if ($qr !== false) {
    qr_save($qr, 'qrcode.png');
}
```

直接將圖片送到瀏覽器：

```php
<?php
$qr = qr_encode('test for qrcode');
if ($qr !== false) {
    header('Content-Type: image/png');
    qr_save($qr);
}
```

### 函式

函式簽章請參閱上方的 [Functions](#functions)。參數、常數、引數限制和範例請參閱[函式參考](docs/functions.zh-TW.md)。
