=== Plugin Name ===
Contributors: Victoria Scales
Tags: stats, statistics, clicks, traffic, visit, monitor, data, figures, performance, url
Requires at least: 3.0
Tested up to: 3.1.3
Donate link: http://www.amazon.co.uk/wishlist/2FRM957UJWLZ2
Stable tag: trunk

VSF Simple Stats plugin. Records date, host, IP, browser info and url.

== Description ==

Records all hits to your website, but excludes hits from IPs that have been added to the filter list.  IP addresses can be filtered individually or as a range from the admin page.

The stats that this plugin generates are basic.  They do however allow you to see exactly who has been visiting (IP, host, browser) in real-time.  This information is exceedingly useful when used in conjunction with VSF Simple Block and Bad Behavior to reduce nuisance visits.  If you want full graphical stats though, use Googles Analytics.  This is not designed to be a replacement for GA.

== Installation ==

1. Download and extract it
2. Copy vsf-simple-stats folder to the "/wp-content/plugins/" directory
3. Activate the plugin through the 'Plugins' menu in WordPress
4. Done

== Frequently Asked Questions ==

= Do I have to configure something? =

You don't have to, but you can exclude ips from the admin page (VSF Simple Stats).

= How do I exclude a range of IP addresses? =

Go to the admin page, VSF Simple Stats and in the "IP From" box, put the start of the ip range, E.g. 207.46.0.0
In the second box, "IP to", put the end of the ip range, E.g. 207.46.255.255
In the third box, "Description", you can put a description of the ip range, E.g. Microsoft

= How do I exclude a single IP address? =

Simply follow the instructions above for excluding a range of ips, but don't fill in the "IP to" box.
E.g. "IP from" 1.2.3.4, "IP to" [blank] and "Description" Me.

= I'm getting an error during export / import =

Go to the plugin website and post a comment on the simple stats page with as much information as possible please.

== Screenshots ==

1. Image of the show stats page
2. Image of the show stats page
3. Image of the admin menu page allowing for ips to be added which will be ignored when producing stats.

== Changelog ==

= 0.8 =
Added in ability to add custom search query parameters in to the admin page.  This means that if there are visitors coming from a search engine that isn't known, like the sites own, it can be added manually.

= 0.7 =
Add in serach terms box on the stats page for the main search engines.  Might upgrade this in future to allow custom search param to be added to a separate table so as to extend this functionality for search engines that use something other than p or q to indicate search terms.

= 0.6 =
Re-design of the interface on the admin and stats pages.  Database table upgrade so the tables are used more efficiently.

= 0.5 = 
Added import and export feature of the addresses in the filter table.  Since I have multiple installs and use this plugin everywhere, I need a facility for importing and exporting the list of filtered IP ranges.

= 0.4 =
All strings are now ready for internationalisation.

= 0.3 = 
Add reset button for stats to the admin page and to the stats page.  Tidy up code and change the admin layout slightly.  Improve checking of the description field.

= 0.2.1 = 
Made the plugin activation better by adding an activation hook instead of checking installation everywhere.  Should improve performance (slightly) and improve first activation / installation

= 0.2 =
Small modification to the name to comply with wordpress.org

= 0.1 =
First and stable version.