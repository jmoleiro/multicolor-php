# multicolor-php

A lightweight, zero-dependency PHP library for CLI text colorization. Emit ANSI escape codes directly with support for a broad set of styles compatible with [chalk](https://github.com/chalk/chalk), [picocolors](https://github.com/alexeyraspopov/picocolors), [yoctocolors](https://github.com/sindresorhus/yoctocolors), and Node's [`util.styleText`](https://nodejs.org/api/util.html#utilstyletextformat-text-options).

This library PHP is a port of the NodeJS library [@jmoleiro/multicolor](https://github.com/jmoleiro/multicolor)

## Features

- **Zero dependencies** - pure PHP, single file
- **Familiar API** - simple `multicolor($text, $colors)` function
- **Rich style set** - modifiers, foregrounds, bright colors, backgrounds, and gray/grey aliases
- **Smart detection** - respects `NO_COLOR`, `FORCE_COLOR`, `TERM=dumb`, and TTY/non-TTY (pipes/redirects)
- **Manual control** - override detection with `multicolor_enable()`
- **Safe & forgiving** - unknown styles are silently ignored; returns original text when colorization is disabled or unsupported

## Requirements

- PHP >= 7.4

## Installation

### via Composer

```bash
composer require j_moleiro/multicolor-php
```

After installing, Composer's autoloader will load `multicolor.php` automatically (configured via `files` autoload).

### Manually

Just include the file in your project:

```php
<?php
require_once '/path/to/multicolor.php';
```

## Usage

### Basic

```php
<?php
require 'vendor/autoload.php'; // or require_once 'multicolor.php';

echo multicolor('Hello World', ['green', 'bold']) . PHP_EOL;
```

### Multiple styles

Styles are applied in order; nesting/stacking works as you'd expect (each call wraps the current string):

```php
echo multicolor('Warning!', ['yellow', 'bold', 'underline']) . PHP_EOL;
// Or combine with background
echo multicolor('Error', ['white', 'bgRed', 'bold']) . PHP_EOL;
```

### Chaining mentally / building strings

```php
$text = multicolor('Success', ['greenBright']) . ': ' . multicolor('All tests passed', ['cyan']);
echo $text . PHP_EOL;
```

### Disable/enable color output

```php
// Force off (useful for CI logs)
multicolor_enable(false);
echo multicolor('This will be plain', ['red', 'bold']); // plain text

// Force on
multicolor_enable(true);
echo multicolor('This will be colored', ['red', 'bold']);

// Clear override and re-detect environment
multicolor_enable(null);
```

### Environment variables

- `NO_COLOR` - If set to a non-empty value other than `0`/`false` (case-insensitive), colors are disabled (follows [no-color.org](https://no-color.org/)).
- `FORCE_COLOR` - If set to a non-empty value other than `0`/`false`, colors are forced on (unless `NO_COLOR` takes precedence in effect as coded).
- `TERM=dumb` - Disables color output.
- Non-TTY stdout (pipes/redirects) - Defaults to plain text unless forced.

## Supported styles

### Modifiers
| Name | Effect | Open | Close |
|---|---|---|---|
| `reset` | Reset all styles | 0 | 0 |
| `bold` | Bold/bright | 1 | 22 |
| `dim` | Dim/faint | 2 | 22 |
| `italic` | Italic | 3 | 23 |
| `underline` | Underline | 4 | 24 |
| `inverse` | Swap foreground/background | 7 | 27 |
| `hidden` | Hidden/invisible | 8 | 28 |
| `strikethrough` | Strikethrough | 9 | 29 |

### Foreground colors
| Name | Open | Close |
|---|---|---|
| `black` | 30 | 39 |
| `red` | 31 | 39 |
| `green` | 32 | 39 |
| `yellow` | 33 | 39 |
| `blue` | 34 | 39 |
| `magenta` | 35 | 39 |
| `cyan` | 36 | 39 |
| `white` | 37 | 39 |

### Bright foreground colors
| Name | Open | Close |
|---|---|---|
| `blackBright` | 90 | 39 |
| `redBright` | 91 | 39 |
| `greenBright` | 92 | 39 |
| `yellowBright` | 93 | 39 |
| `blueBright` | 94 | 39 |
| `magentaBright` | 95 | 39 |
| `cyanBright` | 96 | 39 |
| `whiteBright` | 97 | 39 |
| `gray` / `grey` | 90 | 39 (aliases) |

### Background colors
| Name | Open | Close |
|---|---|---|
| `bgBlack` | 40 | 49 |
| `bgRed` | 41 | 49 |
| `bgGreen` | 42 | 49 |
| `bgYellow` | 43 | 49 |
| `bgBlue` | 44 | 49 |
| `bgMagenta` | 45 | 49 |
| `bgCyan` | 46 | 49 |
| `bgWhite` | 47 | 49 |

### Bright background colors
| Name | Open | Close |
|---|---|---|
| `bgBlackBright` | 100 | 49 |
| `bgRedBright` | 101 | 49 |
| `bgGreenBright` | 102 | 49 |
| `bgYellowBright` | 103 | 49 |
| `bgBlueBright` | 104 | 49 |
| `bgMagentaBright` | 105 | 49 |
| `bgCyanBright` | 106 | 49 |
| `bgWhiteBright` | 107 | 49 |
| `bgGray` / `bgGrey` | 100 | 49 (aliases) |

## API Reference

### `multicolor(string $text, array $colors = []): string`
Colorizes text for terminal output. Returns the original text if:
- `$colors` is empty
- `$text` is empty
- Color output is not supported/disabled by detection or `multicolor_enable()`

Unknown style names are silently ignored.

### `multicolor_styles(): array`
Returns the map of supported style names to `[openCode, closeCode]`. Can be used to inspect available styles.

### `multicolor_is_supported(): bool`
Detects whether styled output should be emitted based on environment, TTY, and manual overrides.

### `multicolor_enable(?bool $enabled = true): void`
Manually force colorization on/off. Pass `true` to always colorize, `false` to never, or `null` to clear the override and re-detect.

## Examples

```php
<?php

require 'vendor/autoload.php';

echo multicolor('multicolor-php', ['cyan', 'bold']) . ' - ' . multicolor('ready!', ['greenBright']) . PHP_EOL;

// Create a colored list
$items = ['Build', 'Test', 'Deploy'];
foreach ($items as $i => $item) {
    $colors = $i === 2 ? ['magenta', 'bold'] : ['white'];
    echo '  ' . multicolor('•', ['blue']) . ' ' . multicolor($item, $colors) . PHP_EOL;
}

// Status messages
echo multicolor('[OK]', ['green', 'bold']) . ' Operation completed' . PHP_EOL;
echo multicolor('[WARN]', ['yellow', 'bold']) . ' Check configuration' . PHP_EOL;
echo multicolor('[ERR]', ['red', 'bold']) . ' Something went wrong' . PHP_EOL;
```

## License

MIT License. See [LICENSE](LICENSE) if present, or the header in source.
