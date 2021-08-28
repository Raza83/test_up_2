<?php
/**
 * Plugin Name: Easy Coupons
 *
 */

//adding scripts
function mw_plugin_scripts() {
    if( is_admin() ){ 
      wp_register_style( 'bootstrap', 'https://cdnjs.cloudflare.com/ajax/libs/twitter-bootstrap/4.5.2/css/bootstrap.css' );
      wp_enqueue_style('bootstrap');
  
      wp_register_style( 'bootstrap_data', 'https://cdn.datatables.net/1.10.25/css/dataTables.bootstrap4.min.css' );
      wp_enqueue_style('bootstrap_data');
  
      wp_register_script( 'jQuery', 'https://code.jquery.com/jquery-3.6.0.js', null, null, true );
      wp_enqueue_script('jQuery');
  
      wp_register_script( 'jQuery_datatable', 'https://cdn.datatables.net/1.10.25/js/jquery.dataTables.min.js', null, null, true );
      wp_enqueue_script('jQuery_datatable');
  
    } 
  }  
  add_action('admin_enqueue_scripts', 'mw_plugin_scripts'); 
  



//inserting coupons data in db
add_action('init','coupon_insert');
function coupon_insert(){
  if ( isset( $_POST['create_coupons'] ) ){

    global $wpdb;
  $table='wp_easy_coupons';
$count = $_POST['no_of_codes'];

for($i=0 ; $i < $count ; $i++)
{
    $permitted_chars = '0123456789abcdefghijklmnopqrstuvwxyz';
    // Output: 54esmdr0qf
    $coupon_code = substr(str_shuffle($permitted_chars), 0, 4);
    
      $wpdb->insert($table, array(
        'coupons' => $coupon_code,
        'coupon_expiry' => $_POST['expiry_date'],
        'flag' =>    0,
    ));
}

echo "<script> alert('Coupons are created'); </script>";
}

}


//bulk deletion on base of date
add_action('init','do_del_bulk');
function do_del_bulk(){
  if ( isset( $_POST['del_bulk'] ) ){

    global $wpdb;

    $expiry_date_bulk = $_POST['expiry_date_bulk'];
  $table='wp_easy_coupons';
  $wpdb->delete( $table, array( 'coupon_expiry' => $expiry_date_bulk ) );

  echo "<script> alert('Coupons related to specific date are deleted'); </script>";

  }

}

// individual deletion
add_action('init','do_del');
function do_del(){
  if ( isset( $_POST['delete_coupon'] ) ){

    global $wpdb;

    $coupon_id = $_POST['coupon_id'];
  $table='wp_easy_coupons';
  $wpdb->delete( $table, array( 'id' => $coupon_id ) );

  echo "<script> alert('Coupon deleted'); </script>";

  }

}


//creating table at plugin activation
function easy_coupons_table() {
    global $wpdb;
    global $easy_coupons_db_version;
    if( $wpdb->query( 'SELECT * FROM ' . $wpdb->prefix . 'easy_coupons' ) === false ) {

    $table_name = $wpdb->prefix . 'easy_coupons';
    
    $charset_collate = $wpdb->get_charset_collate();

    $sql = "CREATE TABLE $table_name (
        id mediumint(9) NOT NULL AUTO_INCREMENT,
        coupons varchar(255)  NULL,
        coupon_expiry DATE  NULL,
        flag int(10)  NULL, 
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        PRIMARY KEY  (id)
    ) $charset_collate;";

    require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
    dbDelta( $sql );

    add_option( 'easy_coupons_db_version', $easy_coupons_db_version );
    }
}

register_activation_hook( __FILE__, 'easy_coupons_table' );

function video_coupon_table() {
    global $wpdb;
    global $video_coupon_db_version;
    if( $wpdb->query( 'SELECT * FROM ' . $wpdb->prefix . 'video_coupon' ) === false ) {

    $table_name = $wpdb->prefix . 'video_coupon';
    
    $charset_collate = $wpdb->get_charset_collate();

    $sql = "CREATE TABLE $table_name (
        id mediumint(9) NOT NULL AUTO_INCREMENT,
        coupons varchar(255)  NULL,
        video_id int(20)  NULL,
        user_id int(20) NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        PRIMARY KEY  (id)
    ) $charset_collate;";

    require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
    dbDelta( $sql );

    add_option( 'video_coupon_db_version', $video_coupon_db_version );
    }
}

register_activation_hook( __FILE__, 'video_coupon_table' );


//creating admin menu for managing all operations
add_action( 'admin_menu', 'extra_post_info_menu' ); 
function extra_post_info_menu(){   
     $page_title = 'Easy Coupons';  
      $menu_title = 'Easy Coupons Menu';   
      $capability = 'manage_options';   
      $menu_slug  = 'easy_coupons';  
       $function   = 'add_coupons';  
        $icon_url   = 'dashicons-media-code';  
         $position   = 4;    add_menu_page( $page_title,    
                       $menu_title,               
                       $capability,           
                       $menu_slug,      
                       $function,       
                       $icon_url, 
                       $position ); }




// function to show form and table having data related to coupons
function add_coupons()
{
    global $wpdb;
    // this adds the prefix which is set by the user upon instillation of wordpress
    $table_name = $wpdb->prefix . "easy_coupons";
    // this will get the data from your table

    $retrieve_coupons = $wpdb->get_results( "SELECT * FROM $table_name " );

    

   
    
    $data="";
    
    $data.='
<br>
<br>

    <br><br> <form action="" method="post"> 
   
   No. of codes to generate: <input type="text" name="no_of_codes" id ="no_of_codes" >
  
   Expiry Date:  <input type="date" name="expiry_date" id ="expiry_date" > 
    <input type="submit" name="create_coupons" value = "Submit"> 
    </form> <br>';


$data.='<h2>Delete Filteration</h2>

<button class="btn btn-primary" id="delete_bulk" onclick="show_del_filter()"> Delete Filteration </button>

<div id="bulk_del_form" style="display:none;">
<br><br>
<form action="" method="post"> 
Expiry Date:  <input type="date" name="expiry_date_bulk" id ="expiry_date_bulk" > 
<input type="submit" name="del_bulk" value = "Delete"> 
</form> 
<br>
</div>
';

    
    $data.='
    <br>
    <br>
    <h2> Coupon Entries </h2>
    <br> 
    <table id="example" class="table table-striped table-bordered" style="width:100%">
              
    <thead>
 <tr>
     <th>Coupons Code</th>
     <th>Expiry</th>
     <th>Status</th>
     <th>Delete</th>
    
 </tr>
</thead>
<tbody>
    '; 
    if($retrieve_coupons)
    {
    foreach ($retrieve_coupons as $retrieve_coupon){

        if($retrieve_coupon->flag == 1)
        {
            $flag = 'used';
        }
        else{
            $flag = 'valid';
        }

    $data .='
    <tr>
        <td>'.$retrieve_coupon->coupons.'</td>
        <td>'.$retrieve_coupon->coupon_expiry.'</td>
        <td>'.$flag.'</td>
        <td> 
        <form action="" method="post" >
        <input type="hidden" name="coupon_id" value="'.$retrieve_coupon->id.'">
        <input type="submit" value ="Delete" name="delete_coupon" >
        </form>
        </td>
    </tr>
';
    }
}
    $data.='
    </tbody></table>';
    
  $get_coupon_videos =  $wpdb->get_results("Select * from wp_video_coupon");

  

$data.='

    <br>
    <br>
    <h2> Coupon Video Entries </h2>
    <br> 
    <table id="example1" class="table table-striped table-bordered" style="width:100%">
              
    <thead>
 <tr>
     <th>Coupons Code</th>
     <th>Video Name</th>
    
    
 </tr>
</thead>
<tbody>
    '; 
    if($get_coupon_videos)
    {
    foreach ($get_coupon_videos as $get_coupon_video){
         
    $data .='
    <tr>
        <td>'.$get_coupon_video->coupons.'</td>
        <td>'.get_the_title($get_coupon_video->video_id).'</td>
        
    </tr>
';
    }
}
    $data.='
    </tbody></table>';


    
    echo $data;
    
   //  $path = plugin_dir_url( __FILE__ ) . 'js/new.js';
   //  echo $path;
}


//script to create datatables
function my_scripts_plugin() {
  
    wp_enqueue_script( 'new-1', get_site_url() . '/wp-content/plugins/easycoupons/easycoupons.js', array( 'jquery' ),'',true );
  }
  add_action( 'admin_enqueue_scripts', 'my_scripts_plugin' );


?>