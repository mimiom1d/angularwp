# GCACF to WP-API

[![Join the chat at https://gitter.im/times/gcacf-to-wp-api](https://badges.gitter.im/Join%20Chat.svg)](https://gitter.im/times/gcacf-to-wp-api?utm_source=badge&utm_medium=badge&utm_campaign=pr-badge&utm_content=badge)

Contributors: Mimi (Credit: chrishutchinson, kokarn, ramvi   
Tags: gcacf, acf, api, wp-api   
Requires at least: 3.9.0
Tested up to: 4.3.0 
Stable tag: 1.0.0
License: MIT   
License URI: http://opensource.org/licenses/MIT

Plugs GC Advanced Custom Fields (GCACF) data into the WordPress JSON API (WP-API).

## Description

Puts all GCACF fields from posts, pages, custom post types, comments, attachments and taxonomy terms, into the WP-API output under the 'gcacf' key. Creates a new `/option` endpoint returning options (requires GCACF Options Page plugin).

## Installation

1. Unzip and upload the `gcacf-to-wp-api` directory to `/wp-content/plugins/`.
2. Activate the plugin through the 'Plugins' menu in WordPress

## Frequently Asked Questions

#### How can I log and issue or contribute code?
See the `CONTRIBUTING.md` file.

#### How can I filter posts on a custom field?
See issue #13 for example code to do this.

## Options Endpoints

### `/wp-json/wp/v2/gcacf/options`
Request a list of all options configured in GCACF

### `/wp-json/wp/v2/gcacf/options/{option-name}`
Request a specific option, by passing in the option name

## Changelog

### 1.3.2

* Adds support for custom post types when using v2 of the REST API

### 1.3.1

* Fix to support PHP < 5.4

### 1.3.0

* Add support for v2 of WP-API
* Restructure of much of the code, adding documentation throughout
* Add an additional endpoint for requesting single option values
* Tested with WordPress 4.3.0

### 1.2.1

* Tested with WordPress 4.2.1

### 1.2.0

* Added GCACF data to comments (Thanks @ramvi).

### 1.1.0

* Add `/option` endpoint for GCACF options add-on (Thanks @kokarn).

### 1.0.1

* Fix for addGCACFDataTerm.

### 1.0.0

* Initial release.