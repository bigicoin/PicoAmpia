# PicoAmpia

PicoAmpia is a plugin for Pico CMS that provides support for Google **AMP** and Facebook **I**nstant **A**rticles.

# Introduction

[AMP](https://www.ampproject.org/) is a project created by Google with the goal of speeding up loading of
web pages (usually content pages) on mobile devices; predominantly used by Google search results.

Similarly, [Instant Articles](https://instantarticles.fb.com/) is a project created by Facebook with the
same goal, except for article links that appear on Facebook.

Although they serve similar purposes, the output format required to use AMP and IA are radically different;
in fact, even the paradigm of the two systems are different.

The PicoAmpia plugin aims to make it easy for your content web site (running on Pico CMS) to start providing
your content in both the AMP format and Instant Articles format.

It's possible to use Ampia for only AMP, or only Instant Articles. See Config section below.

# How AMP works (briefly)

AMP works by the site providing a different URL that renders different HTML output for the same content, for each page.

For example, if you have a page: `https://yourdomain.com/sub/page`

You will need to also provide an AMP version of this page at: `https://yourdomain.com/amp/sub/page`

The `amp` part of this can be at the root of the URL path, can be at a subdomain, or at the end of the path.
The PicoAmpia plugin works by inserting it at the root of the path like the example above.

## How does Google find the AMP version of the page?

Your ordinary page will contain a `<link>` tag which points to the URL of the AMP version of the page.

# How Instant Articles work (briefly)

Facebook Instant Articles are associated with a Facebook Page, which is what you would create for your company or brand.

The [quickstart guide](https://developers.facebook.com/docs/instant-articles/quickstart) is a good overview of the steps,
which I will also lay out below.

Facebook requires you to [sign up](https://www.facebook.com/instant_articles/signup) for the Instant Articles program
for your Facebook Page first.

After this, you configure your articles' styling. (this includes font face, color, etc.)

Then you have to import your articles. There are several ways to do this, such as doing so manually using an admin interface
on their site, or making an API call to their server, or providing an RSS feed. PicoAmpia uses the RSS feed method.

You can think of importing like uploading your articles to their server. With the RSS feed method, they will pull updated
articles from your server instead.

Finally, you need to submit for Facebook's approvals for your Instant Articles to work on Facebook.

# Config

PicoAmpia uses a few configuration variables. Here is an example using all of them.

```
$config[ 'PicoAmpia.enabled' ] = true;
$config[ 'PicoAmpia.ampRoot' ] = 'amp';
$config[ 'PicoAmpia.iaRoot' ] = 'ia';
$config[ 'PicoAmpia.iaRss' ] = 'facebookiarss';
$config[ 'PicoAmpia.facebookPageId' ] = '1234567890123456';
$config[ 'PicoAmpia.facebookDefaultStyle' ] = 'default';
```

`PicoAmpia.enabled`  
This enables or disables the plugin completely.

`PicoAmpia.ampRoot`  
This is the name of the root level addition to your pages' URLs for AMP versions of the pages. For example,
`https://yourdomain.com/sub/page` becomes `https://yourdomain.com/amp/sub/page` in this case. For most sites
`amp` is fine, but if you happen to have real pages situated at the URL `/amp`, then you will want to rename
this to something more unique.

Additionally, this is also the name of the subdirectory you will put your AMP themes in, under the `/themes`
folder.

`PicoAmpia.iaRoot`  
Similar to above, this is the name of the root level addition to your pages' URLs for Instant Articles versions
of the pages. Again, this is also the name of the subdirectory for IA themes.

`PicoAmpia.iaRss`  
This is the URL to the RSS feed that you will use for Facebook to pull your updated Instant Articles from.

`PicoAmpia.facebookPageId`  
This is your Facebook Page ID of your brand, where you've registered Instant Articles with.

`PicoAmpia.facebookDefaultStyle`  
This is the name of the `style` to use for your articles. See "setting up styles" in Instant Articles.

# Themes

You will need to put valid Google AMP HTML as well as valid Facebook Instant Articles markup in your
themes folder, corresponding to the AMP Root and IA Root config path.

A sample AMP HTML file and an Instant Articles markup file are included in this repository.

For more info on how to write proper markup for AMP and IA, please see the Notes section below.

Note that since Instant Articles styling are largely configured in the Facebook Page Admin interface,
you can most likely use the default included sample IA template file as-is. You can then configure
look-and-feel in the Page Admin interface for styling your Instant Articles.

# Notes and References

Google AMP tutorial: https://www.ampproject.org/docs/get_started/create

Facebook Instant Articles: https://developers.facebook.com/docs/instant-articles/guides/articlecreate
