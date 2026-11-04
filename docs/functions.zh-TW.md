# qrencode 函式參考

繁體中文 | [English](functions.md)

本文件說明 extension 0.11 版。這個 extension 包裝
[libqrencode](https://fukuchi.org/works/qrencode/)，提供兩個函式：

- [`qr_encode()`](#qr_encode)：將字串編碼為 QR code。
- [`qr_save()`](#qr_save)：將 QR code 輸出為 PNG 圖片。

## 快速開始

```php
<?php
$qr = qr_encode('https://example.com/');
if ($qr === false) {
    exit('無法編碼這段文字。');
}

qr_save($qr, 'qrcode.png');
```

## qr_encode

將字串編碼為 QR code。

```text
qr_encode(
    string $text,
    int $version = 1,
    int $level = QR_ECLEVEL_L,
    int $mode = QR_MODE_8,
    int $casesenstitive = 1
): resource|false
```

### 參數

| 參數 | 說明 |
|---|---|
| `$text` | 要編碼的文字。不可為空字串，且編碼會在第一個 NUL byte 停止。 |
| `$version` | 最小 QR 版本，範圍為 `1` 到 `40`。`0` 同樣會選用放得下資料的最小版本。資料放不下時，函式庫會自動改用較大的版本。 |
| `$level` | 錯誤修正等級，請參閱[錯誤修正等級](#錯誤修正等級)。 |
| `$mode` | 編碼模式，只有 `QR_MODE_8` 和 `QR_MODE_KANJI` 可用，請參閱[編碼模式](#編碼模式)。 |
| `$casesenstitive` | 只在 `QR_MODE_KANJI` 下使用。`1` 保留大小寫，`0` 會將小寫字母轉成大寫。`QR_MODE_8` 會忽略這個參數。 |

> [!NOTE]
> 參數名稱的拼法確實是 `$casesenstitive`。使用具名引數時請照此拼法，
> 寫成 `casesensitive:` 會擲出 `Error`。

### 回傳值

成功時回傳 `qr` resource，由 PHP 自動釋放。編碼失敗時回傳 `false`，不會產生警告或錯誤訊息。常見原因包括：

- `$text` 是空字串或太長
- `$version` 或 `$level` 超出範圍
- `$mode` 不是 `QR_MODE_8` 或 `QR_MODE_KANJI`

### 編碼模式

| 常數 | 值 | 行為 |
|---|---|---|
| `QR_MODE_8` | `2` | 預設值。依原始 byte 編碼，UTF-8 文字請使用這個模式。 |
| `QR_MODE_KANJI` | `3` | 將文字視為 Shift-JIS，並以 Kanji 模式編碼漢字。 |
| `QR_MODE_NUM` | `0` | `qr_encode()` 不支援，會回傳 `false`。 |
| `QR_MODE_AN` | `1` | `qr_encode()` 不支援，會回傳 `false`。 |
| `QR_MODE_NUL` | `-1` | `qr_encode()` 不支援，會回傳 `false`。 |

### 錯誤修正等級

等級越高越能承受損毀，但可容納的資料越少。「最大 byte 數」是版本 40 搭配 `QR_MODE_8` 時的上限。

| 常數 | 值 | 可復原資料 | 最大 byte 數 |
|---|---|---|---|
| `QR_ECLEVEL_L` | `0` | 約 7% | 2953 |
| `QR_ECLEVEL_M` | `1` | 約 15% | 2331 |
| `QR_ECLEVEL_Q` | `2` | 約 25% | 1663 |
| `QR_ECLEVEL_H` | `3` | 約 30% | 1273 |

### 範例

```php
<?php
// UTF-8 文字，使用中等錯誤修正，並選用放得下資料的最小版本。
$qr = qr_encode('繁體中文 Hello', 0, QR_ECLEVEL_M);

// 以具名引數寫出相同的呼叫。
$qr = qr_encode('繁體中文 Hello', level: QR_ECLEVEL_M);
```

## qr_save

將 QR code 輸出為 1-bit 灰階 PNG 圖片，可寫入檔案或 PHP 的輸出。

```text
qr_save(resource $qrencode): bool
qr_save(resource $qrencode, string $filename): bool
qr_save(resource $qrencode, int $size, int $margin): bool
qr_save(resource $qrencode, string $filename, int $size, int $margin): bool
```

請使用這四種寫法之一。第二個引數的意義取決於傳入的引數數量。

### 參數

| 參數 | 預設值 | 說明 |
|---|---|---|
| `$qrencode` | | `qr_encode()` 回傳的 resource。 |
| `$filename` | 輸出 | 本機檔案路徑，已存在的檔案會被覆寫。省略時，PNG 資料會送到 PHP 的輸出，可用 output buffering 擷取。 |
| `$size` | `3` | 每個 module（QR 的點）的寬高，單位為像素，至少為 `1`。 |
| `$margin` | `4` | 靜區（quiet zone）的寬度，單位為 module，至少為 `0`。 |

圖片的寬高（像素）計算方式：

```text
(modules + 2 × margin) × size
modules = 17 + 4 × version
```

例如，版本 1 搭配預設設定時，圖片為 `(21 + 2 × 4) × 3 = 87` 像素見方。

### 回傳值

成功時回傳 `true`。

無法開啟檔案、無法寫入 PNG，或引數無效時，會回傳 `false` 並產生 `E_WARNING`，請參閱[引數限制](#引數限制)。PNG 寫入失敗時，可能留下空白或不完整的檔案。

如果 `$qrencode` 不是有效的 `qr` resource，PHP 會擲出 `TypeError`。

### 範例

儲存為檔案：

```php
<?php
$qr = qr_encode('https://example.com/');
if ($qr !== false && !qr_save($qr, 'large.png', 8, 2)) {
    echo "無法儲存圖片。\n";
}
```

將圖片送到瀏覽器。請先送出 header，且同一個回應不要輸出其他內容：

```php
<?php
$qr = qr_encode('https://example.com/');
if ($qr !== false) {
    header('Content-Type: image/png');
    qr_save($qr, 4, 2);
}
```

將 PNG 資料擷取為字串：

```php
<?php
$qr = qr_encode('https://example.com/');

ob_start();
qr_save($qr);
$png = ob_get_clean();

echo '<img src="data:image/png;base64,' . base64_encode($png) . '" alt="QR code">';
```

### 引數限制

`qr_save()` 會先檢查所有引數，再開啟輸出目標，因此被拒絕的呼叫不會動到已存在的檔案。以下呼叫都會回傳 `false` 並產生 `E_WARNING`：

| 呼叫方式 | 原因 |
|---|---|
| `qr_save($qr, null, 3, 4)` 或 `qr_save($qr, 5, 3, 4)` | 傳入四個引數時，第二個必須是字串。 |
| `qr_save($qr, 'a.png', '3', '4')` 或 `qr_save($qr, 'a.png', null, null)` | `$size` 和 `$margin` 必須是整數，數字字串不會被轉換。 |
| `qr_save($qr, null)` 或 `qr_save($qr, 5)` | 傳入兩個引數時，第二個必須是字串。 |
| `qr_save($qr, size: 5)` | 第二個引數會被視為 `null`，不符合 `$size`、`$margin` 的寫法。 |
| `$size` 小於 `1` | size 至少為 `1`。 |
| `$margin` 為負數 | margin 至少為 `0`。 |
| 圖片寬度超過 1,000,000 像素 | 這是 libpng 預設接受的最大寬度。 |

> [!WARNING]
> 通過這些檢查的超大圖片仍可能需要很長的產生時間。來自使用者輸入的 `$size` 和 `$margin`，請自行設定較小的上限。

### 檔案路徑限制

`$filename` 會直接以本機路徑開啟，因此：

- 不支援 `php://`、`s3://` 等 stream wrapper。
- 路徑不受 `open_basedir` 限制。

請勿用不受信任的輸入組成路徑。
