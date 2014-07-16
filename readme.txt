=== tf Song List ===
Contributors: ipm-frommen
Donate link: http://ipm-frommen.de/wordpress
Tags: song list, songlist, songs, repertoire, bands, musicians, performers, music
Requires at least: 3.0
Tested up to: 3.9.1
Stable tag: trunk
License: GPLv2
License URI: http://www.gnu.org/licenses/gpl-2.0.html

tf Song List is an easy-to-use song listing plugin for bands and solo musicians.

== Description ==

**tf Song List is an easy-to-use song listing plugin for bands and solo musicians.**  
Conveniently manage your song list directly from within the WordPress backend, make use of the nifty CSV import/export functionality, and style the actual output entirely to your liking.

Take a look at the <a href="http://ipm-frommen.de/wordpress/tf-song-list/live-example" target="_blank">live example</a>, read through the <a href="http://ipm-frommen.de/wordpress/tf-song-list#documentation" target="_blank">documentation</a>, or just <a href="http://downloads.wordpress.org/plugin/tf-song-list.zip">download</a> the plugin right now.

= Features =

**Manage Songs**

* add new songs to the song list
* edit/delete existing songs
* delete the entire song list
* set song status to public (listed both on frontend and backend) or private (listed on backend only)
* filter the song list by the song status (all songs, public songs, private songs)

**Import/Export**

* import songs from a CSV file and insert them into the current song list (duplicates will be detected)
* import songs from a CSV file and replace the current song list with the import
* export the entire song list as a CSV file
* export only the public songs as a CSV file

**Settings**

* provide an optional introduction to the song list (some HTML tags allowed)
* order the song list by artist or title
* choose the column order "artist | title” or “title | artist”
* define custom names for the artist and title columns
* show/hide column names (i.e., table head)

**Styling**

* make use of the integrated class names and IDs
* copy the frontend stylesheet to your template directory and thus be independent of any plugin updates

= Translations =

The plugin originally comes with English, German and Spanish language.

If you would like to provide a translation for a currently not included language, please go ahead and do that! I would highly appreciate it, and include the file in the next update.

You may either download and work with the current POT file that is located in the `languages` folder, or use a plugin (e.g., <a href="http://wordpress.org/extend/plugins/codestyling-localization/" target="_blank">Codestyling Localization</a>) to read the relevant text portions from the plugin files. Then send me the respective <a href="mailto:tf@ipm-frommen.de?subject=[tf Song List] Translation">PO/MO file via e-mail</a>.

== Installation ==

1. Upload the `tf-song-list` folder to the `/wp-content/plugins/` directory on your web server.
2. Activate the plugin through the _Plugins_ menu in WordPress.
3. To display the song list, simply put `[tf_song_list]` in the content of your desired page.

== Frequently Asked Questions ==

 &rarr; <a href="http://ipm-frommen.de/wordpress/tf-song-list#faq" target="_blank">Frequently Asked Questions</a>.

== Screenshots ==

1. **Songs page** - Here you can view the song list, add new songs, edit or delete existing ones, or even delete the entire song list itself.
2. **Settings page** - Here you can customize the introduction to the actual song list, the names for the _Artist_ and _Title_ columns, the sorting and the like.
3. **Import/Export page** - Here you can import songs from a CSV file, or export your current list to a CSV file.

== Changelog ==

= 1.1.0 =

* Compatible up to WordPress 3.9.1
* Add Spanish translations (thanks to the guys over at WebHostingHub, especially Jelena Kovacevic and Andrew Kurtis)
* Move `/css` folder into new `/assets` folder
* Remove `index.php` files
* Clean up

= 1.0.9 =

* Added `stripslashes_deep` to `artist` and `title` when inserting/updating a song _by hand_.

= 1.0.8 =

* Restricted `add_cap` and `remove_cap` functions to editable roles only.

= 1.0.7 =

* Added song list wrapper element and according `#tfsl-wrapper` CSS.
* For actions, filters and the like, replaced class objects by class names.
* For function calls of current class, replaced `$this` by `self::`.

= 1.0.6 =

* Added Settings and Documentation links to plugin description on Installed Plugins page.
* Updated translations.

= 1.0.5 =

* Changed `the-tfsl-list` class to `tfsl-the-list`.
* Adapted `tf-song-list.css` to new `tfsl-the-list` class.
* Added version number to stylesheets and script files.
* Renamed script files from `*-scripts.js` to `*-functions.js`.
* Removed link to external POT file from readme. The file comes with the plugin, anyway.

= 1.0.4 =

* Changed text domain from `tfsl` to `tf-song-list`.
* Added German language files.

= 1.0.3 =

* Encoding for CSV import/export explicitly set to UTF-8.
* Fixed output formatting of song list data.

= 1.0.2 =

* Fixed `dbDelta` function call in `db.php`, again.

= 1.0.1 =

* Fixed `dbDelta` function call in `db.php`.

= 1.0.0 =

* Initial release.
