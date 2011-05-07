<?php 
 class EventorQueryWidget extends WP_Widget {  
    function EventorQueryWidget() {  
        parent::WP_Widget(false, 'Eventor Query');  
    }  

    // Lay out the widget config form
    function form($instance) 
    {  
        $title = esc_attr($instance['title']); 
        $query =  esc_attr($instance['query']);
?>  
        <p><label for="<?php echo $this->get_field_id('title'); ?>"><?php _e('Title:'); ?> <input class="widefat" id="<?php echo $this->get_field_id('title'); ?>" name="<?php echo $this->get_field_name('title'); ?>" type="text" value="<?php echo $title; ?>" /></label></p>  
        
        <p>
        	<label for="<?php echo $this->get_field_id('query'); ?>"><?php _e('Query:'); ?>
        		<input class="widefat" id="<?php echo $this->get_field_id('query'); ?>" name="<?php echo $this->get_field_name('query'); ?>" type="text" value="<?php echo $query; ?>" />
        	</label>
       	</p>        
<?php  
    }  
   
	function update($new_instance, $old_instance) 
	{  
        // processes widget options to be saved  
        return $new_instance;  
	}  
	    
	// Emit widget html
	function widget($args, $instance) 
	{  		
		// TODO: Hide surrounding html based on widget new config setting 'hide_wordpress_widget_html'.
		$args['title'] = $instance['title']; 
		echo $args['before_widget'] . $args['before_title'] . $args['title'] . $args['after_title'];	
		
		// Instantiate query object dynamically from widget config.
		$queryType = $instance['query'];		
		$query = new $queryType();
		
		// provide widget_id for separate caching.
		$query->loadWithCacheKey($args['widget_id']);
		
		echo $query->getHtml();
		        
		echo $args['after_widget']; 
    }  
}  
?>