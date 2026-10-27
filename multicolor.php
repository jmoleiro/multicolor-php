<?php

/**
 * multicolor-php — single-file CLI text colorization utility.
 *
 * PHP equivalent of the NodeJS [multicolor](https://github.com/jmoleiro/multicolor) library, without external dependencies.
 * ANSI codes are emitted directly, covering the union of styles
 * supported by chalk, picocolors, yoctocolors and Node's styleText.
 *
 * Usage:
 *   require 'multicolor.php';
 *   echo multicolor('Hello', ['red', 'bold']);
 *   echo multicolor('Hello', ['bgBlue', 'whiteBright', 'underline']);
 *
 * Unknown style names are silently ignored (mirrors the JS try/catch).
 */

if (!function_exists('multicolor')) {
    /**
     * Colorize text for terminal output.
     *
     * @param string $text   Text to colorize.
     * @param array  $colors List of color/modifier names, e.g. ['red', 'bold'].
     * @return string Colorized text (or original text when unsupported/disabled).
     */
    function multicolor(string $text, array $colors = []): string
    {
        if ($colors === [] || $text === '' || !multicolor_is_supported()) {
            return $text;
        }

        static $styles = null;
        if ($styles === null) {
            $styles = multicolor_styles();
        }

        $result = $text;
        foreach ($colors as $name) {
            if (!is_string($name) || !isset($styles[$name])) {
                continue; // silently ignore unknown modifiers, like the JS version
            }
            [$open, $close] = $styles[$name];
            $result = "\e[{$open}m{$result}\e[{$close}m";
        }

        return $result;
    }
}

if (!function_exists('multicolor_styles')) {
    /**
     * Map of supported style name => [open code, close code].
     *
     * Covers Node util.styleText / chalk / picocolors / yoctocolors names:
     * foregrounds, bright foregrounds, gray/grey aliases,
     * backgrounds, bright backgrounds, and modifiers.
     *
     * @return array<string, array{int, int}>
     */
    function multicolor_styles(): array
    {
        return [
            // Modifiers
            'reset'         => [0, 0],
            'bold'          => [1, 22],
            'dim'           => [2, 22],
            'italic'        => [3, 23],
            'underline'     => [4, 24],
            'inverse'       => [7, 27],
            'hidden'        => [8, 28],
            'strikethrough' => [9, 29],

            // Foreground colors
            'black'   => [30, 39],
            'red'     => [31, 39],
            'green'   => [32, 39],
            'yellow'  => [33, 39],
            'blue'    => [34, 39],
            'magenta' => [35, 39],
            'cyan'    => [36, 39],
            'white'   => [37, 39],

            // Bright foreground colors
            'blackBright'   => [90, 39],
            'redBright'     => [91, 39],
            'greenBright'   => [92, 39],
            'yellowBright'  => [93, 39],
            'blueBright'    => [94, 39],
            'magentaBright' => [95, 39],
            'cyanBright'    => [96, 39],
            'whiteBright'   => [97, 39],

            // Gray aliases (bright black)
            'gray' => [90, 39],
            'grey' => [90, 39],

            // Background colors
            'bgBlack'   => [40, 49],
            'bgRed'     => [41, 49],
            'bgGreen'   => [42, 49],
            'bgYellow'  => [43, 49],
            'bgBlue'    => [44, 49],
            'bgMagenta' => [45, 49],
            'bgCyan'    => [46, 49],
            'bgWhite'   => [47, 49],

            // Bright background colors
            'bgBlackBright'   => [100, 49],
            'bgRedBright'     => [101, 49],
            'bgGreenBright'   => [102, 49],
            'bgYellowBright'  => [103, 49],
            'bgBlueBright'    => [104, 49],
            'bgMagentaBright' => [105, 49],
            'bgCyanBright'    => [106, 49],
            'bgWhiteBright'   => [107, 49],

            // Gray background aliases (bright black background)
            'bgGray' => [100, 49],
            'bgGrey' => [100, 49],
        ];
    }
}

if (!function_exists('multicolor_is_supported')) {
    /**
     * Detect whether styled output should be emitted.
     *
     * Respects NO_COLOR (https://no-color.org/), TERM=dumb,
     * non-TTY output, and a manual override via multicolor_enable().
     */
    function multicolor_is_supported(): bool
    {
        static $forced = null;
        if ($forced !== null) {
            return $forced;
        }

        // Manual override stored via $GLOBALS by multicolor_enable().
        if (array_key_exists('__multicolor_enabled', $GLOBALS)) {
            return (bool) $GLOBALS['__multicolor_enabled'];
        }

        // NO_COLOR: present and non-empty (except "0"/"false") disables color.
        $noColor = getenv('NO_COLOR');
        if ($noColor !== false && $noColor !== '' && $noColor !== '0' && strtolower((string) $noColor) !== 'false') {
            return false;
        }

        // Explicit opt-in always wins (except the NO_COLOR case above).
        $force = getenv('FORCE_COLOR');
        if ($force !== false && $force !== '' && $force !== '0' && strtolower((string) $force) !== 'false') {
            return true;
        }

        $term = getenv('TERM');
        if ($term === 'dumb') {
            return false;
        }

        // Non-interactive output (pipes/redirects) defaults to plain text.
        if (function_exists('stream_isatty')) {
            $stream = defined('STDOUT') ? STDOUT : fopen('php://stdout', 'w');
            $isTty = @$stream ? @stream_isatty($stream) : false;
            if ($isTty === false) {
                return false;
            }
        }

        return true;
    }
}

if (!function_exists('multicolor_enable')) {
    /**
     * Manually force colorized output on or off.
     *
     * @param bool|null $enabled true = always colorize, false = never,
     *                           null = clear override and re-detect.
     */
    function multicolor_enable(?bool $enabled = true): void
    {
        if ($enabled === null) {
            unset($GLOBALS['__multicolor_enabled']);
            return;
        }
        $GLOBALS['__multicolor_enabled'] = $enabled;
    }
}
