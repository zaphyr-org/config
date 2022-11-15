# Changelog

All notable changes to this project will be documented in this file,
in reverse chronological order by release.

## [v1.0.2](https://github.com/zaphyr-org/config/compare/1.0.1...1.0.2) [2022-11-15]

### Changed:
* Changed Link to website pages and remove unused files
* Updated composer dependencies
  * Updated symfony/yaml to v5.4
  * Updated phpstan/phpstan to v1.9
  * Updated phpstan/phpstan-phpunit to v1.2
  * Updated phpunit/phpunit to v9.5
  * Update roave/security-advisories to dev-latest
  * Update squizlabs/php_codesniffer to v3.7

### Removed:
* Removed unnecessary doc blocs

### Fixed:
* Fixed typos and dead links in readme files
* Fixed typo in README.md

## [v1.0.1](https://github.com/zaphyr-org/config/compare/1.0.0...1.0.1) [2022-07-10]

### New:
* Add release date to CHANGELOG.md
* Add Packagist badges to README.md

### Changed:
* Move documentation to index.md
* Use @zaphyr.org email addresses
* Update CHANGELOG.md

### Removed:
* Unused $key parameter in Config::makeReplacements removed

### Fixed:
* Resolve [#1](https://github.com/zaphyr-org/config/issues/1); Delete temporarily created files during unit tests
* Fix broken link for "Create custom replacer" in documentation index.md

## v1.0.0] [2022-07-09]

### New:
* First stable release version
