# Mautic Whitelabel

**Mautic Whitelabel** is a Composer package that allows you to customize the Mautic UI dynamically using `.env` values.  
You can modify logos, background colors, and other assets without directly changing Mautic core files.

## Installation

Run the following command inside your **Mautic project root**:

```sh
composer require matbcvo/mautic-whitelabel
```

## Configuration

Ensure your `.env` file (or compiled env) contains the required branding variables:

```
WHITELABEL_BRAND="Marketing Automation Ltd"
WHITELABEL_FOOTER="Marketing Automation Ltd. All Rights Reserved."
WHITELABEL_PRIMARY="#ffffff"
WHITELABEL_HOVER="#ffffff"
WHITELABEL_ACTIVE_ICON="#ffffff"
WHITELABEL_LOGO_BACKGROUND="#ffffff"
WHITELABEL_SIDEBAR_LOGO=/custom-logo.png
WHITELABEL_SIDEBAR_LOGO_WIDTH=130
WHITELABEL_SIDEBAR_LOGO_MARGIN_TOP=0
WHITELABEL_SIDEBAR_LOGO_MARGIN_LEFT=0
WHITELABEL_SIDEBAR_LOGO_MARGIN_RIGHT=0
WHITELABEL_SIDEBAR_BACKGROUND="#ffffff"
WHITELABEL_SIDEBAR_SUBMENU_BACKGROUND="#ffffff"
WHITELABEL_SIDEBAR_LINK="#ffffff"
WHITELABEL_SIDEBAR_LINK_HOVER="#ffffff"
WHITELABEL_SIDEBAR_DIVIDER="#ffffff"
WHITELABEL_DIVIDER_LEFT=50
WHITELABEL_SUBMENU_BULLET_BACKGROUND="#ffffff"
WHITELABEL_SUBMENU_BULLET_SHADOW="#ffffff"
WHITELABEL_LOGIN_LOGO=/custom-logo.png
WHITELABEL_LOGIN_LOGO_WIDTH=/custom-logo.png
WHITELABEL_LOGIN_LOGO_MARGIN_TOP=20
WHITELABEL_LOGIN_LOGO_MARGIN_BOTTOM=20
WHITELABEL_FAVICON=/custom-logo.png
```

If using production, make sure your .env values are compiled:
```sh
composer dump-env prod
```

## Applying Whitelabel Branding

Run manually at any time:
```sh
composer mautic:whitelabel
```

## Contributing

We welcome contributions! If you have ideas or fixes, feel free to submit a PR.
