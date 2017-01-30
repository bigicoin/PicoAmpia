# PicoAmpia

PicoAmpia is a plugin for Pico CMS that provides support for Google AMP and Facebook Instant Articles.

# Config

PicoAmpia uses a few configuration variables:

```
$config[ 'PicoAmpia.enabled' ] = true;
$config[ 'PicoAmpia.ampRoot' ] = 'amp';
$config[ 'PicoAmpia.iaRoot' ] = 'ia';
$config[ 'PicoAmpia.iaRss' ] = 'facebookiarss';
$config[ 'PicoAmpia.facebookPageId' ] = '1234567890123456';
$config[ 'PicoAmpia.facebookDefaultStyle' ] = 'default';
```

# Themes

You will need to put valid Google AMP HTML as well as valid Facebook Instant Articles markup in your
themes folder, corresponding to the AMP Root and IA Root config path.

For more info on how to write proper markup for AMP and IA, please see the Notes section below.

# Notes

Google AMP tutorial: https://www.ampproject.org/docs/get_started/create

Facebook Instant Articles: https://developers.facebook.com/docs/instant-articles/guides/articlecreate
