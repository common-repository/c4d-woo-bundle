<?php
add_action('admin_init', 'c4d_woo_bundle_create_list_urser_role');

function c4d_woo_bundle_filesystem_init() {
	$creds = request_filesystem_credentials(site_url() . '/wp-admin/', '', false, false, array());
	/* initialize the API */
	if ( ! WP_Filesystem($creds) ) {
		/* any problems and we exit */
		return false;
	}
}

function c4d_woo_bundle_plugin_activation () {
	c4d_woo_bundle_create_list_urser_role();
}

function c4d_woo_bundle_create_list_urser_role() {
	if (basename($_SERVER['REQUEST_URI']) == 'index.php') {
		c4d_woo_bundle_filesystem_init();
		global $wp_filesystem;

		$editableRoles = array_reverse( get_editable_roles() );
	  $userRoles = array();

    foreach ( $editableRoles as $role => $details ) {
        $name = translate_user_role($details['name'] );
				$userRoles[] = array('value' => $role, 'text' => $name);
    }
		$filename = trailingslashit(dirname(dirname(__FILE__))).'userrole.json';
		$wp_filesystem->put_contents( $filename, json_encode($userRoles), FS_CHMOD_FILE);
	}
}

function c4d_woo_bundle_get_post_meta($id, $key) {
	$bundleDatas = get_post_meta( $id, $key, true );
	if ($bundleDatas) {
		$userID 		= get_current_user_id();
		$user      	= get_userdata( $userID );
		$userRoles 	= $user ? $user->roles : 'guest';
		$bundleDatas['user_role'] = $userRoles;
	}
	return $bundleDatas;
}

