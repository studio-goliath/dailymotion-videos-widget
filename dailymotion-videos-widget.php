<?php
/*
 * Plugin Name: Dailymotion Videos Widget
 * Plugin URI: https://github.com/studio-goliath/dailymotion-videos-widget
 * Description: Add widget to show your latest dailymotion videos
 * Version: 0.1
 * Author: Studio Goliath
 * Author URI: http://www.studio-goliath.fr
 * License: GPL2
 *
 * Text Domain: dailymotion-videos-widget
 * Domain Path: /languages
 *
 */


class Widget_Dailymotion_Videos extends WP_Widget {


    private $thumb_size = array(
            'thumbnail_60_url'  => '60',
            'thumbnail_120_url' => '120',
            'thumbnail_180_url' => '180',
            'thumbnail_240_url' => '240',
            'thumbnail_360_url' => '360',
            'thumbnail_480_url' => '480',
            'thumbnail_720_url' => '720',
        );

    /**
     * Register widget with WordPress.
     */
    public function __construct() {

        parent::__construct(
                        'dvw_dailymotion_widget',
                        'Dailymotion Videos Widget', // Name
                        array(
                            'description' => __('Show your latest dailymotion video', 'dailymotion-videos-widget')
                        )
        );

    }


    /**
     * Front-end display of widget.
     *
     * @see WP_Widget::widget()
     *
     * @param array $args     Widget arguments.
     * @param array $instance Saved values from database.
     */
    public function widget($args, $instance) {


        $instance = wp_parse_args(
            $instance,
            array(
                    'title'             => '',
                    'user_id'           => false,
                    'nb_limit_video'    => 2,
                    'thumb_size'             => 'thumbnail_360_url',
                ));


        if( !empty( $instance['user_id'] ) ){

            $videos = get_transient( 'dvw_' . $this->id );
            $thumbnail_size = $instance['thumb_size'];

            // Ignore transient on preview mode
            if( ! $videos || ( method_exists('WP_Widget', 'is_preview' ) && $this->is_preview() ) ){
                // Dailymotion api call
                $user_id = esc_attr( $instance['user_id'] );

                $dailymotion_api_url = "https://api.dailymotion.com/user/$user_id/videos?limit={$instance['nb_limit_video']}&fields=id,allow_embed,embed_url,title,{$thumbnail_size}";

                $dailymotion_response = wp_remote_get( $dailymotion_api_url );

                if( ! is_wp_error( $dailymotion_response ) ){

                    $videos = wp_remote_retrieve_body( $dailymotion_response );

                    // Don't set transient on preview mode
                    if( ! method_exists('WP_Widget', 'is_preview' ) || ! $this->is_preview() ){

                        set_transient( 'dvw_' . $this->id, $videos, HOUR_IN_SECONDS );
                    }
                }

            }



            if( $videos ){

                $videos = json_decode( $videos );

                if( ! empty( $videos->list) ){

                    add_thickbox();

                    $title = apply_filters('widget_title', $instance['title']);

                    echo $args['before_widget'];

                    if ( !empty( $title ) ){
                        echo $args['before_title'] . $title . $args['after_title'];
                    }

                    foreach ($videos->list as $video) {

                        echo '<ul class="dvw_videos_list">';

                        if( $video->allow_embed ){

                            $thumbnail_src = $video->{$thumbnail_size};

                            echo "<a href='{$video->embed_url}?TB_iframe=true' class='thickbox'>";
                            echo "<img src='$thumbnail_src' height='{$this->thumb_size[$thumbnail_size]}'/>";
                            echo "<h3>{$video->title}</h3>";
                            echo '</a>';

                        }

                        echo '</ul>';

                    }

                    echo $args['after_widget'];
                }

            }

        }

    }

    /**
     * Sanitize widget form values as they are saved.
     *
     * @see WP_Widget::update()
     *
     * @param array $new_instance Values just sent to be saved.
     * @param array $old_instance Previously saved values from database.
     *
     * @return array Updated safe values to be saved.
     */
    public function update($new_instance, $old_instance) {

        $instance = $old_instance;

        $instance['title'] = sanitize_text_field($new_instance['title']);

        $instance['user_id'] = sanitize_text_field($new_instance['user_id']);

        $instance['thumb_size'] = sanitize_text_field($new_instance['thumb_size']);

        $instance['nb_limit_video'] = intval($new_instance['nb_limit_video']);

        delete_transient( 'dvw_' . $this->id );

        return $instance;
    }

    /**
     * Back-end widget form.
     *
     * @see WP_Widget::form()
     *
     * @param array $instance Previously saved values from database.
     */
    public function form($instance) {

        ?>
        <p>
            <label for="<?php echo $this->get_field_id('title'); ?>"><?php _e('Title'); ?> :</label>
            <input class="widefat" id="<?php echo $this->get_field_id('title'); ?>" name="<?php echo $this->get_field_name('title'); ?>" type="text" value="<?php if( isset( $instance['title'] ) ){ echo $instance['title'] ;} ?>" />
        </p>

        <p>
            <label for="<?php echo $this->get_field_id('user_id'); ?>"><?php _e('Dailymotion id', 'dailymotion-videos-widget') ?> :</label>
            <input class="widefat" id="<?php echo $this->get_field_id('user_id'); ?>" name="<?php echo $this->get_field_name('user_id'); ?>" type="text" value="<?php if( isset($instance['user_id']) ){ echo $instance['user_id'];} ?>" />
        </p>

        <p>
            <label for="<?php echo $this->get_field_id('nb_limit_video'); ?>"><?php _e('Number of videos', 'dailymotion-videos-widget') ?> :</label>
            <input class="widefat" id="<?php echo $this->get_field_id('nb_limit_video'); ?>" name="<?php echo $this->get_field_name('nb_limit_video'); ?>" type="number" value="<?php if( isset($instance['nb_limit_video']) ){ echo $instance['nb_limit_video'];} ?>" />
        </p>

        <p>
            <label for="<?php echo $this->get_field_id('thumb_size'); ?>"><?php _e('Thumbnail height', 'dailymotion-videos-widget') ?> :</label>
            <select id="<?php echo $this->get_field_id('thumb_size'); ?>" name="<?php echo $this->get_field_name('thumb_size'); ?>">
                <?php
                foreach ($this->thumb_size as $size => $size_label ) {

                    $selected = selected( $instance['thumb_size'], $size, false );

                    echo "<option value='$size' $selected>$size_label</option>";
                }
                ?>
            </select>
        </p>

        <?php
    }

}
add_action('widgets_init', create_function('', 'register_widget( "Widget_Dailymotion_Videos" );'));