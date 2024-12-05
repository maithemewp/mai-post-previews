# Changelog

## 0.3.1 (12/5/24)
* Changed: Update the updater.

## 0.3.0 (7/19/24)
* Added: New settings for `rel` attributes `nofollow`, `noreferrer`, and `sponsored`.
* Added: Better error handling and fallbacks for blocked, broken, etc. urls.
* Changed: Links no longer open in a new window if the link is on the same domain.
* Changed: Update embed package.
* Changed: Better file structure and now registering block via block.json.
* Changed: Update the updater and other dependencies.
* Fixed: No longer passing `null` to `ltrim()` and `rtrim()` (forcing string) for more PHP 8 compatibility.

## 0.2.2 (11/29/23)
* Added: PHP 8.2 compatibility.

## 0.2.1 (11/27/23)
* Changed: Update the updater.
* Fixed: Image fallback sometimes not getting correct string.

## 0.2.0 (10/6/23)
* Fixed: URL and description sometimes causing errors when fetching meta from url.
* Changed: URL now uses exact url from block settings.

## 0.1.2 (6/8/23)
* Fixed: Make sure 'host' is returned when parsing url.

## 0.1.1 (6/8/23)
* Fixed: fallback URL when fetching data.
* Changed: Better minification of JS file.

## 0.1.0
* Initial release.
