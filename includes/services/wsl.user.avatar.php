<?php
/*!
* WordPress Social Login
*
* http://hybridauth.sourceforge.net/wsl/index.html | http://github.com/hybridauth/WordPress-Social-Login
*    (c) 2011-2013 Mohamed Mrassi and contributors | http://wordpress.org/extend/plugins/wordpress-social-login/
*/

/** 
* Displaying the user avatar when available on the comment section
*/

// Exit if accessed directly
if ( !defined( 'ABSPATH' ) ) exit;

// --------------------------------------------------------------------

/** 
 * wsl_user_custom_avatar is borrowed from http://wordpress.org/extend/plugins/oa-social-login/ 
 * thanks a million mate
 */
function wsl_user_custom_avatar($avatar, $mixed, $size, $default, $alt)
{

	//Check if avatars are enabled
	if( get_option ( 'wsl_settings_users_avatars' ) )
	{
		//Current comment
		global $comment;
	
		//Chosen user
		$user_id = null;

		//Check if we have an user identifier
		if (is_numeric ($mixed) || $mixed['item_id'] )
		{
			if ($mixed > 0)
			{
				$user_id = $mixed;
			}
		}
		//Check if we are in a comment
		elseif (is_object ($comment) AND property_exists ($comment, 'user_id') AND !empty ($comment->user_id))
		{
			$user_id = $comment->user_id;
		}
		//Check if we have an email
		elseif (is_string ($mixed) && ($user = get_user_by ('email', $mixed)))
		{
			$user_id = $user->ID;
		}
		//Check if we have an user object
		else if (is_object ($mixed))
		{
			if (property_exists ($mixed, 'user_id') AND is_numeric ($mixed->user_id))
			{
				$user_id = $mixed->user_id;
			}
		}

		//User found?
		if ( $user_id )
		{	

			/** 
			*
			* BUDDYPRESS IMAGE FIX 
			* For anyone who uses the BuddyPress plugin: 
			* ------------------------------------------
			* This social login plugin will not display the profile images 
			* that it had pulled from the social networks the following is a fix for this
			* 
			*/

			//Check if user has an uploaded image, if so then return that avatar
			if(function_exists(bp_get_user_has_avatar))
				if(bp_get_user_has_avatar($user_id['item_id'])) return($avatar);

			//If an item ID is found that means the wordpress installation is using buddypress and the 
			//above functions havn't been called, the item_id is the user_id
			if(is_numeric($user_id['item_id'])){

				// Apply the user_id 
				$user_id = $user_id['item_id'];
				
				// The $size parameter isn't passed through with BuddyPress so the images don't have any size attributes.. 
				// But it is available in the $avatar parameters image makup, lets extract that value.
				preg_match("/width=\"(.*?)\"/", $avatar,$matches);

				// Apply the image size
				$size = $matches[1];
			}

			/* 
			* 
			* The base variables are overwritten when BuddyPress is active, this should now work. 
			* 
			*/

			
			$user_thumbnail = get_user_meta ( $user_id, 'wsl_user_image', true );
			if ( $user_thumbnail ){

				//Twitter image size fix
				if($size > 50) $user_thumbnail = str_replace("_normal","",$user_thumbnail);

				//Return image
				return '<img src="' . $user_thumbnail . '" class="avatar avatar-wordpress-social-login avatar-' . $size . ' photo" height="' . $size . '" width="' . $size . '" />'; 
			}
		}
	}

	return $avatar;
}

/*
*
* BuddyPress uses a different avatar function to grap profile images, so we'll need to hook to that function
* if buddypress is active.
*
* Before the below was edited it was just 
* add_filter( 'get_avatar', 'wsl_user_custom_avatar', 10, 5 );
* 
*/

if(function_exists("bp_core_fetch_avatar"))
	add_filter( 'bp_core_fetch_avatar', 'wsl_user_custom_avatar', 10, 5 );
else
	add_filter( 'get_avatar', 'wsl_user_custom_avatar', 10, 5 );


// --------------------------------------------------------------------
