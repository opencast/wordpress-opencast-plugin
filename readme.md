# Wordpress Opencast Plugin

## Description
Opencast is an open-source video-management system, mostly used in higher education. It has a wide variety of learning management
integration (Moodle, Ilias, Stud.IP, LTI), includes a browser-based recording tool (Opencast Studio) and supports automated recordings 
in classrooms. The Opencast player supports the playback of multiple synchronous video-streams and features several other unique 
features, like a zoom into the video. 

The purpose of the Worpress-Opencast-Plugin is an easy to use, secure and reliable integration of Opencast into Wordpress for administrators, 
authors and viewers. The plugin features the ability to upload videos to Opencast, create videos with Opencast Studio and easily inlude 
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

## Usage

### Shortcodes

## Installation and Configuration


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

