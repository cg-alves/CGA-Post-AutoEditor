<?php
/**
 * @package Post_AutoEdit
 * @version 1.0
 */
/*
Plugin Name: CGA Post Auto Editor
Plugin URI: https://github.com/cg-alves/CGA-Post-AutoEditor
Description: Plugin for automatically editing post titles.
Author: Carlos Alves
Version: 1.0
Author URI: https://masto.pt/@carlosalves
License: GPLv3
Text Domain: cga-post-autoeditor
Domain Path: /lang/
*/

defined( 'ABSPATH' ) or die( 'No script kiddies please!' );

add_action( 'admin_menu', 'cgapae_menu');
add_action( 'plugins_loaded', 'cgapae_load_textdomain' );

function cgapae_load_textdomain() {
	load_plugin_textdomain( 'cga-post-autoeditor', false, dirname( plugin_basename(__FILE__) ) . '/lang/' );
    // Define the description and name as translatable strings.
    __( 'CGA Post Auto Editor', 'cga-post-autoeditor' );
    __( 'Plugin for automatically editing post titles.', 'cga-post-autoeditor' );
}

function cgapae_menu() {
	add_menu_page(__( 'CGA Post Auto Editor', 'cga-post-autoeditor' ), __( __(  'Post Auto Editor', 'cga-post-autoeditor' ), 'cga-post-autoeditor' ), 'manage_options', 'cgapae-mainmenu', 'cgapae_mainmenu', 'dashicons-edit', 6  );
	add_submenu_page(__( 'Post Type Chooser', 'cga-post-autoeditor' ), __( 'Post Type Chooser', 'cga-post-autoeditor' ), 'Whatever You Want','manage_options', 'cgapae-posttypes', 'cgapae_getposttypes');
    add_submenu_page(__( 'Editing Options', 'cga-post-autoeditor' ), __( 'Editing Options', 'cga-post-autoeditor' ), 'Whatever You Want','manage_options', 'cgapae-editposts', 'cgapae_editchoice');
    add_submenu_page(__( 'Post Backup Import', 'cga-post-autoeditor' ), __( 'Post Backup Import', 'cga-post-autoeditor' ), 'Whatever You Want','manage_options', 'cgapae-importposts', 'cgapae_importposts');
    add_submenu_page(__( 'Post Backup Download', 'cga-post-autoeditor' ), __( 'Post Backup Download', 'cga-post-autoeditor' ), 'Whatever You Want','manage_options', 'cgapae-filedownload', 'cgapae_filedownload');
    add_submenu_page(__( 'Post Backup Download', 'cga-post-autoeditor' ), __( 'Post Backup Download', 'cga-post-autoeditor' ), 'Whatever You Want','manage_options', 'cgapae-exportposts', 'cgapae_exportposts');
    add_submenu_page(__( 'Post Backup Import', 'cga-post-autoeditor' ), __( 'Post Backup Import', 'cga-post-autoeditor' ), 'Whatever You Want','manage_options', 'cgapae-importer', 'cgapae_fileupload');
    add_submenu_page(__( 'Changes Done', 'cga-post-autoeditor' ), __( 'Changes Done', 'cga-post-autoeditor' ), 'Whatever You Want','manage_options', 'cgapae-alldone', 'cgapae_alldone');
    add_submenu_page(__( 'Post Type Chooser', 'cga-post-autoeditor' ), __( 'Post Type Chooser', 'cga-post-autoeditor' ), 'Whatever You Want','manage_options', 'cgapae-posttypes', 'cgapae_getposttypes');
    add_submenu_page(__( 'Manual Search', 'cga-post-autoeditor' ), __( 'Manual Search', 'cga-post-autoeditor' ), 'Whatever You Want','manage_options', 'cgapae-manualsearch', 'cgapae_manualsearch');
}

//These actions allow the redirection of the user between functions, as well as the download of the backup.
add_action("admin_init", "cgapae_filedownload"); 
add_action('template_redirect','cgapae_exportposts');   
add_action('template_redirect','cgapae_filedownload'); 

//Simple function to display a landing page.
function cgapae_mainmenu() {
    ?>
        <div class="wrap">
            <h1 class='wp-heading-inline'><?php echo esc_attr( __( 'CGA Post Auto Editor', 'cga-post-autoeditor' ) ); ?></h1>
            <hr class='wp-header-end' />
            <br />
            <a class='button button-primary load-customize hide-if-no-customize' href='../wp-admin/admin.php?page=cgapae-importposts'><?php echo esc_attr( __( 'Import Posts', 'cga-post-autoeditor' ) ); ?></a>
            <a class='button button-primary load-customize hide-if-no-customize' href='../wp-admin/admin.php?page=cgapae-exportposts'><?php echo esc_attr( __( 'Export Posts', 'cga-post-autoeditor' ) ); ?></a>
            <a class='button button-primary load-customize hide-if-no-customize' href='../wp-admin/admin.php?page=cgapae-posttypes'><?php echo esc_attr( __( 'Edit Posts', 'cga-post-autoeditor' ) ); ?></a>
        </div>
    <?php
}

//Simple function to display a page when there were changes committed to the database.
function cgapae_alldone() {
    ?>
        <div class="wrap">
            <h1 class='wp-heading-inline'><?php echo esc_attr( __( 'Changes Done', 'cga-post-autoeditor' ) ); ?></h1>
            <a class='page-title-action' href='admin.php?page=cgapae-mainmenu'><?php echo esc_attr( __( 'Return to the Main Menu', 'cga-post-autoeditor' ) ); ?></a>
            <hr class='wp-header-end' />
            <br />
            <p><?php echo esc_attr( __( 'Your changes have been committed to the database.', 'cga-post-autoeditor' ) ); ?></p>
        </div>
    <?php
}

//This function creates a file, names it and fills it with the serialized contents of the array %posts, when the user clicks the download button
function cgapae_filedownload(){
    if ( isset($_POST['download_posts']) ) {
        $posts = cgapae_getposts();        
        $file = 'post_backup-'.gmdate("Y-m-d_h:i:s").'.txt';
        $current = json_encode(serialize($posts));
        file_put_contents($file, $current);
        if (file_exists($file)) {
            cgapae_addheaders($file);
            readfile($file);
            exit;
        }
    } 
}

//This function creates a page for the user to choose what sort of changes he wants to commit to the database.
function cgapae_editchoice() {
	if (isset($_POST['submit'])) {
        if ( $_POST['submit'] == ( esc_attr( __( 'First letter of the post uppercase', 'cga-post-autoeditor' ) ) ) ) {
            cgapae_edit_firstupper();
        }
        if ( $_POST['submit'] == ( esc_attr( __( 'First Letter Of Each Word Uppercase', 'cga-post-autoeditor' ) ) ) ) {
            cgapae_edit_eachupper();
        }
        if ( $_POST['submit'] == ( esc_attr( __( 'ALL UPPERCASE', 'cga-post-autoeditor' ) ) ) ) {
            cgapae_edit_allupper();
        }
        if ( $_POST['submit'] == ( esc_attr( __( 'all lowercase', 'cga-post-autoeditor' ) ) ) ) {
            cgapae_edit_alllower();
        }
	}
	?>
        <div class="wrap">
            <h1 class='wp-heading-inline'><?php echo esc_attr( __( 'Choose your edit', 'cga-post-autoeditor' ) ); ?></h1>
            <a class='page-title-action' href='admin.php?page=cgapae-mainmenu'><?php echo esc_attr( __( 'Return to the Main Menu', 'cga-post-autoeditor' ) ); ?></a>
            <hr class='wp-header-end' />
            <br />
            <form method="POST" id="edit_choice">
                <input id="firstupper" name="submit" type="submit" value="<?php echo esc_attr( __( 'First letter of the post uppercase', 'cga-post-autoeditor' ) ); ?>" class='button button-primary load-customize hide-if-no-customize'>
                <input id="eachupper" name="submit" type="submit" value="<?php echo esc_attr( __( 'First Letter Of Each Word Uppercase', 'cga-post-autoeditor' ) ); ?>" class='button button-primary load-customize hide-if-no-customize'>
                <input id="allupper" name="submit" type="submit" value="<?php echo esc_attr( __( 'ALL UPPERCASE', 'cga-post-autoeditor' ) ); ?>" class='button button-primary load-customize hide-if-no-customize'>
                <input id="alllower" name="submit" type="submit" value="<?php echo esc_attr( __( 'all lowercase', 'cga-post-autoeditor' ) ); ?>" class='button button-primary load-customize hide-if-no-customize'>
            </form>
        </div>
	<?php
}

//Simple function to change the first letter of the post title to Uppercase while the rest is lowercase
function cgapae_edit_firstupper() {
	$posts = get_transient ( 'selected_posts' );
	foreach ($posts as $each_post => $post_prop ){
        $corrected_posts [$post_prop['ID']] [ 'ID' ] = $post_prop['ID'] ;
        $corrected_posts [$post_prop['ID']] [ 'post_title' ] = ucfirst(strtolower($post_prop['post_title']));
    }
    cgapae_saveposts($corrected_posts);
}

//Simple function to change the first letter of each word of the post title to Uppercase while the rest is lowercase
function cgapae_edit_eachupper() {
	$posts = get_transient ( 'selected_posts' );
	foreach ($posts as $each_post => $post_prop ){
        $corrected_posts [$post_prop['ID']] [ 'ID' ] = $post_prop['ID'] ;
        $corrected_posts [$post_prop['ID']] [ 'post_title' ] = ucwords(strtolower($post_prop['post_title']));
    }
    cgapae_saveposts($corrected_posts);
}

//Simple function to change all letters to uppercase
function cgapae_edit_allupper() {
	$posts = get_transient ( 'selected_posts' );
	foreach ($posts as $each_post => $post_prop ){
        $corrected_posts [$post_prop['ID']] [ 'ID' ] = $post_prop['ID'] ;
        $corrected_posts [$post_prop['ID']] [ 'post_title' ] = strtoupper($post_prop['post_title']);
    }
    cgapae_saveposts($corrected_posts);
}

//Simple function to change all letters to lowercase
function cgapae_edit_alllower() {
	$posts = get_transient ( 'selected_posts' );
	foreach ($posts as $each_post => $post_prop ){
        $corrected_posts [$post_prop['ID']] [ 'ID' ] = $post_prop['ID'] ;
        $corrected_posts [$post_prop['ID']] [ 'post_title' ] = strtolower($post_prop['post_title']);
    }
    cgapae_saveposts($corrected_posts);
}

//Does a simple query to obtain the current names of the posts, as well as their ids
function cgapae_getposts(){
    $selected_types = get_transient( 'selected_post_types' );
    
    global $wpdb ;    
    foreach ($selected_types as $each_type => $type_prop ){
        $query = "SELECT ID, post_title FROM wp_posts WHERE post_type = '$type_prop'";
        $results = $wpdb -> get_results ($query);
        foreach ($results as $each_result => $result_value) {
            $posts [$result_value->ID] ['ID'] = $result_value->ID;
            $posts [$result_value->ID] ['post_title'] = $result_value->post_title;
        }
    }
    set_transient( 'selected_posts', $posts, 30 * MINUTE_IN_SECONDS );
}

//Queries DB to get post types based on a form selection that prints most normal post types.
function cgapae_getposttypes(){
    if ( isset ( $_POST['type_select'] ) ) {
        $i=0;
        foreach ( $_POST as $type => $selection ) {
            if ( $selection == 1 ) {
                $selected_types [ $i ] = $type;
                $i++;
            }
        }
        set_transient( 'selected_post_types', $selected_types, 30 * MINUTE_IN_SECONDS );
        cgapae_getposts();
        echo '<script>window.location.href="admin.php?page=cgapae-editposts"</script>'; //Workaround for redirecting within the wp-admin page.
    }
    
    $query = "SELECT DISTINCT post_type FROM wp_posts WHERE post_type NOT LIKE '%/_%' ESCAPE '/' and post_type != 'revision'";
    global $wpdb ;
    $post_types = $wpdb -> get_results ( $query );
    ?>
        <div class="wrap">
            <h1 class='wp-heading-inline'><?php echo esc_attr( __( 'Choose the post types you wish to edit', 'cga-post-autoeditor' ) ); ?></h1>
            <a class='page-title-action' href='admin.php?page=cgapae-mainmenu'><?php echo esc_attr( __( 'Return to the Main Menu', 'cga-post-autoeditor' ) ); ?></a>
            <hr class='wp-header-end' />
            <br />
            <table class='wp-list-table widefat striped tags'>
                <thead>
                    <tr>
                        <th class='manage-column column-author' style='text-align:center;width:50%;'><h3><?php echo esc_attr( __( 'Post Type', 'cga-post-autoeditor' ) ); ?></h3></th>
                        <th class='manage-column column-author' style='text-align:center;width:50%;'><h3><?php echo esc_attr( __( 'Selection', 'cga-post-autoeditor' ) ); ?></h3></th>
                    </tr>
                </thead>
                <tbody>
                <form method='post' style='text-align:center' name='type_select'>
    <?php
            foreach ($post_types as $each_type => $type_value) {
                    ?>
                        <tr>
                            <td class='author column-author' style='text-align:center;'> <b> "<?php echo esc_attr( $type_value->post_type ); ?>" </b> </td>
                            <td class='author column-author' style='text-align:center;'> <input type='hidden' value='0' name="<?php echo esc_attr( $type_value->post_type ); ?>"> <input type='checkbox' name="<?php echo esc_attr( $type_value->post_type ); ?>" value='1'> </input> </td>
                        </tr>
                    <?php
            }
    ?>
                <tfoot>
                    <tr>
                        <td class='manage-column column-author' colspan='2' style='text-align:center;'>
                            <input class='button button-primary button-hero load-customize hide-if-no-customize' type='submit' name='type_select' value='<?php echo esc_attr( __( 'Select Post Types', 'cga-post-autoeditor' ) ); ?>'/> </input>
                        </td>
                    </form>
                    </tr>
                    <tr>
                        <td class='manage-column column-author' colspan='2' style='text-align:center;width:50%;font-weight:bold;'><?php echo esc_attr( __( 'OR', 'cga-post-autoeditor' ) ); ?></td>
                    </tr>
                    <tr>
                        <td class='manage-column column-author' colspan='2' style='text-align:center;'>
                            <a class='button button-primary button-hero load-customize hide-if-no-customize' href='admin.php?page=cgapae-manualsearch'><?php echo esc_attr( __( 'Manual Search by Post ID (Advanced)', 'cga-post-autoeditor' ) ); ?></a>
                        </td>
                    </tr>
                </tfoot>
            </table>
        </div>
    <?php
}

//Commits user changes to database and redirects to a "finished" page
function cgapae_saveposts($corrected_posts){
    global $wpdb;
    foreach ($corrected_posts as $each_post => $post_prop ){
        $post_id = addslashes($post_prop['ID']);
        $new_post_name = addslashes($post_prop['post_title']);
        $query = "UPDATE wp_posts  SET post_title = '$new_post_name' WHERE  ID = $post_id";
        $savechanges = $wpdb -> get_results ($query);
    }
    echo '<script>window.location.href="admin.php?page=cgapae-alldone"</script>'; //Workaround for redirecting within the wp-admin page.
}

//Creates the "menu" for the backup download
function cgapae_exportposts(){
    cgapae_filedownload();
    ?>
        <div class="wrap">
            <h1 class='wp-heading-inline'><?php echo esc_attr( __( 'Save your backup to a safe location', 'cga-post-autoeditor' ) ); ?></h1>
            <a class='page-title-action' href='admin.php?page=cgapae-mainmenu'><?php echo esc_attr( __( 'Return to the Main Menu', 'cga-post-autoeditor' ) ); ?></a>
            <hr class='wp-header-end' />
            <br />
            <form method="post" id="download_posts" action="">
                <input type="submit" name="download_posts" class='button button-primary load-customize hide-if-no-customize' value="<?php echo esc_attr( __( 'Download Backup', 'cga-post-autoeditor' ) ); ?>" />
            </form>
        </div>
    <?php
}

//Headers for file download
function cgapae_addheaders($file){
    header('Content-Description: File Transfer');
    header('Content-Type: application/octet-stream');
    header('Content-Disposition: attachment; filename="'.$file.'"');
    header('Expires: Mon, 26 Jul 1997 05:00:00 GMT');
    header('Last-Modified: '.gmdate('D, d M Y H:i:s').' GMT');
    header('Cache-Control: cache, must-revalidate');
    header('Pragma: public');
    header('Content-Length: ' . filesize($file));
}

//Page to upload the backup file
function cgapae_importposts(){
    ?>
        <div class="wrap">
            <h1 class='wp-heading-inline'><?php echo esc_attr( __( 'Select plugin backup to upload', 'cga-post-autoeditor' ) ); ?></h1>
            <a class='page-title-action' href='admin.php?page=cgapae-mainmenu'><?php echo esc_attr( __( 'Return to the Main Menu', 'cga-post-autoeditor' ) ); ?></a>
            <hr class='wp-header-end' />
            <br />
            <form name="upload" action="admin.php?page=cgapae-importer" method="post" enctype="multipart/form-data">
                <input type="file" name="file" id="file" class='button load-customize hide-if-no-customize' style='text-align:center;padding-top:0;'>
                <input type="submit" value="<?php echo esc_attr( __( 'Upload File', 'cga-post-autoeditor' ) ); ?>" class='button button-primary load-customize hide-if-no-customize' name="submit">
            </form>
        </div>
    <?php
}

//This function will take an uploaded file from importposts and, translate it into an array and then run a SQL query to update the titles.
function cgapae_fileupload(){
    global $wpdb ;
    $file = file_get_contents($_FILES['file']['tmp_name']);
    $reverse = unserialize(json_decode($file));
    foreach ( $reverse as $each => $key ) {
        $post_id = addslashes($key->ID);
        $new_post_name = addslashes($key->post_title);
        $query = "UPDATE wp_posts  SET post_title = '$new_post_name' WHERE  ID = $post_id";
        $savechanges = $wpdb -> get_results ($query);
    }
    echo '<script>window.location.href="admin.php?page=cgapae-alldone"</script>'; //Workaround for redirecting within the wp-admin page.
}

//Function creates a page with a textarea where the user can manually insert post IDs for a more granular edit.
function cgapae_manualsearch() {
    if ( isset ( $_POST['search'] ) ) {
        $tmp = stripslashes ( $_POST['search'] );
        $search = ( explode ( ',', $tmp ) ); 
        cgapae_searcher( $search );
    }

    ?>
        <div class="wrap">
            <h1 class="wp-heading-inline"><?php echo esc_attr( __( "Manual ID Search", 'cga-post-autoeditor' ) ); ?></h1>
            <a class='page-title-action' href='admin.php?page=cgapae-mainmenu'><?php echo esc_attr( __( "Return to the Main Menu", 'cga-post-autoeditor' ) ); ?></a>
            <hr class='wp-header-end'>
            <table class='wp-list-table widefat striped tags'>
                <caption>
                    <h3><?php echo esc_attr( __( "Please insert the IDs of the posts to edit bellow.", 'cga-post-autoeditor' ) ); ?></h3>
                </caption>
                <thead>
                    <tr>
                        <th class='manage-column column-author' colspan='2' style='text-align:center;'><?php echo esc_attr( __( "Posts to edit", 'cga-post-autoeditor' ) ); ?></th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td class='manage-column column-author' colspan='2' style='text-align:center;'>
                        <form method='post' name='search'>
                            <textarea name='search' class='widefat' style='width:100% !important; height:300px !important; overflow:auto;' placeholder='<?php echo esc_attr( __( 'Insert the IDs of the posts that you wish to edit separated by commas.&#10;&#10;Example:&#10;1,2,3', 'cga-post-autoeditor' ) ); ?>'></textarea>
                        </td>
                    </tr>
                </tbody> 
                <tfoot>
                    <tr>
                        <td class='manage-column column-author' colspan='2' style='text-align:center;'>
                            <input class='button button-primary button-hero load-customize hide-if-no-customize' type='submit' name='plugin_select' value='<?php echo esc_attr( __( 'Edit Posts', 'cga-post-autoeditor' ) ); ?>'/>
                        </td>
                    </tr>
                </form>
                </tfoot>
            </table>
        </div>
    <?php
}

//Function that recursively searches for posts by ID and then adds them to an array.
function cgapae_searcher( $search ) {
    global $wpdb ;
    foreach ($search as $each_id ){
        $query = "SELECT ID, post_title FROM wp_posts WHERE ID = $each_id";
        $results = $wpdb -> get_results ($query);
        foreach ($results as $each_result => $result_value) {
            $posts [$result_value->ID] ['ID'] = $result_value->ID;
            $posts [$result_value->ID] ['post_title'] = $result_value->post_title;
        }
    }
    set_transient( 'selected_posts', $posts, 30 * MINUTE_IN_SECONDS );
    echo '<script>window.location.href="admin.php?page=cgapae-editposts"</script>'; //Workaround for redirecting within the wp-admin page.
}
