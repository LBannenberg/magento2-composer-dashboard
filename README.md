# Magento 2 Composer Dashboard

[![Latest Version on Packagist](https://img.shields.io/packagist/v/corrivate/magento2-composer-dashboard?color=blue)](https://packagist.org/packages/corrivate/magento2-composer-dashboard)
[![MIT Licensed](https://img.shields.io/badge/license-MIT-brightgreen.svg)](LICENSE.md)

By `Corrivate`

## Introduction

This module provides a dashboard inside the Magento admin to view your composer packages;
* What packages are installed? Are they up to date?
* Are there any security advisories for these packages?

Under the hood it uses Composer to fetch the data, but it exposes that data in a way that's friendlier for merchants, project managers etc. to review.

## Installation

Installation is straightforward. In your local dev environment you can run:

```bash
composer require corrivate/magento2-composer-dashboard
bin/magento setup:upgrade
```

This should add the following to your `app/etc/config.php`:
```php
'Corrivate_ComposerDashboard' => 1,
'Loki_CssUtils' => 1,
'Loki_Base' => 1,
'Loki_Components' => 1,
'Loki_AdminComponents' => 1,
```

### Enable the dashboard cache

Because fetching package version information through composer takes some time, we cache the results. You should enable that cache with:

```bash
bin/magento cache:enable corrivate_composerdashboard
```

### Permissions
If your admin users have customized roles, you may need to grant them permission to use the `Composer Dashboard` under System > User Roles.

### Loki Admin Components

As you can see, we depend on [Loki Admin Components](https://loki-extensions.com/docs/admin-components) under the hood to present the dashboard. Writing this package was a good test project to see how much easier Loki makes it to write admin functionality compared to the classic Magento UI components. (Turns out, a lot.)

## Usage

In the admin, you can find the dashboard under the System > Composer Dashboard heading.

### Security Advisories

This uses `composer audit` under the hood to retrieve advisories for installed packages.

### Installed Packages

This uses `composer show` to gather information about all your installed (non-dev) packages. 

Note that the latest version reported here is the latest version *you have access to*. It's possible that for some (private/third party) packages there are newer versions that you don't have access to, for example because you'd need to renew your subscription. Unfortunately there's no universal way to check that through Composer.


## Advanced

### Package aliases

Some vendors use commercial names for packages that are quite different than their composer names for those packages. To make this easier to read you can provide an alias through `di.xml`:

```xml
    <type name="Corrivate\ComposerDashboard\Model\Composer\PackageAliases">
        <arguments>
            <argument name="aliases" xsi:type="array">
                <item name="amasty/shopby" xsi:type="string">Improved Layered Navigation</item>
            </argument>
        </arguments>
    </type>
```

## Known Issues
* Filters in the installed packages grid don't work. This feature is not fully implemented yet in the Loki Admin Components.

## Corrivate
(en.wiktionary.org)

Etymology

From Latin *corrivatus*, past participle of *corrivare* ("to corrivate").

### Verb

**corrivate** (*third-person singular simple present* **corrivates**, *present participle* **corrivating**, *simple past and past participle* **corrivated**)

(*obsolete*) To cause to flow together, as water drawn from several streams. 

