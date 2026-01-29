# Changelog

## 0.5.1
### Updated
* Compatible with loki/magento2-admin-components 0.5.x

## 0.5.0
### Added
* Configuration to enable/disable API access
* Configuration to decide reminder email frequency
### Changed
* Using the API no longer bypasses the composer dashboard cache
### Fixed
* Filtering the grids now works (newer version of Loki Admin Components).

## 0.4.2
### Fixed
* Fix incorrect field used for filter on installed packages grid

## 0.4.1
### Fixed
* Removed broken-link logo from emails

## 0.4.0
### Added
* API endpoints to enable central monitoring of installed packages & advisories

## 0.3.1
### Fixed
* Github friendly README.md header

## 0.3.0
### Added
* Setup script to initially enable the cache 

## 0.2.0
### Added
* Added filtering to security advisory grid
* Can configure daily warning emails about security advisories and weekly emails about possible updates

### Fixed
* CVEs and update status are now sortable by priority instead of alphabetical
* Better handling of composer results with empty fields 

## 0.1.2
### Fixed
* composer.json typo


## 0.1.1
### Fixed
* composer.json autoload glitches
### Added
* README badges

## 0.1.0
Initial release
