<?php
/*
Plugin Name: Comment Log
Plugin URI: http://peopletab.com/cLog.html
Description: Keep track of your comments on other blogs that support the cLog API.
Version: 1.2
Author: Ian Szewczyk
Author URI: http://peopletab.com
*/

/*  Copyright 2008  Ian Szewczyk  (email : roamzero@gmail.com)

    This program is free software; you can redistribute it and/or modify
    it under the terms of the GNU General Public License as published by
    the Free Software Foundation; either version 2 of the License, or
    (at your option) any later version.

    This program is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.

    You should have received a copy of the GNU General Public License
    along with this program; if not, write to the Free Software
    Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
*/

@session_start();
$clog_db_version = "1.0";
$clog_pear_api = false;

function cLogInstall () {
   global $wpdb, $clog_db_version;
 
   $table_name = $wpdb->prefix . "commentlog";
   if($wpdb->get_var("show tables like '$table_name'") != $table_name) {
      add_option("clog_db_version", "1.0");
      $sql = "CREATE TABLE " . $table_name . " (
	  id mediumint(9) NOT NULL AUTO_INCREMENT,
	  date int NOT NULL,
	  comment text NOT NULL,
	  url VARCHAR(255) NOT NULL,
	  UNIQUE KEY id (id)
	);";

    require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
    dbDelta($sql);
	   
	update_option("clog_db_version", $clog_db_version);
	add_option("cLogPageID", "", "Page ID for showing remote comments");

   }
   
}
register_activation_hook(__FILE__, 'cLogInstall');


function cLogAdminMenus() {
	
    add_menu_page('Comment Log', 'cLog', 'edit_others_posts', __FILE__, '_cLogAdminIndexPage');

	if (isset($_GET['page']) && $_GET['page'] == 'clog-confirm') {
		if(isset($_POST['confirm_submit']) && isset($_POST['clognonce'])) {
			if(isset($_POST['from']) && isset($_POST['comment']) && ($_POST['clognonce'] == $_SESSION['clognonce'])) {
				_cLogProcessRemoteComment($_POST['from'], $_POST['comment']);
			} 
		}
	
		add_submenu_page(__FILE__, 'Confirm', 'Confirm', 'edit_others_posts', 'clog-confirm', '_cLogAdminConfirmPage');
	}
}
add_action('admin_menu', 'cLogAdminMenus');



function cLogProcessLocalComment($redirect, $comment) 
{
	if (isset($_POST['clog_submit'])) {
		if($comment->comment_author_url) {
			// Use PEAR
			_cLogBeginPearAPI();
			
			require_once 'Net/URL.php';
			$url =& new Net_URL($comment->comment_author_url);
			if(preg_match('/^[a-z0-9-]+(\.[a-z0-9-]+)+/i', $url->host) || $url->host = 'localhost')  {
				$resource = array(
					'name' => 'clog_processor',
					'input' => array(
						'from' => urlencode($redirect),
						'comment' => urlencode($comment->comment_content)
					)
					
				);
				
			    $data = _cLogRESTTA('cLog', $url->protocol.'://'.$url->host.'/restta.xml', $resource);
					
				if(!PEAR::isError($data)) {
					return $data['url'];
				} else {
					// RESTTA error
					$error_name = "RESTTA Error";
					$error_body = "Error processing RESTTA file";
					_cLogDeleteLocalComment($comment->comment_ID);
					include(ABSPATH . 'wp-content/plugins/comment-log/Templates/error.php');
					return;
				}
				
			} else {
				// Bad host error
				$error_name = "Url Error";
				$error_body = "Error processing remote Url";
				_cLogDeleteLocalComment($comment->comment_ID);
				include(ABSPATH . 'wp-content/plugins/comment-log/Templates/error.php');
				return;
			}
				
				
			_cLogEndPearAPI();
		} else {
			// Missing comment URL
			$error_name = "Missing Url Error";
			$error_body = "Remote Url not provided";
			_cLogDeleteLocalComment($comment->comment_ID);
			include(ABSPATH . 'wp-content/plugins/comment-log/Templates/error.php');
			return;
		}
	} else {
		// Normal comment post
		return $redirect;
	}
}
add_filter('comment_post_redirect', 'cLogProcessLocalComment',0,2);



function cLogAddCommentLogButton() 
{
	global $user_ID;
	if (!isset($user_ID)) {
		include_once (ABSPATH . 'wp-content/plugins/comment-log/Templates/button.php');
	}
}
add_action( 'comment_form', 'cLogAddCommentLogButton' );


function cLogCommentsPage($content) 
{
	$page_id = get_option('cLogPageID');
	if(is_page($page_id) AND $page_id != "") {
		echo $content;
		$comments = _cLogGetComments();
		$date_format = get_option('links_updated_date_format');
		include_once (ABSPATH . 'wp-content/plugins/comment-log/Templates/commentsPage.php');
	} else	{
		return $content;
	}
}
add_action('the_content', 'cLogCommentsPage');

// Non-hooked functions
function _cLogAdminIndexPage() 
{
	global $wpdb;

	// Use PEAR
	_cLogBeginPearAPI();
	
	require_once 'Net/URL.php';
	$url =& new Net_URL();
	
	$date_format = get_option('links_updated_date_format');

	// Confirm Delete
	if(isset($_GET['delete_id']) && preg_match('/^[0-9]+$/', $_GET['delete_id'])) {
		$_SESSION['clogdeletenonce'] = wp_create_nonce();
		include(ABSPATH . 'wp-content/plugins/comment-log/Templates/adminIndexPageConfirmDelete.php');
		return;
	}
	
	// Process Delete
	if(isset($_POST['page_id']) && isset($_POST['confirm_delete_submit']) && ($_POST['clogdeletenonce'] == $_SESSION['clogdeletenonce'])) {
		// Delete
		_cLogDeleteComment($_POST['page_id']);
		
		$confirm_delete = 'Comment Deleted';
	}
	
	// Page ID to display comments
	if(isset($_POST['pageset_submit'])) {
		if(isset($_POST['page_id'])) {
			 update_option('cLogPageID', $_POST['page_id']);
			 $page_id = $_POST['page_id'];
			 $update_page_id = true;
		}
	} else {
		 $page_id = get_option('cLogPageID');
	}
	
	$post_table = $wpdb->prefix  . "posts";
	$sql = 'SELECT ID, post_name FROM '.$post_table.' WHERE post_type = "page"';
	
	$pages = $wpdb->get_results($sql); 
	
	$comments = _cLogGetComments();
	
	include(ABSPATH . 'wp-content/plugins/comment-log/Templates/adminIndexPage.php');
	
	_cLogEndPearAPI();
	
	
	
	
}

function _cLogGetComments() 
{
	global $wpdb;

	$table_name = $wpdb->prefix . "commentlog";
	
	_cLogBeginPearAPI();
	require_once 'Pager/Pager.php';
	//first, we use Pager to create the links
	$num_comments = $wpdb->get_results('SELECT COUNT(*) AS num FROM '.$table_name);
	$pager_options = array(
	    'mode'       => 'Sliding',
	    'perPage'    => 10,
	    'delta'      => 3,
		'urlVar'     => 'clogpage',
	    'totalItems' => $num_comments[0]->num,
	);

	$pager = Pager::factory($pager_options);

	//then we fetch the relevant records for the current page
	list($from, $to) = $pager->getOffsetByPageId();
	//set the OFFSET and LIMIT clauses for the following query
	$limit = 'LIMIT '.($from - 1).','.$pager_options['perPage'];
	$query = 'SELECT * FROM '.$table_name.' ORDER BY date DESC '.$limit;
	$comments = $wpdb->get_results($query);
	$return['comments'] = $comments;
	$return['pager'] =& $pager;
	_cLogEndPearAPI();
	
	return $return;
}

function _cLogAdminConfirmPage() {
	
	
	
	if(isset($_GET['from']) && isset($_GET['comment'])) {
		$from = wp_specialchars(urldecode($_GET['from']));
		$comment = wp_specialchars(urldecode($_GET['comment']));
		$nonce = wp_create_nonce();
		$url = get_settings('siteurl');
		$_SESSION['clognonce'] = $nonce;
		include(ABSPATH . 'wp-content/plugins/comment-log/Templates/adminConfirmPage.php');
	}
}

function _cLogProcessRemoteComment($url, $comment) 
{
	global $wpdb;
 
    $table_name = $wpdb->prefix . "commentlog";
    $insert = "INSERT INTO " . $table_name .
        " (date, comment, url) " .
        "VALUES ('" . time() . "','" . $wpdb->escape($comment) . "','" . $wpdb->escape($url) . "')";

    $results = $wpdb->query( $insert );
	
	wp_redirect($url);

}

function _cLogRESTTA($requested_class, $url, $requested_resource)
{
	
	
	require_once 'HTTP/Request.php';
	$req =& new HTTP_Request($url);
	if (!PEAR::isError($req->sendRequest())) {
		$restta = $req->getResponseBody();
	} else {
		return PEAR::raiseError('RESTTA file not found');
	}
	
	
	if(isset($restta)) {
		
		
		
		// Include XML_Unserializer
		require_once 'XML/Unserializer.php'; 
		require_once 'Net/URL.php';
		$urlobj =& new Net_URL($url);
		// Instantiate the serializer
		$unserializer = &new XML_Unserializer(
								array('parseAttributes' => TRUE, 'forceEnum' => array('resource','input')));
		
		// Serialize the data structure
		$status = $unserializer->unserialize($restta);
		
		// Check whether serialization worked
		if (PEAR::isError($status)) {
			return $status;
		}
		
		$restta_data = $unserializer->getUnserializedData();
	
		
		
		
		foreach($restta_data['appClass'] AS $app_class) {
			if ($app_class['name'] == $requested_class) {
				foreach($app_class['resource'] AS $resource) {
					if($requested_resource['name'] == $resource['name']) {
						if(substr($resource['uriPattern'], 0, 4) != 'http') {
							if ($resource['pathPrefix']) {
								$pathPrefix = $resource['pathPrefix'];
							} else if($app_class['pathPrefix']) {
								$pathPrefix = $app_class['pathPrefix'];
							} else if($restta_data['pathPrefix']) {
								$pathPrefix = $restta_data['pathPrefix'];
							} else {
								$pathPrefix = '';
							}
						
						
							$resource['uriPattern'] = $urlobj->protocol.'://'.$urlobj->host.$pathPrefix.$resource['uriPattern'];
						
						}	
						$data = _cLogRESTTAProcessResource($resource, $requested_resource['input']);
						
						return $data;
					}
				
				}
							
			} 
			
			
			if ($app_class['name'] == 'restta'){
				foreach($app_class['resource'] AS $resource) {
					if($resource['name'] == 'restta_delegate') {
						if(substr($resource['uriPattern'], 0, 4) != 'http') {
							if ($resource['pathPrefix']) {
								$pathPrefix = $resource['pathPrefix'];
							} else if($app_class['pathPrefix']) {
								$pathPrefix = $app_class['pathPrefix'];
							} else if($restta_data['pathPrefix']) {
								$pathPrefix = $restta_data['pathPrefix'];
							} else {
								$pathPrefix = '';
							}
							$resource['uriPattern'] = $urlobj->protocol.'://'.$urlobj->host.$pathPrefix.$resource['uriPattern'];
						
						}	
						$input_data = array(
							'app_class' => $requested_class
						);
						$data = _cLogRESTTAProcessResource($resource, $input_data);
						if (PEAR::isError($data)) {
							return $data;
						} else {
							return _cLogRESTTA($app_class, $data['url'], $resource);
						}
					}
				}
				
			}
		}
	} else {
		// RESTTA error
		return PEAR::raiseError('No RESTTA file set');
	}
}

function _cLogRESTTAProcessResource($resource, $supplied_input)
{
	$get_array = array();
	$post_array = array();
	foreach($resource['input'] AS $input) {
		if($input['type'] == 'get') {
			if(!$input['queryKey']) {
				$querykey = $input['name'];
			} else {
				$querykey = $input['queryKey'];
			}
			// Default fallback
			$get_array[$querykey] = $supplied_input[$input['name']];
			
		} else if($input['type'] == 'post') {
			if(!$input['queryKey']) {
				$querykey = $input['name'];
			} else {
				$querykey = $input['queryKey'];
			}
			// Default fallback
			
			$post_array[$querykey] = $supplied_input[$input['name']];
			
		} else if($input['type'] == 'uriPattern') {
			$resource['uriPattern'] = str_replace('['.$input['name'].']',$supplied_input[$input['name']], $resource['uriPattern']);
		}
	}

	$fin_url =& new Net_URL($resource['uriPattern']);
	if (count($get_array)) {
		if (count($fin_url->querystring)) {
			$fin_url->querystring = array_merge($fin_url->querystring,$get_array);
		} else {
			$fin_url->querystring = $get_array;
			
		}
	}
	
	$return['url'] =  $fin_url->getURL();
	
	if (count($post_array)) {
		$return['post'] = $post_array;
	}
	
	return $return;
	
						

}

function _cLogDeleteComment($comment_id)
{
	global $wpdb;
 
    $table_name = $wpdb->prefix . "commentlog";
	$delete = "DELETE FROM " . $table_name .
		" WHERE ID = " . $wpdb->escape($comment_id) . " LIMIT 1";

    $results = $wpdb->query( $delete );

}

function _cLogDeleteLocalComment($comment_id)
{
	global $wpdb;
 
    $table_name = $wpdb->prefix . "comments";
	$delete = "DELETE FROM " . $table_name .
		" WHERE  comment_ID = " . $wpdb->escape($comment_id) . " LIMIT 1";

    $results = $wpdb->query( $delete );

}


// Use PEAR
function _cLogBeginPearAPI()
{
	global $clog_pear_api;
	if($clog_pear_api == false) {
		$path = get_include_path();
		$api_path = ABSPATH . 'wp-content/plugins/comment-log/PEAR';
		$path = $api_path . PATH_SEPARATOR . $path;
		set_include_path($path);
		$clog_pear_api = true;
	}

}


// Stop using PEAR 
function _cLogEndPearAPI()
{
	global $clog_pear_api;
	if($clog_pear_api == true) {
		$path = get_include_path();
		$api_path = ABSPATH . 'wp-content/plugins/comment-log/PEAR';
		$path = substr($path, strlen($api_path) + 2);
		set_include_path($path);
		$clog_pear_api = false;
	}
}
?>