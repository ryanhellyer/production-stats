# Laravel Production Stats

Production-ready performance monitoring for Laravel applications. Automatically injects performance statistics as HTML comments—lightweight, secure, and production-safe.

For years I've added basic performance stats to WordPress sites as HTML comments in the footer. They're always available in the HTML source, don't bother users, and help determine if a site is cached and how quickly pages generate. When I moved to Laravel, I missed this functionality and kept manually adding stats to templates. This package automates that process.

**Note:** This package injects page load time and generation timestamp information as HTML comments visible in the page source. While this is typically not sensitive information, you shouldn't use this package if you don't want site visitors being able to see this data.

## Features

- **Automatic tracking** - Measures page load time and generation timestamp
- **Production-ready** - Unlike development-only tools, safe for production use
- **Non-intrusive** - Injects as HTML comments, invisible to end users
- **Safe** - Only processes HTML responses, won't break JSON/XML
- **Zero configuration** - Works immediately after installation

## Requirements

- PHP 8.2+
- Laravel 10, 11, or 12

## Installation

```bash
composer require ryanhellyer/laravel-production-stats
```

## Usage

No configuration needed. The package automatically tracks page load times and generation timestamps, injecting them as HTML comments before the closing `</body>` tag.

### Example Output

```html
<!-- Total page load time: 25.23 ms | Page generated at 2025-01-07 14:30:45 -->
</body>
```

View in your browser's page source.

## What Gets Tracked

- **Page load time** - Total time from Laravel bootstrap to response (milliseconds)
- **Generation timestamp** - When the page was generated

## Changelog

No releases yet.

## License

GPL-2.0 - See [LICENSE](LICENSE) file for details.

## Author

**Ryan Hellyer** - ryan@hellyer.kiwi | [GitHub](https://github.com/ryanhellyer)

## Support

Ping me via ryan.hellyer.kiwi/contact if you encounter any problems.
