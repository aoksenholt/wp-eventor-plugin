<?php  
 class Eventor_Widget_ClubDeadlines extends WP_Widget {  
    function Eventor_Widget_ClubDeadlines() {  
        parent::WP_Widget(false, 'Eventor Club Deadlines');  
    }  
function form($instance) {  
        $title = esc_attr($instance['title']);  
?>  
        <p><label for="<?php echo $this->get_field_id('title'); ?>"><?php _e('Title:'); ?> <input class="widefat" id="<?php echo $this->get_field_id('title'); ?>" name="<?php echo $this->get_field_name('title'); ?>" type="text" value="<?php echo $title; ?>" /></label></p>  
<?php  
    }  
   

function update($new_instance, $old_instance) {  
        // processes widget options to be saved  
        return $new_instance;  
    }  
function widget($args, $instance) {  
        $args['title'] = $instance['title']; 
		echo $args['before_widget'] . $args['before_title'] . $args['title'] . $args['after_title'];		
        getActivities();
		echo $args['after_widget']; 
    }  
}  
 
?>