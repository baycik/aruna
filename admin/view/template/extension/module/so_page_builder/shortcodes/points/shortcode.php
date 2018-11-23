<?php 
function pointsYTShortcode($atts,$contentC,$module_id,$id,$database){
	$css ='';
	$points = '';
	if($module_id != "0"){
		$css .= "/* Style Points */ \n";
		$css .= $atts->css_internal;
		$css .= "#".$id."{max-width:".$atts->width."%}";
		$file = '../catalog/view/javascript/so_page_builder/css/style_render_'.$module_id.'.css';
		// Open the file to get existing content
		$current = file_get_contents($file);
		// Append a new person to the file
		$current .= $css."\n";
		// Write the contents back to the file
		file_put_contents($file, $current);
		if(!empty($contentC)){
			add_ytshortcode("points_item");
			$atts->dem = 1;
			foreach($contentC as $child => $value){
				$points .= points_itemYTShortcode($value,$atts,$module_id,$id,$database);
				$atts->dem += 1;
			}
		}
	}else{
		if ($atts->src == ''){
			 $atts->src = 'no_image.png';
		}
		$image = '<img src="'. $database['url']. yt_image_media($atts->src) . '" alt="' .uniqid("title_").rand().time(). '" />';
		
		
		$points .= "<div class='yt-product-wrapper' id='".$id."'>";
		$points .= $image.'<ul class="blank">';
		if(!empty($contentC)){
			add_ytshortcode("points_item");
			$atts->dem = 1;
			foreach($contentC as $child => $value){
				$points .= points_itemYTShortcode($value,$atts,$module_id,$id,$database);
				$atts->dem += 1;
			}
		}
		$points .= '</ul></div>';
	}
	return $points;
}
?>