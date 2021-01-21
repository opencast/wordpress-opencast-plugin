=== Opencast Plugin ===
Contributors: farbodzmn
Tags: Opencast, Videos manager
Requires at least: 4.3
Tested up to: 5.6
Stable tag: 1.0
Requires PHP: 7.0
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

The purpose of the Worpress-Opencast-Plugin is an easy to use, secure and reliable integration of Opencast into WordPress for administrators, authors and viewers. The plugin features the ability to upload videos to Opencast, create videos with Opencast Studio and easily include the Opencast Player and restrict access to the recordings based on Worpress roles.

== Description ==

Opencast is an open-source video-management system, mostly used in higher education. It has a wide variety of learning management integration (Moodle, Ilias, Stud.IP, LTI), includes a browser-based recording tool (Opencast Studio) and supports automated recordings in classrooms. The Opencast player supports the playback of multiple synchronous video-streams and features several other unique features, like a zoom into the video.

The purpose of the Worpress-Opencast-Plugin is an easy to use, secure and reliable integration of Opencast into WordPress for administrators, authors and viewers. The plugin features the ability to upload videos to Opencast, create videos with Opencast Studio and easily include the Opencast Player and restrict access to the recordings based on Worpress roles.

You can find more information on Opencast here:

- [Opencast Website](https://opencast.org/)
- [Opencast Documantation](https://docs.opencast.org/)
- [Opencast Github](https://github.com/opencast/opencast)
- [Opencast demo server](https://stable.opencast.org/)

There is currently no public WordPress demo instance with the Opencast-plugin installed available.

== Frequently Asked Questions ==

=  What is Opencast Series ID? =

It is an unique ID which Opencast provides for every event (Series).

= What is Opencast API url? =

It is the endpoint url for your Opencast server, which can hanlde the external calls to the server.

= What is the difference between Password for API user/Password for API user and Consumer Key/Consumer Secret ? =

Consumer Key and Secret are used to make LTI connections where username and passwords for API calls are general credentional to authorize the External API calls to the server.

= What is LTI Instructor Permission? =

By default there are 2 LTI user modes defined in Opencast (Instructor/Learner). Instructor has higher access permissions than Learner.
Selecting WordPress user roles  consider each user of defined roles as Instructor and it passes different LTI credentials when LTI calls is made.

= What is Workflow to start after upload? =

It is a workflow defined in opencast which will be run after a video has been uploaded.

= How can I change styles of each shortcode? = 

Simply by passing class='{your class name}' attribute. Note: when there is a custome css style name, the default styling won't apply anymore!

== Screenshots ==

1. Admin Panel - API Setting with some data!
2. Admin Panel - Upload Video Setting with some data!
3. Admin Panel - Studio Setting with some data!
4. Admin Panel - Episodes Setting with some data!
5. Admin Panel - Single Episode Setting with some data!
6. Admin Panel - Video Manager Section with some data!
7. Shortcode - [ opencast-episodes id='' name='' class='' ] Episodes List with pagination applied! 
8. Shortcode - [ opencast-upload-button text='' btn_text='' type='' success_text='' fail_text='' class='' ] Upload Button with default attributes!
9. Shortcode - [ opencast-episode-single wp_id='{required}' oc_id='' class='' ] Single Episode as well as Single Episode Public!
10. Shortcode - [ opencast-studio-button title='' class='' ] Studio Button with default Text!

== Changelog ==

= 1.0 =
* The first stable version which contains features and functions described!

== Features ==

The first version of the Opencast plugin offers these features:

* Robust upload videos to Opencast with a minimal set of metadata. The upload can be restricted to selected WordPress roles.
* Video creation with Opencast Studio, a browser-based multi-stream recording tool. Opencast Studio can be restricted to selected WordPress roles.
* Video playback with Opencast Paella Player that limits the access to videos based on WordPress roles.
* List of all recordings available.
* Easy picking of recording in an placeholder in the WordPress Editor.

== Current Limitations ==

This version has currently some limitations:

* Only one Opencast series is used for all videos within the Worpress-plugin.
* Only single stream upload dialogue (although Opencast Studio records multi-stream).
* For the configuration IDs have to be copied, instead of input fields that read these from the Opencast APIs

== Configuration ==

= API Setting =

Opencast Series ID, Opencast API url, Username for API calls, Password for API user, Consumer Key and Consumer Secret are the required parameters to connect and communicate with Opencast Server. Failure to provide valid parameters can result in a whole or partly misfunction errors!
There is also recommended to provide Connection timeout in seconds which has a default of 1 second. In this section there is another parameter which helps to manage who can be consider as LTI Instructor by simply assigning the role of the user provided by WordPress.

= Upload Video Settings = 

The Opencast Video Upload Box can be managed through this section. Access Permissions is needed when the checkbox is set and must have WordPress users' roles. Workflow as well as Max. Upload Size in (MB) can also be set here.

= Studio Setting =

This section is used to manage Opencast Studio Button. Access Permissions will be applied when the checkbox is set and WordPress Users' roles are selected.

= Episodes Settings =

In this section administrators are able to manage the Opencast Video list. It is possible to provide different Endpoint and Series ID, in case there is a separate Opencast display server. Pagination as well as Access Permission on User roles can be also applied here.

= Single Episode Settings =

Each Opencast Single Video display can be managed separately in a table view, in the action column Edit and Delete (shown in icons respectively) help admins to do so. Opencast Episode ID (video ID) and css style class as well as Access Permission can be set in Edit pop-up window.

= Video Manager =

Upon entering server credentials, a list of available videos is represented in this section as a table view in which administrators are able to delete selected videos only (version 1.0).

== Shortcodes ==

There are 5 different Shortcodes provided for this Plugin:

= [ opencast-episodes id='' name='' class='' ] =

This Shortcode is used to display the Opencast Video List, and it accepts 3 Attributes:

* id: the id of the element by default is 'oc-episodes'
* name: the name of the element by default is 'oc-episodes'
* class: the class name of the element by default is 'opencast-episodes-container'. When a custom class is set the default css stylesheet won't be loaded.

= [ opencast-episode-single wp_id='{required}' oc_id='' class='' ] =

This Shortcode is used to display a single Opencast episode video. It provides a list of videos at first to search and select. It accepts 3 Attributes:

* wp_id: This is required at first and if the wp_id already exists, it will reuse and display the existing one. Fail to provide this attribute will result in error message.
* oc_id: It is an optional attribute, if it is not set the box will show you a mini list of all available video to search and select a video.
* class: it is used to provide custom class, if set the default stylesheet won't be loaded.

= [ opencast-episode-single-public oc_id='{required}' class='' ] =

It can be used for a single public video which has been made public specifically in Opencast.

* oc_id: it is a required attribute and must contain the episode id of a public video.
* class: for custom class name.

= [ opencast-studio-button title='' class='' ] =

This Shortcode makes a link button which redirects users to Opencast Studio. It accepts 2 Attributes:

* title: this attribute will replace the text of the link button.
* class: used to apply custom class.

= [ opencast-upload-button text='' btn_text='' type='' success_text='' fail_text='' class='' ] =

By using this Shortcode, users can upload their videos via a Upload Box which gets the Author name and a Title and an uploaded video, then sends it to Opencast server directly. It accepts 6 attributes:

* text: it is the text displayed in the dropzone area. By default it shows "Drop your video file here"
* btn_text: the text of the button, by default it shows "Upload Video To Opencast"
* type: it is the Opencast Video flavor which must be (presenter or presentation) , by default it considers the video as "presenter"
* success_text: a text message displayed when upload is successful, by default "Uploaded"
* fail_text: a text message displayed when the upload is unsuccessful, by default "Failed"
* class: used to apply custom class on the upload box.

= Important to know: =
Except [ opencast-episode-single-public ] Shortcode, others will apply the Access Permissions if it is enabled. The users who do not get the permission to access will see nothing.

== Upgrade Notice ==

There is nothing to be noticed yet!