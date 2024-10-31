<?php
/*
Plugin Name: Piccshare for Wordpress
Plugin URI: http://www.piccshare.com/
Description: Piccshare is a sharing button that will be rendered on top of each photo and will expose social share buttons to enable sharing the photo link to facebook, twitter and by email.
Version: 1.0
Author: Piccshare
Author URI: http://www.piccshare.com
*/

// ------------------------------------------------------------------------
// REQUIRE MINIMUM VERSION OF WORDPRESS:
// ------------------------------------------------------------------------
// THIS IS USEFUL IF YOU REQUIRE A MINIMUM VERSION OF WORDPRESS TO RUN YOUR
// PLUGIN. IN THIS PLUGIN THE WP_EDITOR() FUNCTION REQUIRES WORDPRESS 3.3
// OR ABOVE. ANYTHING LESS SHOWS A WARNING AND THE PLUGIN IS DEACTIVATED.
// ------------------------------------------------------------------------

function psfwp_requires_wordpress_version() {
    global $wp_version;
    $plugin = plugin_basename( __FILE__ );
    $plugin_data = get_plugin_data( __FILE__, false );

    if ( version_compare($wp_version, "2.7", "<" ) ) {
        if( is_plugin_active($plugin) ) {
            deactivate_plugins( $plugin );
            wp_die( "'".$plugin_data['Name']."' requires WordPress 2.7 or higher, and has been deactivated! Please upgrade WordPress and try again.<br /><br />Back to <a href='".admin_url()."'>WordPress admin</a>." );
        }
    }
}
add_action( 'admin_init', 'psfwp_requires_wordpress_version' );

// ------------------------------------------------------------------------
// PLUGIN PREFIX:
// ------------------------------------------------------------------------
// A PREFIX IS USED TO AVOID CONFLICTS WITH EXISTING PLUGIN FUNCTION NAMES.
// WHEN CREATING A NEW PLUGIN, CHANGE THE PREFIX AND USE YOUR TEXT EDITORS
// SEARCH/REPLACE FUNCTION TO RENAME THEM ALL QUICKLY.
// ------------------------------------------------------------------------

// 'psfwp_' prefix is derived from [p]picc [s]share [f]for [w]word [p]press

// ------------------------------------------------------------------------
// REGISTER HOOKS & CALLBACK FUNCTIONS:
// ------------------------------------------------------------------------
// HOOKS TO SETUP DEFAULT PLUGIN OPTIONS, HANDLE CLEAN-UP OF OPTIONS WHEN
// PLUGIN IS DEACTIVATED AND DELETED, INITIALISE PLUGIN, ADD OPTIONS PAGE.
// ------------------------------------------------------------------------

// Set-up Action and Filter Hooks
register_activation_hook(__FILE__, 'psfwp_add_defaults');
register_uninstall_hook(__FILE__, 'psfwp_delete_plugin_options');
add_action('admin_init', 'psfwp_init' );
add_action('admin_menu', 'psfwp_add_options_page');
add_filter( 'plugin_action_links', 'psfwp_plugin_action_links', 10, 2 );

// --------------------------------------------------------------------------------------
// CALLBACK FUNCTION FOR: register_uninstall_hook(__FILE__, 'psfwp_delete_plugin_options')
// --------------------------------------------------------------------------------------
// THIS FUNCTION RUNS WHEN THE USER DEACTIVATES AND DELETES THE PLUGIN. IT SIMPLY DELETES
// THE PLUGIN OPTIONS DB ENTRY (WHICH IS AN ARRAY STORING ALL THE PLUGIN OPTIONS).
// --------------------------------------------------------------------------------------

// Delete options table entries ONLY when plugin deactivated AND deleted
function psfwp_delete_plugin_options() {
    delete_option('psfwp_options');
}

// ------------------------------------------------------------------------------
// CALLBACK FUNCTION FOR: add_action('admin_init', 'psfwp_init' )
// ------------------------------------------------------------------------------
// THIS FUNCTION RUNS WHEN THE 'admin_init' HOOK FIRES, AND REGISTERS YOUR PLUGIN
// SETTING WITH THE WORDPRESS SETTINGS API. YOU WON'T BE ABLE TO USE THE SETTINGS
// API UNTIL YOU DO.
// ------------------------------------------------------------------------------

// Init plugin options to white list our options
function psfwp_init(){
    register_setting( 'psfwp_plugin_options', 'psfwp_options', 'psfwp_validate_options' );
}

// ------------------------------------------------------------------------------
// CALLBACK FUNCTION FOR: add_action('admin_menu', 'psfwp_add_options_page');
// ------------------------------------------------------------------------------
// THIS FUNCTION RUNS WHEN THE 'admin_menu' HOOK FIRES, AND ADDS A NEW OPTIONS
// PAGE FOR YOUR PLUGIN TO THE SETTINGS MENU.
// ------------------------------------------------------------------------------

// Add menu page
function psfwp_add_options_page() {
    add_options_page('Piccshare Options Page', 'Piccshare for Wordpress', 'manage_options', __FILE__, 'psfwp_render_form');
}

// ------------------------------------------------------------------------------
// CALLBACK FUNCTION FOR: register_activation_hook(__FILE__, 'psfwp_add_defaults')
// ------------------------------------------------------------------------------
// THIS FUNCTION RUNS WHEN THE PLUGIN IS ACTIVATED. IF THERE ARE NO THEME OPTIONS
// CURRENTLY SET, OR THE USER HAS SELECTED THE CHECKBOX TO RESET OPTIONS TO THEIR
// DEFAULTS THEN THE OPTIONS ARE SET/RESET.
//
// OTHERWISE, THE PLUGIN OPTIONS REMAIN UNCHANGED.
// ------------------------------------------------------------------------------

// Define default option settings
function psfwp_add_defaults() {
	$tmp = get_option('psfwp_options');
	if(($tmp['chk_default_options_db']=='1')||(!is_array($tmp))) {
		delete_option('psfwp_options'); // so we don't have to reset all the 'off' checkboxes too! (don't think this is needed but leave for now)
		$arr = array(						"chk_show_btn_fb" => "1",
											"chk_show_btn_twitter" => "1",
											"chk_show_btn_email" => "1",
											"drp_menu_pos" => '"piccshare_top"',
											"txt_domain_id" => "",
											"chk_default_options_db" => ""
		);
		update_option('psfwp_options', $arr);
	}
}

// ------------------------------------------------------------------------------
// CALLBACK FUNCTION SPECIFIED IN: add_options_page()
// ------------------------------------------------------------------------------
// THIS FUNCTION IS SPECIFIED IN add_options_page() AS THE CALLBACK FUNCTION THAT
// ACTUALLY RENDER THE PLUGIN OPTIONS FORM AS A SUB-MENU UNDER THE EXISTING
// SETTINGS ADMIN MENU.
// ------------------------------------------------------------------------------

// Render the Plugin options form
function psfwp_render_form() {
    ?>
<div class="wrap">

    <!-- Display Plugin Icon, Header, and Description -->
    <div class="icon32" id="icon-options-general"><br></div>
    <h2>Piccshare for Wordpress</h2>

    <!-- Beginning of the Plugin Options Form -->
    <form method="post" action="options.php">
        <?php settings_fields('psfwp_plugin_options'); ?>
        <?php $options = get_option('psfwp_options'); ?>

        <!-- Table Structure Containing Form Controls -->
        <!-- Each Plugin Option Defined on a New Table Row -->

        <table class="form-table">

			<!-- Textbox Control -->
			<tr>
				<th scope="row">Piccshare Domain ID</th>
				<td>
					<input type="text" size="57" name="psfwp_options[txt_domain_id]" value="<?php echo $options['txt_domain_id']; ?>" />
				</td>
			</tr>

            <!-- Select Drop-Down Control -->
            <tr>
                <th scope="row">Share Menu Position</th>
                <td>
                    <select name='psfwp_options[drp_menu_pos]'>
                        <option value='"piccshare_top"' 			<?php selected('"piccshare_top"',     $options['drp_menu_pos']); ?>>Top</option>
                        <option value='"piccshare_bottom"' 			<?php selected('"piccshare_bottom"',  $options['drp_menu_pos']); ?>>Bottom</option>
                    </select>
                </td>
            </tr>

            <!-- Checkbox Buttons -->
            <tr valign="top">
                <th scope="row">Social Buttons</th>
                <td>
                    <!-- Facebook -->
                    <label><input name="psfwp_options[chk_show_btn_fb]"         type="checkbox" value="1" <?php if (isset($options['chk_show_btn_fb']))			{ checked('1', $options['chk_show_btn_fb']); 		} ?> /> Show Facebook Button</label><br />

                    <!-- Twitter -->
                    <label><input name="psfwp_options[chk_show_btn_twitter]"    type="checkbox" value="1" <?php if (isset($options['chk_show_btn_twitter']))	{ checked('1', $options['chk_show_btn_twitter']); 	} ?> /> Show Twitter Button</label><br />

                    <!-- Email -->
                    <label><input name="psfwp_options[chk_show_btn_email]"      type="checkbox" value="1" <?php if (isset($options['chk_show_btn_email']))		{ checked('1', $options['chk_show_btn_email']); 	} ?> /> Show Email Button</label><br />
                </td>
            </tr>

            <tr><td colspan="2"><div style="margin-top:10px;"></div></td></tr>
            <tr valign="top" style="border-top:#dddddd 1px solid;">
                <th scope="row">Database Options</th>
                <td>
                    <label><input name="psfwp_options[chk_default_options_db]" type="checkbox" value="1" <?php if (isset($options['chk_default_options_db'])) { checked('1', $options['chk_default_options_db']); } ?> /> Restore defaults upon plugin deactivation/reactivation</label>
                    <br /><span style="color:#666666;margin-left:2px;">Only check this if you want to reset plugin settings upon Plugin reactivation</span>
                </td>
            </tr>
        </table>
        <p class="submit">
            <input type="submit" class="button-primary" value="<?php _e('Save Changes') ?>" />
        </p>
    </form>

</div>
<?php
}

// Sanitize and validate input. Accepts an array, return a sanitized array.
function psfwp_validate_options($input) {
	$input['txt_domain_id'] =  wp_filter_nohtml_kses($input['txt_domain_id']); // Sanitize textarea input (strip html tags, and escape characters)
    return $input;
}

// Display a Settings link on the main Plugins page
function psfwp_plugin_action_links( $links, $file ) {

    if ( $file == plugin_basename( __FILE__ ) ) {
        $psfwp_links = '<a href="'.get_admin_url().'options-general.php?page=piccshare/piccshare.php">'.__('Settings').'</a>';
        // make the 'Settings' link appear first
        array_unshift( $links, $psfwp_links );
    }

    return $links;
}

// ------------------------------------------------------------------------------
// SAMPLE USAGE FUNCTIONS:
// ------------------------------------------------------------------------------
// THE FOLLOWING FUNCTIONS SAMPLE USAGE OF THE PLUGINS OPTIONS DEFINED ABOVE. TRY
// CHANGING THE DROPDOWN SELECT BOX VALUE AND SAVING THE CHANGES. THEN REFRESH
// A PAGE ON YOUR SITE TO SEE THE UPDATED VALUE.
// ------------------------------------------------------------------------------

add_action('wp_head','piccshare_inject');

function piccshare_inject() {
	$options = get_option('psfwp_options');
    $domainId = $options['txt_domain_id'];
	if ($domainId != "") {
?>
		<script type="text/javascript">
			var g_piccshare_settings = g_piccshare_settings || {};
			g_piccshare_settings.settings = g_piccshare_settings.settings || {};
			g_piccshare_settings.settings.di_facebook_share = 	<?php if (isset($options['chk_show_btn_fb']))			{if ($options['chk_show_btn_fb']) 		{echo 'true';} else {echo 'false';} } else { echo 'false'; } ?>;
			g_piccshare_settings.settings.di_twitter = 			<?php if (isset($options['chk_show_btn_twitter']))		{if ($options['chk_show_btn_twitter']) 	{echo 'true';} else {echo 'false';} } else { echo 'false'; } ?>;
			g_piccshare_settings.settings.di_email = 			<?php if (isset($options['chk_show_btn_email']))		{if ($options['chk_show_btn_email']) 	{echo 'true';} else {echo 'false';} } else { echo 'false'; } ?>;
			g_piccshare_settings.settings.bu_button_position = 	<?php echo $options['drp_menu_pos']; ?>;
		</script>

		<script src="http://plugin.piccshare.com/piccshare.php?domainId=<?php echo $domainId; ?>&appId=wp_v1.0" type='text/javascript'></script>
<?php
	}
}

?>