<?PHP
/*
Plugin Name: Flowplayer for Wordpress
Plugin URI: http://saiweb.co.uk/wordpress-flowplayer
Description: Flowplayer Wordpress Extension GPL Edition
Version: 2.0.0.0
Author: David Busby
Author URI: http://saiweb.co.uk
*/
/**
 * FlowPlayer for Wordpress
 * ©2008 David Busby
 * @see http://creativecommons.org/licenses/by-nc-sa/2.0/uk
 */

/**
 * WP Hooks
 */
add_action('wp_head', 'flowplayer_js');
add_filter('the_content', 'flowplayer_content');
add_action('admin_menu', 'flowplayer_admin');

/**
 * END WP Hooks
 */
 
 
/**
 * Javascript head

 */
 function flowplayer_js() {
 	$html = "\n<!--Saiweb Flwoplayer For Wordpress Javascript Start -->\n";
	$html .= '<script type="text/javascript" src="'.flowplayer::RELATIVE_PATH.'/flowplayer_3.0.1_gpl/flowplayer.min.js"></script>';
 	$html = "\n<!--Saiweb Flwoplayer For Wordpress Javascript END -->\n";
 	echo $html;
 }
/**
 * Admin menu function!
 */
function flowplayer_admin () {
	/**
	 * We're in the admin page
	 */
	 if (function_exists('add_submenu_page')) {
		add_options_page(
							'Wordpress Flowplayer', 
							'Wordpress Flowplayer', 
							8, 
							basename(__FILE__), 
							'flowplayer_page'
						);
	}
}

function flowplayer_page() {
	$html = 
'<div class="wrap">
<form id="wpfp_options">
<div id="icon-options-general" class="icon32"><br></div>
<h2><a href="http://www.saiweb.co.uk">Saiweb</a> Flowplayer for Wordpress</h2>
<h3>Please set your default player options below</h3>
<table>
	<tr>
		<td>AutoPlay</td>
		<td><select name="autoplay"><option value="true">true</option><option value="false">false</option></select></td>
	</tr>
	<tr>
		<td>BG Colour</td>
		<td>
			#<input type="text" size="6" name="bgcolour" id="bgcolour" />
			<div id="bgcolour_preview" style="width: 10px; height: 10px;" />	
		</td>
	</tr>
	<tr>
		<td>autoBuffering</td>
		<td><select name="autoBuffering"><option value="true">true</option><option value="false">false</option></select></td>
	</tr>
	<tr>
		<td>Opacity</td>
		<td>
			<select name="opactiy">
				<option value="1.0">1.0</option>
				<option value="0.9">0.9</option>
				<option value="0.8">0.8</option>
				<option value="0.7">0.7</option>
				<option value="0.6">0.6</option>
				<option value="0.5">0.5</option>
				<option value="0.4">0.4</option>
				<option value="0.3">0.3</option>
				<option value="0.2">0.2</option>
				<option value="0.1">0.1</option>		
			</select>
		</td>
	<tr>
		<td>&nbsp;</td>
		<td>
			<input type="submit" name="Submit" class="button-primary" value="Save Changes" />
		</td>
	</tr>
</table>
</form>
 </div>';
 
 echo $html;
}

function flowplayer_content( $content ) {
	$fp = new flowplayer();
	
	$regex = '/\[FLOWPLAYER=([a-z0-9\.\-\&\_]+)\,([0-9]+)\,([0-9]+)\]/';
	$matches = array();

	preg_match_all($regex, $content, $matches);
	
	if($matches[0][0] != '') {
		foreach($matches[0] as $key => $data) {
			/**
			 * 0 = string
			 * 1 = media
			 * 2 = width
			 * 3 = height
			 */
			$content = str_replace($matches[0][$key], $fp->build_min_player($matches[2][$key], $matches[3][$key], $matches[1][$key]),$content);

		}
	
	} else {
		$regex = '/\[FLOWPLAYER=([a-z0-9\.\-\&\_\:\/]+)\,([a-z0-9\.\-\&\_]+)\,([0-9]+)\,([0-9]+)\]/';
		$matches = array();
		preg_match_all($regex, $content, $matches);
		if($matches[0][0] != '') {
			foreach($matches[0] as $key => $data) {
				/**
		 		* 0 = string
		 		* 1 = server
		 		* 2 = media
		 		* 3 = width
			 	* 4 = height
			 	*/
				$content = str_replace($matches[0][$key], $fp->build_min_player($matches[3][$key], $matches[4][$key], $matches[2][$key], $matches[1][$key]),$content);
			}
		}
		
	}
	
	return $content;
}

class flowplayer
{
	private $count = 0;
	
	const RELATIVE_PATH = '/wp-content/plugins/word-press-flow-player';
	const VIDEO_PATH = '/wp-content/videos/';
	
	/**
	 * Salt function
	 * @return string salt
	 */
	private function _salt() {
        $salt = substr(md5(uniqid(rand(), true)), 0, 10);    
        return $salt;
	}
	
	public function build_min_player($width, $height, $media, $server=false) {
			$html = ''; //setup html var
			/**
			 * Fix #2 
			 * @see http://trac.saiweb.co.uk/saiweb/ticket/2
			 */
			$hash = md5($media.$this->_salt());
			
			/**
			 * Very basic integration of flowplayer 3.0.1
			 */
			$html .= '<a href="'.flowplayer::VIDEO_PATH.$media.'" style="display:block;width:425px;height:300px;" id="saiweb_'.$hash.'"></a>';
    		$html .= '<script language="JavaScript"> flowplayer("saiweb_'.$hash.'", "'.flowplayer::RELATIVE_PATH.'/flowplayer_3.0.1_gpl/flowplayer-3.0.1.swf"); </script>';

		return $html;
	}
}
?>