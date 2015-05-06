=== Plugin Name ===
Contributors: arnaudban, studio-goliath
Tags: dailymotion, widget, video
Requires at least: 3.5
Tested up to: 4.2.1
Stable tag: trunk
License: GPLv2
License URI: http://www.gnu.org/licenses/gpl-2.0.html

Add widget to show your latest dailymotion videos

== Description ==

Add widget to show your latest dailymotion videos

== Frequently Asked Questions ==

= Can it be customisable ? =

Yes there are somes filters that can help you get the output that you want :

the **bvw_dailymotion_video_fields** filter, that let you filter the video fields to get from dailymotion
Possible fields can be found here : https://developer.dailymotion.com/documentation#video-fields

example :
`
function bvw_dailymotion_video_fields( $fields ){

    array_push($fields, 'duration_formatted');

    return $fields;
}
add_filter( 'bvw_dailymotion_video_fields', 'bvw_dailymotion_video_fields' );
`

the **bvw_dailymotion_video_link** filter, that let you filter the ouput form each video

example :
`
function bvw_dailymotion_video_link( $output, $video, $widget_instance ){

    $thumbnail_size = $widget_instance['thumb_size'];
    $thumbnail_src = $video->{$thumbnail_size};


    $output = "<a href='{$video->embed_url}?TB_iframe=true' class='thickbox'>";
    $output .= "<img src='$thumbnail_src' />";
    $output .= "<h3>{$video->title} {$video->duration_formatted}</h3>";
    $output .= '</a>';

    return $output;
}
add_filter( 'bvw_dailymotion_video_link', 'bvw_dailymotion_video_link', 10, 3 );



== Screenshots ==

1. Customize view of the widget
2. Admin view of the widget
3. How the widget display with the theme TwentyFifteen

== Changelog ==

= 0.1 =
* First version of the plugin