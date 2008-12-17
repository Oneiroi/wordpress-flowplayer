<?PHP
/*
Plugin Name: Flowplayer for Wordpress
Plugin URI: http://saiweb.co.uk/wordpress-flowplayer
Description: Flowplayer Wordpress Extension GPL Edition
Version: 2.0.0.68
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
 	$html = "\n<!-- Saiweb.co.uk Flowplayer For Wordpress Javascript Start -->\n";
	$html .= '<script type="text/javascript" src="'.flowplayer::RELATIVE_PATH.'/flowplayer_3.0.1_gpl/flowplayer.min.js"></script>';
 	$html .= "\n<!-- Saiweb.co.uk Flowplayer For Wordpress Javascript END -->\n";
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

/**
 * Output 'selected' bool options based on arg passed
 * @var string true / false
 * @return string HTML
 */
function bool_select($current) {
	switch($current) {
		 		case "true":
		 			$html = '<option selected value="true">true</option><option value="false">false</option>';
		 		break;
		 		case "false":
		 			$html = '<option value="true" >true</option><option selected value="false">false</option>';
		 		break;
		 		default:
		 			$html = '<option value="true">true</option><option selected value="false">false</option>';
		 		break;
		 	}
		 return $html;
}

/**
 * Generate opacity options
 */
function opacity_select($current) {
	//setup possble vals array
	$vals = array (
					"1.0" => "",
					"0.9" => "",
					"0.8" => "",
					"0.7" => "",
					"0.6" => "",
					"0.5" => "",
					"0.4" => "",
					"0.3" => "",
					"0.2" => "",
					"0.1" => ""
					);
	//set current selected value
	$vals[$current] = "selected";
	//set html
	$html ='	
				<option '.$vals["1.0"].' value="1.0">1.0</option>
				<option '.$vals["0.9"].' value="0.9">0.9</option>
				<option '.$vals["0.8"].' value="0.8">0.8</option>
				<option '.$vals["0.7"].' value="0.7">0.7</option>
				<option '.$vals["0.6"].' value="0.6">0.6</option>
				<option '.$vals["0.5"].' value="0.5">0.5</option>
				<option '.$vals["0.4"].' value="0.4">0.4</option>
				<option '.$vals["0.3"].' value="0.3">0.3</option>
				<option '.$vals["0.2"].' value="0.2">0.2</option>
				<option '.$vals["0.1"].' value="0.1">0.1</option>
			'; 
	return $html;
}
function flowplayer_page() {
	//initialize the class:
	$fp = new flowplayer();
	
	$html = 
'<div class="wrap">
<form id="wpfp_options" method="post">
<div id="icon-options-general" class="icon32"><br></div>
<h2><a href="http://www.saiweb.co.uk">Saiweb</a> Flowplayer for Wordpress</h2>
<h3>Please set your default player options below</h3>
<table>
	<tr>
		<td>AutoPlay: </td>
		<td>
		 	<select name="autoplay">';
	$html .= bool_select($fp->conf['autoplay']);	 	
	$html .=' 
		 	</select>
		 </td>
	</tr>
	<tr>
		<td>BG Colour: </td>
		<td>
			#<input type="text" size="6" name="bgcolour" id="bgcolour" value="'.$fp->conf['bgcolour'].'" style="background:#'.$fp->conf['bgcolour'].'" />
		</td>
	</tr>
	<tr>
		<td>Commercial License Key: </td>
		<td>
			<input type="text" size="20" name="key" id="key" value="'.$fp->conf['key'].'" />	
			(Required for certain features i.e. custom logo)
		</td>
	</tr>
	<tr>
		<td>Auto Buffering:</td>
		<td><select name="autobuffer">';
$html .= bool_select($fp->conf['autobuffer']);
$html .='
		</select></td>
	</tr>
	<tr>
		<td>Opacity</td>
		<td>
			<select name="opacity">';
			
$html .= opacity_select($fp->conf['opacity']);
$html .= '		
			</select>
		</td>
	<tr>
		<td>&nbsp;</td>
		<td>
			<input type="submit" name="submit" class="button-primary" value="Save Changes" />
		</td>
	</tr>
</table>
</form>
<h3>Like this plugin?</h3>
A lot of development time and effort went into Flowplayer and this plugin, you can help support further development by purchasing a comercial license for flowplayer.
<h3><a href="http://flowplayer.org/download/index.html?aff=100">Get a commercial license now!</a></h3>
<br /><br />
<h3><a href="http://trac.saiweb.co.uk/saiweb">Report a Bug</a></h3>
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
	
	} 
	return $content;
}

class flowplayer
{
	private $count = 0;
	
	/**
	 * Relative URL path
	 */
	const RELATIVE_PATH = '/wp-content/plugins/word-press-flow-player';
	/**
	 * Where videos _should_ be stored
	 */
	const VIDEO_PATH = '/wp-content/videos/';
	/**
	 * Where the config file should be
	 */
	private $conf_path = '';
	
	/**
	 * config stack
	 */
	public $conf = array();
	
	/**
	 * Class construct
	 */
	public function __construct() {
		//set conf path
		$this->conf_path = $_SERVER['DOCUMENT_ROOT'].flowplayer::RELATIVE_PATH.'/saiweb_wpfp.conf';
		//if a post event has occured
		if(isset($_POST['submit'])) {
			//write config
			$this->_set_conf();
		}
		//load conf data into stack
		$this->_get_conf();
	}
	/**
	 * get config vars
	 * 
	 * @return bool Returns false on failiure, true on success
	 */
	private function _get_conf() {
		//check file exists
		if(file_exists($this->conf_path)) {
			//open file for reading
			$fp = fopen($this->conf_path,'r');
			//check if failed to open
			if(!$fp) {
				error_log('Could not open '.$this->conf_path);
				$return = false;
			} else {
				//read data
				$data = fread($fp,filesize($this->conf_path));
				//get each line
				$tmp = explode("\n",$data);
				//get each var
				foreach($tmp as $key => $dat) {
					//split from var:val
					$data = explode(':', $dat);
					//build into conf stack
					$this->conf[$data[0]] = $data[1];
					$return = true;
				}
			}
			fclose($fp);
		} else {
			error_log("Files does not exist: $this->conf_path, attempting to create");
			//attempt to create file
			if(touch($this->conf_path)) {
				//everything is ok!
				error_log($this->conf_path.' Created');
				//read the data
				$this->_get_conf();
			} else {
				//failed
				error_log($this->conf_path.' Creation failed');
				$return = false;
			}
		}
		
		return $return;
	}
	/**
	 * write config vars
	 */
	private function _set_conf() {
		//attempt to open file
		$fp = fopen($this->conf_path,'w');
		
		if(!$fp) {
			error_log('Could not open '.$this->conf_path.' for writing');
		} else {
			//file is opened for editing!
			$str = ''; //setup holder var
			//loop post data
			foreach($_POST as $key => $data) {
				//do not want to record the submit value in the config file
				if($key != "submit") {
					$str .= $key.':'.$data."\n";
				}
			}
			//comit data
			$len = strlen($str);
			//check lenght
			if($len > 0) { 
				//attempt write
				$write = fwrite($fp, $str, $len);
				//report if failed to error_log
				if(!$write) {
					error_log('Could not write to '.$this->conf_path);
				}
			} else {
				//report 0 length write attempt
				error_log('Caught attempt to write 0 length to config file, aborted');
			}
			fclose($fp);
		}
	}
	/**
	 * Salt function
	 * @return string salt
	 */
	private function _salt() {
        $salt = substr(md5(uniqid(rand(), true)), 0, 10);    
        return $salt;
	}
	
	public function build_min_player($width, $height, $media, $server=false) {
			
			//@todo check for http if not set to the following
			$media = flowplayer::VIDEO_PATH.$media;
			//set player path
			$player = flowplayer::RELATIVE_PATH.'/flowplayer_3.0.1_gpl/flowplayer-3.0.1.swf';
			
			$html = ''; //setup html var
			/**
			 * Fix #2 
			 * @see http://trac.saiweb.co.uk/saiweb/ticket/2
			 */
			$hash = md5($media.$this->_salt());
			
			/**
			 * flowplayer config
			 */
			 $html .= '<div id="saiweb_'.$hash.'" style="width:'.$width.'px; height:'.$height.'px;"></div>';

/**
 * key: \''.$this->conf['key'].'\',
 *
contextMenu: [ 
 
    {\'Worpress Flowplayer\' : function() { 
        location.href = "http://www.saiweb.co.uk/wordpress-flowplayer";  
    }}, 
     
    // menu separator.  
    \'-\', 
     
    {\'Get Flowplayer Comercial\' : function() { 
        location.href = "http://flowplayer.org/download/index.html?aff=100";  
    }} 
], 
 */
			$html .= '
<script language="JavaScript">
$f("saiweb_'.$hash.'", "'.$player.'", {   
		clip: { 
        url: \''.$media.'\', 
        autoPlay: '.$this->conf['autoplay'].',
        autoBuffering: '.$this->conf['autobuffer'].',
        opacity: '.$this->conf['opacity'].',
		backgroundColor: \'#'.$this->conf['bgcolour'].'\'
    }  
});
</script>';
			
    		//$html .= '<script language="JavaScript"> flowplayer("saiweb_'.$hash.'", "'.flowplayer::RELATIVE_PATH.'/flowplayer_3.0.1_gpl/flowplayer-3.0.1.swf"); </script>';

		return $html;
	}
}
?>