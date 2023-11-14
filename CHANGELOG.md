# Changelog

All notable changes to this project will be documented in this file,
in reverse chronological order by release.

## [v2.2.1](https://github.com/zaphyr-org/config/compare/2.2.0...2.2.1) [2023-11-14]

### New:
* Added `.vscode/` to .gitignore file

### Changed:
* Improved unit tests and moved tests to "Unit" directory

### Removed:
* Removed phpstan-phpunit from composer require-dev

## [v2.2.0](https://github.com/zaphyr-org/config/compare/2.1.0...2.2.0) [2023-10-25]

### New:
* Added PSR-11 container integration

### Changed:
* Updated phpunit.xml to v10.4
* Renamed `unit` to `phpunit` in composer.json scripts section
* Changed visibility to `protected` for `tearDown` and `setUp` methods in unit tests

### Removed:
* Removed `excludePaths:` from phpstan.neon
* Removed .dist from phpunit.xml in .gitattributes export-ignore

## [v2.1.0](https://github.com/zaphyr-org/config/compare/2.0.0...2.1.0) [2023-04-05]

### New:
* Added support for neon config files

### Changed:
* Renamed phpunit.xml.dist to phpunit.xml

### Removed:
* Removed "/tests" directory from phpstan paths

## [v2.0.0](https://github.com/zaphyr-org/config/compare/1.0.2...2.0.0) [2023-04-05]

### New:
* First stable release version
