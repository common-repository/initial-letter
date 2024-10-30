<?php
/*
Plugin Name: Initial Letter
Plugin URI: https://wordpress.org/plugins/initial-letter/
Description: This plugin allows you to style the first letter of each paragraph separately than the rest of the post to create the popular drop caps look.
Version: 2.3
Author: Garrett Grimm
Author URI: http://www.grimmdude.com
*/

/*  Copyright 2021  Garrett Grimm  (email : garrett@grimmdude.com)

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

if (!class_exists('InitialLetter')) {
	class InitialLetter
	{
		private $options;

		const VERSION = '2.2';
		public function __construct()
		{
			$this->options =  get_option('initial_letter_plugin');

			// Set default options if needed
			if (!is_array($this->options)) {
				$options = array(
							'default' 		=> 0,
							'font'			=> '(Match Current Font)',
							'size'			=> 40,
							'right_padding'	=> 0,
							'line_height'	=> 1,
							'first_p'		=> 'true',
							'color'			=> '#233190'
					);

				update_option('initial_letter_plugin', $options);
				$this->options =  get_option('initial_letter_plugin');
			}

			//register_activation_hook(__FILE__, array($this, 'pluginActivate'));
			add_action('wp_head', array($this, 'head'));
			add_action('admin_menu', array($this, 'adminPage'));
			add_action('admin_init', array($this, 'adminInit'));

			// Wrap the initial-letter class to content
			add_filter('the_content', array($this, 'contentFilter'));

			// Wrap the initial-letter class to excerpt
			add_filter('the_excerpt', array($this, 'excerptFilter'));
		}

		public function head()
		{
			$firstP = isset($this->options['first_p']) ? ':first-of-type' : '';
			?>
			<!-- Initial Letter Wordpress Plugin https://wordpress.org/plugins/initial-letter/ -->
			<style type="text/css">
				.initial-letter p<?php echo $firstP; ?>:first-letter {
						font-size:<?php echo $this->options['size']; ?>px;
						<?php echo $this->options['font'] != "(Match Current Font)" ? "font-family:'".$this->options['font']."';" : "" ; ?>
						color:<?php echo $this->options['color']; ?>;
						float:left;
						line-height:<?php echo $this->options['line_height']; ?>px;
						padding-right:<?php echo $this->options['right_padding']; ?>px;}
			</style>
			<?php
		}


		public function adminInit()
		{
			add_action('admin_enqueue_scripts', array($this, 'adminScripts'));

			add_action('add_meta_boxes', array($this, 'metaBox'), 1, 2);
			add_action('save_post', array($this, 'savePost'));

			register_setting('initial_letter_plugin', 'initial_letter_plugin', array($this, 'sanitize'));
			add_settings_section('initial_letter_plugin_options', '', array($this, 'optionsPageSectionText'), 'initial_letter_plugin');
			add_settings_field('initial_letter_plugin_default', 'Default for Posts', array($this, 'defaultSetting'), 'initial_letter_plugin', 'initial_letter_plugin_options');
			add_settings_field('initial_letter_plugin_font', 'Font', array($this, 'fontFamilySelect'), 'initial_letter_plugin', 'initial_letter_plugin_options');
			add_settings_field('initial_letter_plugin_size', 'Size', array($this, 'fontSizeSelect'), 'initial_letter_plugin', 'initial_letter_plugin_options');
			add_settings_field('initial_letter_plugin_right_padding', 'Right Padding', array($this, 'fontPaddingSelect'), 'initial_letter_plugin', 'initial_letter_plugin_options');
			add_settings_field('initial_letter_plugin_line_height', 'Vertical Alignment', array($this, 'fontLineHeightSelect'), 'initial_letter_plugin', 'initial_letter_plugin_options');
			add_settings_field('initial_letter_plugin_color', 'Color', array($this, 'fontColorSelect'), 'initial_letter_plugin', 'initial_letter_plugin_options');
			add_settings_field('initial_letter_plugin_excerpts', 'Enable for Excerpts', array($this, 'enableForExcerpts'), 'initial_letter_plugin', 'initial_letter_plugin_options');
			add_settings_field('initial_letter_plugin_first_p', 'First Paragraph Only', array($this, 'fontFirstParagraph'), 'initial_letter_plugin', 'initial_letter_plugin_options');
			//add_settings_field('initial_letter_plugin_content_class', 'Content CSS Class', array($this, 'contentClass'), 'initial_letter_plugin', 'initial_letter_plugin_options');

			//add_settings_section('initial_letter_plugin_preview', 'Preview', array($this, 'optionsPagePreviewText'), 'initial_letter_plugin');

			// Add settings link on plugin page
			add_filter('plugin_action_links_' . plugin_basename(__FILE__), function ($links) {
				$links[] = '<a href="' . get_admin_url() . 'options-general.php?page=initial-letter">Settings</a>';
				return $links;
			});
		}


		public function adminScripts()
		{
			wp_enqueue_style('wp-color-picker');
			wp_enqueue_script('initial_letter_js', plugins_url('js/admin.js', __FILE__), array('wp-color-picker'), false, true);
		}


		public function adminPage()
		{
			add_options_page('Initial Letter', 'Initial Letter', 'manage_options', 'initial-letter', array($this, 'optionsPageContent'));
		}


		public function optionsPageSectionText()
		{
			//echo 'hi';
		}


		public function optionsPageContent()
		{
			?>
			<div class="wrap">
				<h2>Initial Letter</h2>
				<p>
					This is version <?php echo self::VERSION; ?> <a href="https://wordpress.org/plugins/initial-letter/" title="Feedback &amp; Help" target="_blank">Feedback &amp; Help</a> |  
					<a href="http://www.grimmdude.com/donate/" title="Donate" target="_blank">Donate</a><br />
					<small>By <a href="http://www.grimmdude.com" title="www.grimmdude.com" target="_blank">Garrett Grimm</a></small>
				</p>
				<form method="post" action="options.php">
					<?php settings_fields('initial_letter_plugin'); ?>
					<?php do_settings_sections('initial_letter_plugin'); ?>
					<?php submit_button(); ?>
				</form>
			</div>
			<?php
		}

		public function optionsPagePreviewText()
		{
			?>
				<style type="text/css">
					#initial-letter-preview p:first-letter {
							font-size:<?php echo $this->options['size']; ?>px;
							<?php echo $this->options['font'] != "(Match Current Font)" ? "font-family:'".$this->options['font']."';" : "" ; ?>
							color:<?php echo $this->options['color']; ?>;
							float:left;
							line-height:<?php echo $this->options['line_height']; ?>px;
							padding-right:<?php echo $this->options['right_padding']; ?>px;}
				</style>
				<div id="initial-letter-preview" style="min-height:50px;">
					<p></p>
				</div>
				<textarea style="width:100%;" rows="5" id="initial-letter-preview-input">In illuminated manuscripts, initials with images inside them, such as those illustrated here, are known as historiated initials. They were an invention of the Insular art of the British Isles in the eighth century. Initials containing, typically, plant-form spirals with small figures of animals or humans that do not represent a specific person or scene are known as "inhabited" initials. Certain important initials, such as the B of Beatus vir... at the opening of Psalm 1 at the start of a vulgate Latin psalter, could occupy a whole page of a manuscript.</textarea>
			<?php
		}


		public function defaultSetting()
		{
			?>
				<select name="initial_letter_plugin[default]">
					<option value="1" <?php echo (int) $this->options['default'] === 1 ? 'selected="selected"' : ''; ?>>Yes</option>
					<option value="0" <?php echo (int) $this->options['default'] === 0 ? 'selected="selected"' : ''; ?>>No</option>
				</select>
				<p class="description">
					Select whether you want initial letter to default on new posts (You can change for each post in the post editor).
				</p>
			<?php
		}


		public function fontFamilySelect()
		{
			$fonts = array('(Match Current Font)', 'Arial','Georgia','Impact','Tahoma','Times New Roman','Trebuchet MS','Verdana');
			?>
				<select name="initial_letter_plugin[font]">
					<?php foreach ($fonts as $font) : ?>
						<option value="<?php echo $font; ?>" <?php echo $font == $this->options['font'] ? 'selected="selected"' : ''; ?>><?php echo $font; ?></option>
					<?php endforeach ?>
				</select>
			<?php
		}


		public function fontSizeSelect()
		{
			$values = range(1, 120);
			?>
				<select name="initial_letter_plugin[size]">
				<?php foreach ($values as $value) : ?>
					<option value="<?php echo $value; ?>" <?php echo $value == $this->options['size'] ? 'selected="selected"' : ''; ?>><?php echo $value; ?>px</option>
				<?php endforeach; ?>
				</select>
			<?php
		}


		public function fontColorSelect()
		{
			?>
				<input type='text' name="initial_letter_plugin[color]" value='<?php echo $this->options['color']; ?>' id='initial_letter_plugin_color_picker' />
			<?php
		}


		public function fontPaddingSelect()
		{
			$values = range(0, 120);
			?>
				<select name="initial_letter_plugin[right_padding]">
				<?php foreach ($values as $value) : ?>
					<option value="<?php echo $value; ?>" <?php echo $value == $this->options['right_padding'] ? 'selected="selected"' : ''; ?>><?php echo $value; ?>px</option>
				<?php endforeach; ?>
				</select>
				<p class="description">If the letter looks off horizintally try increasing or decreasing the right padding.</p>
			<?php
		}


		public function fontLineHeightSelect()
		{
			$values = range(0, 120);
			?>
				<select name="initial_letter_plugin[line_height]">
				<?php foreach ($values as $value) : ?>
					<option value="<?php echo $value; ?>" <?php echo $value == $this->options['line_height'] ? 'selected="selected"' : ''; ?>><?php echo $value; ?>px</option>
				<?php endforeach; ?>
				</select>
				<p class="description">If the letter looks too high try increasing the value of this option to bring it down.</p>
			<?php
		}


		public function enableForExcerpts()
		{
			?>
			<label for="excerpts_enable">
				<input type="checkbox" name="initial_letter_plugin[excerpts]" id="excerpts_enable" value="true" <?php echo isset($this->options['excerpts']) ? 'checked="checked"' : ''; ?>/>
				If checked, initial letter will be enabled for excerpts.
			</label>
			<?php
		}

		public function fontFirstParagraph()
		{
			?>
			<label for="first_p">
				<input type="checkbox" name="initial_letter_plugin[first_p]" id="first_p" value="true" <?php echo isset($this->options['first_p']) ? 'checked="checked"' : ''; ?>/>
				If checked, only the first paragraph in a post or page will have the big boy letter.
			</label>
			<?php
		}


		public function contentClass()
		{
			?>
			<input type="text" name="initial_letter_plugin[content_class]" value="<?php echo ! empty($this->options['content_class']) ? $this->options['content_class'] : 'entry-content'; ?>" />
			<p class="description">The css class that immediately surrounds your post content.  <code>entry-content</code> should work for most themes.</p>
			<?php
		}


		public function sanitize($input)
		{
			return array_map('strip_tags', $input);
		}


		public function metaBox($post_type, $post)
		{
			add_meta_box('initial-letter-meta-box', 'Initial Letter', array($this, 'addMetaBox'));
		}


		public function addMetaBox($post)
		{
			$enable = get_post_meta($post->ID, 'initial_letter_enable', true);

			if (empty($enable) && ((int) $this->options['default'] === 1 || empty($this->options['default']))) {
				$enable = 1;
			}

			wp_nonce_field('initial_letter_nonce', 'initial_letter_nonce');
			?>
				<select name="initial_letter_enable">
					<option value="1" <?php echo (int) $enable === 1 ? 'selected="selected"' : ''; ?>>Yes</option>
					<option value="0" <?php echo (int) $enable === 0 ? 'selected="selected"' : ''; ?>>No</option>
				</select>
				<p><a href="<?php echo get_admin_url(); ?>options-general.php?page=initial-letter" target="_blank">Adjust global settings</a></p>
			<?php
		}


		public function savePost($post_id)
		{
			if (isset($_POST['initial_letter_nonce']) && $_POST['initial_letter_enable'] && wp_verify_nonce($_POST['initial_letter_nonce'], 'initial_letter_nonce')) {
				update_post_meta($post_id, 'initial_letter_enable', $_POST['initial_letter_enable']);
			}
		}


		public function pluginActivate()
		{
			add_action('admin_notices', function () {
				?>
					<div class="updated notice is-dismissible">hi there</div>
				<?php
			});
		}


		public function contentFilter($content)
		{
			global $post;

			$enable = get_post_meta($post->ID, 'initial_letter_enable', true);

			if ((int) $enable === 1 || empty($enable) && (int) $this->options['default'] === 1) {
				return '<div class="initial-letter">' . $content . '</div>';
			}

			return $content;
		}


		public function excerptFilter($excerpt)
		{
			global $post;

			$enable = get_post_meta($post->ID, 'initial_letter_enable', true);

			if ((int) $enable === 1 || empty($enable) && (int) $this->options['default'] === 1) {
				if (isset($this->options['excerpts'])) {
					return '<div class="initial-letter">' . $excerpt . '</div>';
				}
			}

			return $excerpt;
		}
	}

	// Let her rip
	new InitialLetter;
}