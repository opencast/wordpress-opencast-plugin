# Wordpress Opencast Plugin

## Description
Opencast is an open-source video-management system, mostly used in higher education. It has a wide variety of learning management
integration (Moodle, Ilias, Stud.IP, LTI), includes a browser-based recording tool (Opencast Studio) and supports automated recordings 
in classrooms. The Opencast player supports the playback of multiple synchronous video-streams and features several other unique 
features, like a zoom into the video. 

The purpose of the Worpress-Opencast-Plugin is an easy to use, secure and reliable integration of Opencast into Wordpress for administrators, 
authors and viewers. The plugin features the ability to upload videos to Opencast, create videos with Opencast Studio and easily include 
the Opencast Player and restrict access to the recordings based on Worpress roles.

You can find more information on Opencast here:
- [Opencast Website](https://opencast.org/)
- [Opencast Documantation](https://docs.opencast.org/)
- [Opencast Github](https://github.com/opencast/opencast)
- [Opencast demo server](https://stable.opencast.org/)

There is currently no public Wordpress demo instance with the Opencast-plugin installed available. 

## Features
The current version of the Opencast plugin offers these features:
- Robust upload videos to Opencast with a minimal set of metadata. The upload can be restricted to selected Wordpress roles.
- Video creation with Opencast Studio, a browser-based multi-stream recording tool. Opencast Studio can be restricted to 
selected Wordpress roles.
- Video playback with Opencast Paella Player that limits the access to videos based on Wordpress roles.
- List of all recordings available. 
- Easy picking of recording in an placeholder in the Wordpress Editor. 

### Current Limitations
This version has currently some limitations:
- Only one Opencast series is used for all videos within the Worpress-plugin.
- Only single stream upload dialogue (although Opencast Studio records multi-stream).
- For the configuration IDs have to be copied, instead of input fields that read these from the Opencast APIs

## Installation and Configuration
It is highly recommended to install the latest stable version of the plugin from WordPress Plugins Marketplace. Otherwise, downloading/cloning the plugin from its Github repo requires some extra commands:
- composer install
- npm install
- npm run prod


In order to configure the plugin, it is good to know the General Setting requirements which can be found in the admin panel.
### General
#### API Setting
Opencast Series ID, Opencast API url, Username for API calls, Password for API user, Consumer Key and Consumer Secret are the required parameters to connect and communicate with Opencast Server. Failure to provide valid parameters can result in a whole or partly misfunction errors!

There is also recommended to provide Connection timeout in seconds which has a default of 1 second. In this section there is another parameter which helps to manage who can be consider as LTI Instructor by simply assigning the role of the user provided by WordPress.

#### Upload Video Settings
The Opencast Video Upload Box can be managed through this section. Access Permissions is needed when the checkbox is set and must have WordPress users' roles. Workflow as well as Max. Upload Size in (MB) can also be set here.

#### Studio Setting
This section is used to manage Opencast Studio Button. Access Permissions will be applied when the checkbox is set and WordPress Users' roles are selected.
#### Episodes Settings
In this section administrators are able to manage the Opencast Video list. It is possible to provide different Endpoint and Series ID, in case there is a separate Opencast display server. Pagination as well as Access Permission on User roles can be also applied here.

#### Single Episode Settings
Each Opencast Single Video display can be managed separately in a table view, in the action column Edit and Delete (shown in icons respectively) help admins to do so. Opencast Episode ID (video ID) and css style class as well as Access Permission can be set in Edit pop-up window.

### Video Manager
Upon entering server credentials, a list of available videos is represented in this section as a table view in which administrators are able to delete selected videos only (version 1.0).
## Usage
Apart from admin panel the plugin is intended to provide the usage and display using Shortcodes.
### Shortcodes
There are 5 different Shortcodes provided for this Plugin:

#### [ opencast-episodes id='' name='' class='' ]
This Shortcode is used to display the Opencast Video List, and it accepts 3 Attributes:
- id: the id of the element by default is 'oc-episodes'
- name: the name of the element by default is 'oc-episodes'
- class: the class name of the element by default is 'opencast-episodes-container'. When a custom class is set the default css stylesheet won't be loaded.

#### [ opencast-episode-single wp_id='{required}' oc_id='' class='' ]
This Shortcode is used to display a single Opencast episode video. It provides a list of videos at first to search and select. It accepts 3 Attributes:
- wp_id: This is required at first and if the wp_id already exists, it will reuse and display the existing one. Fail to provide this attribute will result in error message.
- oc_id: It is an optional attribute, if it is not set the box will show you a mini list of all available video to search and select a video.
- class: it is used to provide custom class, if set the default stylesheet won't be loaded.

#### [ opencast-episode-single-public oc_id='{required}' class='' ]
It can be used for a single public video which has been made public specifically in Opencast.
- oc_id: it is a required attribute and must contain the episode id of a public video.
- class: for custom class name.
##### Version 1.0.1: [opencast-episode-single-public oc_url="{valid_url}"] 
 - oc_url: it is the valid video Player-URL, in case the URL is set, oc_id is no more required and has no effect! 
#### [ opencast-studio-button title='' class='' ]
This Shortcode makes a link button which redirects users to Opencast Studio. It accepts 2 Attributes:
- title: this attribute will replace the text of the link button.
- class: used to apply custom class.
#### [ opencast-upload-button text='' btn_text='' type='' success_text='' fail_text='' class='' ]
By using this Shortcode, users can upload their videos via a Upload Box which gets the Author name and a Title and an uploaded video, then sends it to Opencast server directly. It accepts 6 attributes:
- text: it is the text displayed in the dropzone area. By default it shows "Drop your video file here"
- btn_text: the text of the button, by default it shows "Upload Video To Opencast"
- type: it is the Opencast Video flavor which must be (presenter or presentation) , by default it considers the video as "presenter"
- success_text: a text message displayed when upload is successful, by default "Uploaded"
- fail_text: a text message displayed when the upload is unsuccessful, by default "Failed"
- class: used to apply custom class on the upload box.

## Change Log
### Version 1.0.1
Enhancement: possibily to enter an opencast public video url as an attribute to the public single video shortcode:
[opencast-episode-single-public oc_url="{valid_url}"] 
in this case admin are able to show a public video only with its player-url

#### Important to know:
Except [ opencast-episode-single-public ] Shortcode, others will apply the Access Permissions if it is enabled. The users who do not get the permission to access will see nothing.
 


## Plugin Information

- Plugin Name: Opencast Plugin
- Plugin URI: https://github.com/opencast/wordpress-opencast-plugin
- Description: Opencast video management system for automated video capture, management, and distribution at scale in wordpress.
- Version: 1.0.0
- Author: Farbod Zamani Boroujeni
- Author Email: zamani@elan-ev.de
- Author URI: elan-ev.de
- Organisation: ELAN e.V
- Licence: GPLv2 or later
- Text Domain: elan-ev.de

