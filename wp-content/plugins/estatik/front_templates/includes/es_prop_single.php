<?php 
$es_settings = es_front_settings();
$upload_dir = wp_upload_dir();
ob_start();

global $wpdb;
$sql = $wpdb->prepare("SELECT * FROM {$wpdb->prefix}estatik_properties 
    WHERE prop_id = '%d' ORDER BY prop_id desc", get_the_id());
$es_prop_single = $wpdb->get_row($sql); 

$prop_cat = $wpdb->get_row( "SELECT cat_title FROM {$wpdb->prefix}estatik_manager_categories 
    WHERE cat_id = '{$es_prop_single->prop_category}'");

$prop_rent = $wpdb->get_row( "SELECT period_title FROM {$wpdb->prefix}estatik_manager_rent_period 
    WHERE period_id = '{$es_prop_single->prop_period}'");
 
$prop_type = $wpdb->get_row( "SELECT type_title FROM {$wpdb->prefix}estatik_manager_types 
    WHERE type_id = '{$es_prop_single->prop_type}'");

$prop_status = $wpdb->get_row( "SELECT status_title FROM {$wpdb->prefix}estatik_manager_status
    WHERE status_id = '{$es_prop_single->prop_status}'");

// $currency_sign_ex = explode(",", $es_settings->default_currency);
// if ( count($currency_sign_ex) == 1 ) {
//     $currency_sign = $currency_sign_ex[0];
// } else {
//     $currency_sign = $currency_sign_ex[1];  
// }
// $price_format = explode("|",$es_settings->price_format);
// $price = number_format($es_prop_single->prop_price, 
//                                        $price_format[0], $price_format[1], $price_format[2]);
// $price_with_currency = $es_settings->currency_sign_place == 'before' ? $currency_sign.$price : $price.$currency_sign;


$es_prop_neigh = es_join("b.neigh_title,a.neigh_distance", 
                         "estatik_properties_neighboarhood a", 
                         "estatik_manager_neighboarhood b",
                         "b.neigh_id = a.neigh_id and a.prop_id={$es_prop_single->prop_id}");

$es_prop_features = es_join("b.feature_title",
                            "estatik_properties_features a",
                            "estatik_manager_features b",
                            "b.feature_id = a.feature_id and a.prop_id={$es_prop_single->prop_id}");

$es_prop_appliances = es_join("b.appliance_title",
                              "estatik_properties_appliances a",
                              "estatik_manager_appliances b",
                              "b.appliance_id = a.appliance_id and a.prop_id={$es_prop_single->prop_id}");

$video_sql = $wpdb->prepare("SELECT prop_meta_value FROM {$wpdb->prefix}estatik_properties_meta 
                WHERE prop_id = '%d' AND prop_meta_key = 'video'", $es_prop_single->prop_id);
$prop_video = $wpdb->get_row($video_sql);

$dimension_sql = "SELECT dimension_title FROM {$wpdb->prefix}estatik_manager_dimension WHERE dimension_status = 1";
$es_dimension = $wpdb->get_row($dimension_sql);
$es_dimension = empty($es_dimension) ? '' : $es_dimension->dimension_title;

$agent_sql = $wpdb->prepare("SELECT * FROM {$wpdb->prefix}estatik_agents WHERE agent_id='%d'", 
    $es_prop_single->agent_id);
$prop_agent = $wpdb->get_row($agent_sql);


$image_sql = $wpdb->prepare("SELECT prop_meta_value FROM {$wpdb->prefix}estatik_properties_meta 
    WHERE prop_id = '%d' AND prop_meta_key = 'images'", $es_prop_single->prop_id);
$uploaded_images = $wpdb->get_row($image_sql);
if ( !empty($uploaded_images) ) {
    $upload_image_data = unserialize($uploaded_images->prop_meta_value);
}

$post_id = $es_prop_single->prop_id;
$queried_post = get_post($post_id); 

$permalink = get_post_permalink($post_id);
$pdf_url = add_query_arg( array( 'pdf' => "$es_prop_single->prop_id" ), $permalink );                        
 
$meta_sql = $wpdb->prepare("SELECT prop_meta_value FROM {$wpdb->prefix}estatik_properties_meta 
    WHERE prop_id='%d' AND prop_meta_key = 'prop_custom_field'", $es_prop_single->prop_id);
$prop_meta = $wpdb->get_row($meta_sql);
$meta_value = empty($prop_meta) ? array() : unserialize($prop_meta->prop_meta_value);

?>
	 
<div id="es_content" class="clearfix <?php 
    if ( $es_settings->single_property_layout=='3' ) { 
        echo 'es_single_left'; 
    } else if ( $es_settings->single_property_layout == '2' ) { 
        echo 'es_single_right'; 
    } else { 
        echo 'es_single_center'; 
    }  ?>">
    <div class="es_single_in">
        <div class="es_prop_single_head clearfix">
            <h1>
                <?php echo $es_settings->title == 1 ? $es_prop_single->prop_title : $es_prop_single->prop_address ?>
            </h1>

            <?php if ( $es_settings->price == 1 ) { ?>
            	<strong><?php echo get_price($es_prop_single->prop_price) ?></strong>
            <?php } ?>
			<?php if(!empty($prop_cat) && $prop_cat->cat_title!=""){ ?>
            	<span><?php echo $prop_cat->cat_title?></span>
            <?php } ?>
        </div>
        
        <div class="es_prop_single_tabs_outer"> 
            <div class="es_prop_single_tabs clearfix">
                <div class="es_prop_single_tabs_in clearfix">
                    <ul>
                        <li>
                            <a class="active" href="#es_single_basic_facts">
                                <?php _e("Basic facts", 'es-plugin'); ?>
                            </a>
                        </li>
                        <?php if($es_prop_single->prop_latitude!='' || !empty($es_prop_neigh)) { ?>
                        	<li><a href="#es_single_neigh"><?php _e("Neighborhood", 'es-plugin'); ?></a></li>
                        <?php } ?>
                        <?php if(!empty($es_prop_features) || !empty($es_prop_appliances)){ ?>
                        	<li><a href="#es_single_features"><?php _e("Features", 'es-plugin'); ?></a></li>
                        <?php } ?>
                        <?php if(!empty($prop_video)) { ?>
                        	<li><a href="#es_single_video"><?php _e("Video", 'es-plugin'); ?></a></li>
                        <?php } ?>
                        <?php //if($es_settings->agent!=0 && !empty($prop_agent)) { ?>
                        	<!-- <li><a href="#es_single_contact_agent"><?php _e("Contact agent", 'es-plugin'); ?></a></li> -->
                        <?php //} ?>
                    </ul>
                </div>
            </div>
        </div>
        <div class="es_prop_single_basic_facts clearfix" id="es_single_basic_facts" style="display:block;">

            <div class="es_prop_single_basic_facts_upper clearfix">
                <?php if ( !empty($upload_image_data) ) { ?>
                
                <div id="es_prop_single_slider_outer" class="clearfix">
                	<div id="es_prop_single_slider_in">
                    <ul class="es_prop_single_pics">	
                    	<?php
							foreach ( $upload_image_data as $prop_image ) {
							
								$single_right_image_name = explode("/", $prop_image);
								$single_right_image_name = end($single_right_image_name);
								$single_right_image_path = str_replace($single_right_image_name, "", $prop_image);
								
								$image_url_full = $single_right_image_path.$single_right_image_name;
								
								$image_url = $single_right_image_path.'single_lr_'.$single_right_image_name;
								$img_dimensions = 'width:'.$es_settings->prop_singleview_photo_lr_width.'px;';	
							 	
						?>
						<li>
                            <a href="<?php echo $upload_dir['baseurl'].$image_url_full?>">
                                <img style="<?php echo $img_dimensions?>" 
                                     src="<?php echo $upload_dir['baseurl'].$image_url?>" alt="" />
                             </a>
                         </li>
                            
						<?php } ?>
						
                	</ul>
                    <div id="es_prop_single_pager_outer">
                        <ul class="es_prop_single_pager">	
                            <?php 
							foreach($upload_image_data as $prop_image) {
								
								$list_image_name = explode("/", $prop_image);
								$list_image_name = end($list_image_name);
								$list_image_path = str_replace($list_image_name, "", $prop_image);
								$list_image_url = $list_image_path.'single_thumb_'.$list_image_name;
								
							?>
                                <li>
                                    <a data-slide-index="" href="">
                                        <img style="width:<?php echo $es_settings->prop_singleview_photo_thumb_width?>px; height:<?php echo $es_settings->prop_singleview_photo_thumb_height?>px;" 
                                            src="<?php echo $upload_dir['baseurl'].$list_image_url?>" alt="" />
                                    </a>
                                </li>								
							<?php } ?>
                        </ul>	
                    </div>
                    </div>
                </div>
                
                <?php } ?>
                
                <div class="es_prop_single_basic_facts_right clearfix">

                    <div class="es_prop_single_social_links clearfix">
                        <?php if($es_settings->facebook_link==1) { ?>
                            <span class='st_facebook_large' displayText='Facebook'></span>
                        <?php } ?>
                        <?php if($es_settings->google_plus_link==1) { ?>
                        	<span class='st_googleplus_large' displayText='Google +'></span>
                        <?php } ?>
						<?php if($es_settings->linkedin_link==1) { ?>
                        	<span class='st_linkedin_large' displayText='LinkedIn'></span>
                        <?php } ?>
						<?php if($es_settings->twitter_link==1) { ?>
                        	<span class='st_twitter_large' displayText='Tweet'></span>  
                        <?php } ?>
						<?php if ( $es_settings->pdf_player == 1 ) { ?>
                        	<a href="<?php echo $pdf_url?>" class="pdf_player" target="_blank">a</a>
                        <?php } ?> 
                    </div>
                    <div class="es_prop_single_basic_info">
                        <ul>                           
                            <?php if ( $es_prop_single->prop_date_added != 0 ) { ?>
                            <li>
                                <strong><?php _e("Date added", 'es-plugin'); ?>:</strong>
                                <span>
                                    <?php echo date($es_settings->date_format, $es_prop_single->prop_date_added)?>
                                </span>
                            </li>
                            <?php } ?> 
                            
							<?php if ( $es_prop_single->prop_area != 0 ) { ?>
                            <li>
                                <strong><?php _e("Area size", 'es-plugin'); ?>:</strong>
                                <span><?php echo "{$es_prop_single->prop_area} $es_dimension "?></span>
                            </li>
                            <?php } ?> 
                            
                            <?php if ( $es_prop_single->prop_lotsize != 0 ) { ?>
                            <li>
                                <strong><?php _e("Lot size", 'es-plugin'); ?>:</strong>
                                <span><?php echo "$es_prop_single->prop_lotsize $es_dimension" ?></span>
                            </li>
                            <?php } ?>
                            
                            
							<?php if ( !empty($prop_cat) && $prop_cat->cat_title != "" 
                                    && strpos($prop_cat->cat_title,"rent") != "" ) { ?>
                            <li>
                                <strong><?php _e("Rent Period", 'es-plugin'); ?>:</strong>
                                <span><?php echo $prop_rent->period_title?></span>
                            </li>
                            <?php } ?>
                            
                            
                            <?php 
							if(isset($prop_type->type_title)){ ?>
                            <li>
                                <strong><?php _e("Type", 'es-plugin'); ?>:</strong>
                                <span><?php echo $prop_type->type_title?></span>
                            </li>
                            <?php } ?>
								
                            <?php
							if(isset($prop_status->status_title)){ ?>
                            <li>
                                <strong><?php _e("Status", 'es-plugin'); ?>:</strong>
                                <span><?php echo $prop_status->status_title?></span>
                            </li>
                            <?php } ?>
                            
                            <?php if($es_prop_single->prop_bedrooms!=0){ ?>
                            <li>
                                <strong><?php _e("Bedrooms", 'es-plugin'); ?>:</strong>
                                <span><?php echo $es_prop_single->prop_bedrooms?></span>
                            </li>
                            <?php } ?>
								
                            <?php if($es_prop_single->prop_bathrooms!=0){ ?>
                            <li>
                                <strong><?php _e("Bathrooms", 'es-plugin'); ?>:</strong>
                                <span><?php echo str_replace('.0', '', $es_prop_single->prop_bathrooms)?></span>
                            </li>
                            <?php } ?>
                            
                            <?php if($es_prop_single->prop_floors!=0){ ?>
                            <li>
                                <strong><?php _e("Floors", 'es-plugin'); ?>:</strong>
                                <span><?php echo $es_prop_single->prop_floors?></span>
                            </li>
                            <?php } ?>
                            
                            <?php if ( isset($es_prop_single->prop_builtin) 
                                && $es_prop_single->prop_builtin != "" ) { ?>
                            <li>
                                <strong><?php _e("Built in", 'es-plugin'); ?>:</strong>
                                <span><?php echo $es_prop_single->prop_builtin?></span>
                            </li>
                            <?php } ?>
                            
							<?php
                            foreach ( $meta_value as $key => $val ) {
                                $key_val = str_replace("'", "", $key);
                            ?>
                                <li>
                                    <strong><?php echo $key_val?>:</strong>
                                    <span><?php echo $val?></span>
                                </li>
                            <?php } ?>
                        </ul>
                    </div>
                </div>
            </div>
            
            <?php if ( isset($es_prop_single->prop_description) && $es_prop_single->prop_description != "" ) { ?>
                <div class="es_prop_single_basic_facts_desc">
                    <h3><?php _e("Description", 'es-plugin'); ?></h3>
                    <p><?php echo $es_prop_single->prop_description?></p>
                </div>
            <?php } ?>
            
        </div>
        
        <?php 
		   if ( !empty($es_prop_neigh) || $es_prop_single->prop_latitude != '' ) {	
		?>
            <div class="es_prop_single_view_map_neigh " id="es_single_neigh">
                <h3><?php _e("View on map/Neighborhood", 'es-plugin'); ?></h3>
                
                <?php if ( $es_prop_single->prop_latitude != '' && $es_prop_single->prop_longitude != '' ) { ?>
                     <div id="es_prop_single_view_map"></div>                
                <?php } ?> 
                
                <?php if(!empty($es_prop_neigh)){ ?>
                <ul>
                    <?php foreach($es_prop_neigh as $prop_neigh) { ?>
                        <li>
                            <strong><?php echo $prop_neigh->neigh_title?>:</strong>
                            <?php if ( $prop_neigh->neigh_distance !== "text/number" ) { ?>
                            	<span><?php echo $prop_neigh->neigh_distance?></span>
                            <?php } ?>
                        </li>
                    <?php } ?> 
                </ul>
                <?php } ?>
                
            </div>
        <?php } ?>
        
        <?php if ( !empty($es_prop_features) || !empty($es_prop_appliances) ) { ?>        
            <div class="es_prop_single_features  clearfix" id="es_single_features">
                <h3><?php _e("Features", 'es-plugin'); ?></h3>
                
                <?php if ( !empty($es_prop_features) ) { ?>
                    <div class="es_prop_single_features_in">
                        <label><?php _e("Features", 'es-plugin'); ?>:</label>
                        <ul>
                            <?php foreach ( $es_prop_features as $es_prop_feature ) { ?>
                                <li><?php echo $es_prop_feature->feature_title?></li>
                            <?php } ?> 
                        </ul>
                    </div>
                <?php } ?> 
                
                <?php if ( !empty($es_prop_appliances) ) { ?>
                    <div class="es_prop_single_features_in">
                        <label><?php _e("Amenities", 'es-plugin'); ?>:</label>
                        <ul>
                            <?php foreach ( $es_prop_appliances as $es_prop_appliance ) { ?>
                                <li><?php echo $es_prop_appliance->appliance_title?></li>
                            <?php } ?>
                        </ul>
                    </div>
                <?php } ?>
                
            </div>
        <?php } ?> 
        
        
        <?php if ( !empty($prop_video) ) { ?>            
            <div class="es_prop_single_video clearfix" id="es_single_video">
                <h3><?php _e("Video", 'es-plugin'); ?></h3>
                <div class="es_prop_single_video_in">
                    <?php echo stripslashes($prop_video->prop_meta_value)?>
                </div>
            </div>
        <?php } ?> 
        
    <div id="es_toTop" class="clearfix"> 
    	<a href="javascript:void(0)"><?php _e("To top", 'es-plugin'); ?></a>
    </div>
    
	<?php if($es_settings->powered_by_link==1) { ?>
        <div class="es_powred_by">
            <p><?php _e("Powered by", 'es-plugin'); ?> <a href="http://www.estatik.net" target="_blank">Estatik</a></p>
        </div>    
    <?php } ?>
    
 </div>
</div> 