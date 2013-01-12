<?php
	/**
	 * @package Category_Filter
	 * @version 0.1
	 */
	/*
		Plugin Name: Category Filter
		Plugin URI: http://www.julianxhokaxhiu.com
		Description: This plugin will filter your items by category
		Version: 0.1
		Author: Julian Xhokaxhiu
		Author URI: http://www.julianxhokaxhiu.com
		License: GPLv3
	*/
	function get_elements($elements = true) {
		global $wpdb;
		$json = array();
		if($elements){
			$objs = $wpdb->get_results
			("
				SELECT DISTINCT r1.object_ID as obj_id,terms1.term_id as cat_id
				FROM
					wp_posts as p1
					LEFT JOIN wp_term_relationships as r1 ON p1.ID = r1.object_ID
					LEFT JOIN wp_term_taxonomy as t1 ON r1.term_taxonomy_id = t1.term_taxonomy_id 
					LEFT JOIN wp_terms as terms1 ON t1.term_id = terms1.term_id
					LEFT JOIN wp_terms as termsp ON t1.parent = termsp.term_id
				WHERE
					t1.taxonomy = 'category' AND p1.post_status = 'publish'
				ORDER by obj_id,cat_id
			");
			$lastid = 0;
			$count = 0;
			$temparr = array();
			foreach($objs as $obj){
				if($lastid==$obj->obj_id)$temparr[$count] = $obj->cat_id;
				else{
					if($lastid==0)$temparr[$count] = $obj->cat_id;
					else{
						$json[$lastid] = $temparr;
						$count = 0;
						$temparr = array();
						$temparr[$count] = $obj->cat_id;
					}
					$lastid = $obj->obj_id;
				}
				$count++;
			}
			$json[$lastid] = $temparr;
		}else{
			$cats = $wpdb->get_results
			("
				SELECT DISTINCT terms1.term_id as cat_id, terms1.name as cat_name, termsp.name as cat_parent
				FROM
					wp_posts as p1
					LEFT JOIN wp_term_relationships as r1 ON p1.ID = r1.object_ID
					LEFT JOIN wp_term_taxonomy as t1 ON r1.term_taxonomy_id = t1.term_taxonomy_id 
					LEFT JOIN wp_terms as terms1 ON t1.term_id = terms1.term_id
					LEFT JOIN wp_terms as termsp ON t1.parent = termsp.term_id
				WHERE
					t1.taxonomy = 'category' AND p1.post_status = 'publish'
				ORDER by cat_parent,cat_name
			");
			$lastcat = null;
			$temparr = array();
			foreach($cats as $cat){
				if($lastcat==$cat->cat_parent)$temparr[$cat->cat_id] = $cat->cat_name;
				else{
					if($lastcat==null)$temparr[$cat->cat_id] = $cat->cat_name;
					else{
						$json[$lastcat] = $temparr;
						$temparr = array();
						$temparr[$cat->cat_id] = $cat->cat_name;
					}
					$lastcat = $cat->cat_parent;
				}
			}
			$json[$lastcat] = $temparr;
		}
		return $json;
	}
	function widget_checkboxfilter(){
		if(!is_admin()){
	?>
		<li id="category-filter" class="widget-container widget_meta">
			<form action="" method="post">
				<fieldset>
					<legend><h3 class="widget-title">Search</h3></legend>
					<div id="categoryfilter">
						<ul>
						<?php
							foreach(get_elements(false) as $k => $v){
								echo '<li><a href="#">'.$k.'</a><ul>';
								foreach($v as $kk => $vv)echo '<li class="cat"><input class="li-filter'.$kk.'" name="cat" type="checkbox" value="'.$kk.'" /><label>'.$vv.'</label></li>';
								echo '</ul></li>';
							}
						?>
						</ul>
					</div>
					<script type="text/javascript">
						jQuery(function($){
							$('#categoryfilter').empty().checkboxfilter({
								elements:<?php echo json_encode(get_elements()) ?>,
								menu:<?php echo json_encode(get_elements(false),JSON_FORCE_OBJECT) ?>,
								name:'cat'
							});
						});
					</script>
					<input type="submit" value="search" />
				</fieldset>
			</form>
		</li>
	<?php
		}
	}
	function init_checkboxfilter(){
		if(!is_admin())wp_enqueue_script('jquery.cbfilter',WP_PLUGIN_URL.'/'.str_replace(basename( __FILE__),"",plugin_basename(__FILE__)).'/js/jquery.cbfilter-0.1.js',array('jquery'));
		wp_register_sidebar_widget('widget_checkboxfilter_1','Category Filter','widget_checkboxfilter',array('description'=>'Add this box to let the user chose the categories in your website and filter them dinamically'));
	}
	add_action('init','init_checkboxfilter');
?>