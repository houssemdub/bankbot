<?php
/**
 * Plugin Name: BankBot - AI Islamic Banking Assistant
 * Description: AI chatbot with multi-language support, streaming, custom contexts, and advanced UI
 * Version: 3.2.0
 * Author: mohamed houssem eddine saighi - claude sonnet 4.5
 * License: GPL v2 or later
 */
if (!defined('ABSPATH')) {
    exit;
}

// Activation hook
register_activation_hook(__FILE__, array('BankBot_Plugin', 'activate'));

class BankBot_Plugin {
    private static $instance = null;
    private $option_name = 'bankbot_settings';
    public static function instance() {
        if (null === self::$instance) {
            self::$instance = new self();
        }
        return self::$instance;
    }
    private function __construct() {
        add_action('admin_menu', array($this, 'add_menu'));
        add_action('admin_init', array($this, 'register_settings'));
        add_action('admin_enqueue_scripts', array($this, 'admin_assets'));
        add_action('wp_enqueue_scripts', array($this, 'frontend_assets'));
        add_action('wp_footer', array($this, 'render_widget'));
        add_action('wp_ajax_bankbot_msg', array($this, 'ajax_handler'));
        add_action('wp_ajax_nopriv_bankbot_msg', array($this, 'ajax_handler'));
        add_action('wp_ajax_bankbot_stream', array($this, 'stream_handler'));
        add_action('wp_ajax_nopriv_bankbot_stream', array($this, 'stream_handler'));
        add_action('wp_ajax_store_conversation', array($this, 'store_conversation_ajax'));
        add_action('wp_ajax_nopriv_store_conversation', array($this, 'store_conversation_ajax'));
        // New AJAX action for filtered analytics
        add_action('wp_ajax_bankbot_get_filtered_analytics', array($this, 'ajax_get_filtered_analytics'));
		// New AJAX action for Table of all Conversations
		add_action('wp_ajax_bankbot_get_all_conversations', array($this, 'ajax_get_all_conversations'));
		
		add_action('wp_ajax_bankbot_export_csv', array($this, 'export_conversations_csv'));
		add_action('wp_ajax_bankbot_import_csv', array($this, 'import_conversations_csv'));
		add_action('wp_ajax_bankbot_delete_conversations', array($this, 'delete_conversations_ajax'));
		add_action('wp_ajax_bankbot_save_blacklist', array($this, 'save_ip_blacklist'));
		add_action('wp_ajax_bankbot_check_spam', array($this, 'check_spam'));
    }
    public static function activate() {
        self::create_conversations_table();
    }
    private static function create_conversations_table() {
		global $wpdb;
		$table_name = $wpdb->prefix . 'bankbot_conversations';
		$charset_collate = $wpdb->get_charset_collate();
		$sql = "CREATE TABLE $table_name (
			id BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT,
			session_id VARCHAR(255) NOT NULL,
			user_id BIGINT(20) UNSIGNED,
			user_message TEXT NOT NULL,
			bot_response TEXT NOT NULL,
			ip_address VARCHAR(45),
			hostname VARCHAR(255),
			created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
			PRIMARY KEY (id)
		) $charset_collate;";
		require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
		dbDelta($sql);
	}
    private function defaults() {
        return array(
            'enabled' => 1,
            'bot_name' => 'BankBot',
            'welcome_en' => 'Hello! How can I help you today?',
            'welcome_ar' => 'مرحبا! كيف يمكنني مساعدتك اليوم؟',
            'placeholder_en' => 'Type your message...',
            'placeholder_ar' => 'اكتب رسالتك...',
            'language' => 'en',
            'demo_mode' => 1,
            'api_key' => '',
            'model' => 'openai',
            'stream_enabled' => 1,
            'context_en' => 'You are a helpful banking assistant.',
            'context_ar' => 'أنت مساعد مصرفي مفيد.',
            'context_products' => '',
            'context_services' => '',
            'context_policies' => '',
            // Colors
            'primary' => '#0066cc',
            'secondary' => '#ffffff',
            'text' => '#333333',
            'header_bg' => '#0066cc',
            'header_text' => '#ffffff',
            'user_bubble' => '#0066cc',
            'user_text' => '#ffffff',
            'bot_bubble' => '#f0f0f0',
            'bot_text' => '#333333',
            'input_border' => '#e0e0e0',
            'send_button' => '#0066cc',
            'input_area_bg' => '#ffffff',
            'toggle_bg' => '#0066cc',
            'messages_bg' => '#f5f5f5',
            // Layout
            'position' => 'bottom-right',
            'btn_size' => 60,
            'width' => 380,
            'height' => 600,
            'border_radius' => 16,
            'message_radius' => 18,
            // Typography
            'font_family' => 'system',
            'font_size' => 15,
            'header_font_size' => 18,
            'title_weight' => '600',
            'message_line_height' => '1.6',
            // Icon
            'icon_type' => 'emoji',
            'icon_emoji' => '💬',
            'icon_text' => 'Chat',
            'icon_url' => '',
            'show_icon_in_header' => 1,
            // Advanced
            'header_height' => 70,
            'input_height' => 60,
            'shadow_intensity' => 'medium',
            'animation_speed' => 'normal',
			// Anti-Spam settings
			'enable_antispam' => 1,
			'rate_limit_messages' => 5,
			'rate_limit_time' => 60
        );
    }
	// Export conversations to CSV
		public function export_conversations_csv() {
			check_ajax_referer('bb_nonce', 'nonce');
    
			if (!current_user_can('manage_options')) {
				wp_die('Unauthorized');
			}
			
			global $wpdb;
			$table_name = $wpdb->prefix . 'bankbot_conversations';
			
			$conversations = $wpdb->get_results("SELECT * FROM $table_name ORDER BY created_at DESC");
			
			header('Content-Type: text/csv; charset=utf-8');
			header('Content-Disposition: attachment; filename=bankbot_conversations_' . date('Y-m-d') . '.csv');
			
			$output = fopen('php://output', 'w');
			fputcsv($output, array('ID', 'Session ID', 'User ID', 'User Message', 'Bot Response', 'IP Address', 'Hostname', 'Created At'));
			
			foreach ($conversations as $conv) {
				fputcsv($output, array(
					$conv->id,
					$conv->session_id,
					$conv->user_id,
					$conv->user_message,
					$conv->bot_response,
					isset($conv->ip_address) ? $conv->ip_address : '',
					isset($conv->hostname) ? $conv->hostname : '',
					$conv->created_at
				));
			}
			
			fclose($output);
			exit;
		}

		// Import conversations from CSV
		public function import_conversations_csv() {
			check_ajax_referer('bb_nonce', 'nonce');
			
			if (!current_user_can('manage_options')) {
				wp_send_json_error(array('msg' => 'Unauthorized'));
				return;
			}
			
			if (!isset($_FILES['csv_file'])) {
				wp_send_json_error(array('msg' => 'No file uploaded'));
				return;
			}
			
			$file = $_FILES['csv_file'];
			$handle = fopen($file['tmp_name'], 'r');
			
			if ($handle === false) {
				wp_send_json_error(array('msg' => 'Could not open file'));
				return;
			}
			
			global $wpdb;
			$table_name = $wpdb->prefix . 'bankbot_conversations';
			
			// Skip header row
			fgetcsv($handle);
			
			$imported = 0;
			while (($data = fgetcsv($handle)) !== false) {
				if (count($data) >= 7) {
					$wpdb->insert($table_name, array(
						'session_id' => $data[1],
						'user_id' => intval($data[2]),
						'user_message' => $data[3],
						'bot_response' => $data[4],
						'ip_address' => $data[5],
						'created_at' => $data[6]
					));
					$imported++;
				}
			}
			
			fclose($handle);
			wp_send_json_success(array('msg' => "Imported $imported conversations"));
		}

		// Delete conversations
		public function delete_conversations_ajax() {
			check_ajax_referer('bb_nonce', 'nonce');
			
			if (!current_user_can('manage_options')) {
				wp_send_json_error(array('msg' => 'Unauthorized'));
				return;
			}
			
			global $wpdb;
			$table_name = $wpdb->prefix . 'bankbot_conversations';
			
			$delete_type = isset($_POST['delete_type']) ? sanitize_text_field($_POST['delete_type']) : '';
			$start_date = isset($_POST['start_date']) ? sanitize_text_field($_POST['start_date']) : '';
			$end_date = isset($_POST['end_date']) ? sanitize_text_field($_POST['end_date']) : '';
			
			if ($delete_type === 'all') {
				$deleted = $wpdb->query("DELETE FROM $table_name");
			} elseif ($delete_type === 'date_range' && !empty($start_date) && !empty($end_date)) {
				$deleted = $wpdb->query($wpdb->prepare(
					"DELETE FROM $table_name WHERE created_at BETWEEN %s AND %s",
					$start_date, $end_date
				));
			} else {
				wp_send_json_error(array('msg' => 'Invalid delete parameters'));
				return;
			}
			
			wp_send_json_success(array('msg' => "Deleted $deleted conversations"));
		}

		// Save IP blacklist
		public function save_ip_blacklist() {
			check_ajax_referer('bb_nonce', 'nonce');
			
			if (!current_user_can('manage_options')) {
				wp_send_json_error(array('msg' => 'Unauthorized'));
				return;
			}
			
			$blacklist = isset($_POST['blacklist']) ? sanitize_textarea_field($_POST['blacklist']) : '';
			update_option('bankbot_ip_blacklist', $blacklist);
			
			wp_send_json_success(array('msg' => 'IP blacklist saved'));
		}

		// Check if IP is blacklisted
		private function is_ip_blacklisted($ip) {
			$blacklist = get_option('bankbot_ip_blacklist', '');
			$blocked_ips = array_filter(array_map('trim', explode("\n", $blacklist)));
			return in_array($ip, $blocked_ips);
		}

		// Anti-spam check
		// Anti-spam check
		public function check_spam() {
			$s = $this->get_settings();
			
			// Check if anti-spam is enabled
			if (!$s['enable_antispam']) {
				return array('blocked' => false);
			}
			
			$ip = isset($_SERVER['REMOTE_ADDR']) ? $_SERVER['REMOTE_ADDR'] : '';
			
			// Check blacklist
			if ($this->is_ip_blacklisted($ip)) {
				return array('blocked' => true, 'reason' => 'IP blacklisted');
			}
			
			// Check rate limiting
			$cache_key = 'bankbot_rate_' . md5($ip);
			$requests = get_transient($cache_key);
			
			$max_requests = intval($s['rate_limit_messages']);
			$time_window = intval($s['rate_limit_time']);
			
			if ($requests === false) {
				set_transient($cache_key, 1, $time_window);
			} elseif ($requests >= $max_requests) {
				return array('blocked' => true, 'reason' => 'Rate limit exceeded');
			} else {
				set_transient($cache_key, $requests + 1, $time_window);
			}
			
			return array('blocked' => false);
		}

    public function get_settings() {
        $saved = get_option($this->option_name, array());
        return wp_parse_args($saved, $this->defaults());
    }
    public function add_menu() {
        add_menu_page(
            'BankBot',
            'BankBot',
            'manage_options',
            'bankbot',
            array($this, 'settings_page'),
            'dashicons-format-chat',
            30
        );
        // Add analytics submenu
        add_submenu_page(
            'bankbot',
            'Analytics Dashboard',
            'Analytics',
            'manage_options',
            'bankbot-analytics',
            array($this, 'analytics_dashboard')
        );
    }
    public function register_settings() {
        register_setting('bankbot_group', $this->option_name, array($this, 'sanitize'));
    }
    public function sanitize($input) {
        if (!is_array($input)) {
            return $this->defaults();
        }
        $clean = array();
        $clean['enabled'] = isset($input['enabled']) ? 1 : 0;
        $clean['demo_mode'] = isset($input['demo_mode']) ? 1 : 0;
        $clean['stream_enabled'] = isset($input['stream_enabled']) ? 1 : 0;
        $clean['show_icon_in_header'] = isset($input['show_icon_in_header']) ? 1 : 0;
        $clean['bot_name'] = isset($input['bot_name']) ? sanitize_text_field($input['bot_name']) : '';
        $clean['welcome_en'] = isset($input['welcome_en']) ? sanitize_text_field($input['welcome_en']) : '';
        $clean['welcome_ar'] = isset($input['welcome_ar']) ? sanitize_text_field($input['welcome_ar']) : '';
        $clean['placeholder_en'] = isset($input['placeholder_en']) ? sanitize_text_field($input['placeholder_en']) : '';
        $clean['placeholder_ar'] = isset($input['placeholder_ar']) ? sanitize_text_field($input['placeholder_ar']) : '';
        $clean['language'] = isset($input['language']) ? sanitize_text_field($input['language']) : 'en';
        $clean['api_key'] = isset($input['api_key']) ? sanitize_text_field($input['api_key']) : '';
        $clean['model'] = isset($input['model']) ? sanitize_text_field($input['model']) : 'openai';
        $clean['context_en'] = isset($input['context_en']) ? sanitize_textarea_field($input['context_en']) : '';
        $clean['context_ar'] = isset($input['context_ar']) ? sanitize_textarea_field($input['context_ar']) : '';
        $clean['context_products'] = isset($input['context_products']) ? sanitize_textarea_field($input['context_products']) : '';
        $clean['context_services'] = isset($input['context_services']) ? sanitize_textarea_field($input['context_services']) : '';
        $clean['context_policies'] = isset($input['context_policies']) ? sanitize_textarea_field($input['context_policies']) : '';
        $clean['position'] = isset($input['position']) ? sanitize_text_field($input['position']) : 'bottom-right';
        // Colors
        $clean['primary'] = isset($input['primary']) ? sanitize_hex_color($input['primary']) : '#0066cc';
        $clean['secondary'] = isset($input['secondary']) ? sanitize_hex_color($input['secondary']) : '#ffffff';
        $clean['text'] = isset($input['text']) ? sanitize_hex_color($input['text']) : '#333333';
        $clean['header_bg'] = isset($input['header_bg']) ? sanitize_hex_color($input['header_bg']) : '#0066cc';
        $clean['header_text'] = isset($input['header_text']) ? sanitize_hex_color($input['header_text']) : '#ffffff';
        $clean['user_bubble'] = isset($input['user_bubble']) ? sanitize_hex_color($input['user_bubble']) : '#0066cc';
        $clean['user_text'] = isset($input['user_text']) ? sanitize_hex_color($input['user_text']) : '#ffffff';
        $clean['bot_bubble'] = isset($input['bot_bubble']) ? sanitize_hex_color($input['bot_bubble']) : '#f0f0f0';
        $clean['bot_text'] = isset($input['bot_text']) ? sanitize_hex_color($input['bot_text']) : '#333333';
        $clean['input_border'] = isset($input['input_border']) ? sanitize_hex_color($input['input_border']) : '#e0e0e0';
        $clean['send_button'] = isset($input['send_button']) ? sanitize_hex_color($input['send_button']) : '#0066cc';
        $clean['input_area_bg'] = isset($input['input_area_bg']) ? sanitize_hex_color($input['input_area_bg']) : '#ffffff';
        $clean['toggle_bg'] = isset($input['toggle_bg']) ? sanitize_hex_color($input['toggle_bg']) : '#0066cc';
        $clean['messages_bg'] = isset($input['messages_bg']) ? sanitize_hex_color($input['messages_bg']) : '#f5f5f5';
        // Layout
        $clean['btn_size'] = isset($input['btn_size']) ? intval($input['btn_size']) : 60;
        $clean['width'] = isset($input['width']) ? intval($input['width']) : 380;
        $clean['height'] = isset($input['height']) ? intval($input['height']) : 600;
        $clean['border_radius'] = isset($input['border_radius']) ? intval($input['border_radius']) : 16;
        $clean['message_radius'] = isset($input['message_radius']) ? intval($input['message_radius']) : 18;
        $clean['header_height'] = isset($input['header_height']) ? intval($input['header_height']) : 70;
        $clean['input_height'] = isset($input['input_height']) ? intval($input['input_height']) : 60;
        // Typography
        $clean['font_family'] = isset($input['font_family']) ? sanitize_text_field($input['font_family']) : 'system';
        $clean['font_size'] = isset($input['font_size']) ? intval($input['font_size']) : 15;
        $clean['header_font_size'] = isset($input['header_font_size']) ? intval($input['header_font_size']) : 18;
        $clean['title_weight'] = isset($input['title_weight']) ? sanitize_text_field($input['title_weight']) : '600';
        $clean['message_line_height'] = isset($input['message_line_height']) ? sanitize_text_field($input['message_line_height']) : '1.6';
        // Icon
        $clean['icon_type'] = isset($input['icon_type']) ? sanitize_text_field($input['icon_type']) : 'emoji';
        $clean['icon_emoji'] = isset($input['icon_emoji']) ? sanitize_text_field($input['icon_emoji']) : '💬';
        $clean['icon_text'] = isset($input['icon_text']) ? sanitize_text_field($input['icon_text']) : 'Chat';
        $clean['icon_url'] = isset($input['icon_url']) ? esc_url_raw($input['icon_url']) : '';
        // Advanced
        $clean['shadow_intensity'] = isset($input['shadow_intensity']) ? sanitize_text_field($input['shadow_intensity']) : 'medium';
        $clean['animation_speed'] = isset($input['animation_speed']) ? sanitize_text_field($input['animation_speed']) : 'normal';
		// Anti-Spam
		$clean['enable_antispam'] = isset($input['enable_antispam']) ? 1 : 0;
		$clean['rate_limit_messages'] = isset($input['rate_limit_messages']) ? max(1, intval($input['rate_limit_messages'])) : 5;
		$clean['rate_limit_time'] = isset($input['rate_limit_time']) ? max(10, intval($input['rate_limit_time'])) : 60;

        return $clean;
    }
    public function admin_assets($hook) {
        if ('toplevel_page_bankbot' !== $hook) {
            return;
        }
        wp_enqueue_style('wp-color-picker');
        wp_enqueue_script('wp-color-picker');
        wp_enqueue_media();
        wp_add_inline_script('jquery', "
        jQuery(document).ready(function($) {
            // Color picker
            $('.bb-color').wpColorPicker();
            // Icon type toggle
            $('#bb-icon-type').on('change', function() {
                $('.bb-icon-option').hide();
                $('#bb-icon-' + $(this).val()).show();
            }).trigger('change');
            // Media uploader
            $('#bb-upload-icon').on('click', function(e) {
                e.preventDefault();
                var mediaUploader = wp.media({
                    title: 'Select Icon Image',
                    button: { text: 'Use This Image' },
                    multiple: false
                });
                mediaUploader.on('select', function() {
                    var attachment = mediaUploader.state().get('selection').first().toJSON();
                    $('#bb-icon-url-input').val(attachment.url);
                    $('#bb-icon-preview').html('<img src=\"' + attachment.url + '\" style=\"max-width:60px;max-height:60px;border-radius:50%;\" />');
                });
                mediaUploader.open();
            });
            // Tab navigation
            $('.bb-nav-item').on('click', function(e) {
                e.preventDefault();
                var targetTab = $(this).data('tab');
                $('.bb-nav-item').removeClass('active');
                $('.bb-section').removeClass('active');
                $(this).addClass('active');
                $('#' + targetTab).addClass('active');
            });
        });
        ");
    }
    public function frontend_assets() {
        $s = $this->get_settings();
        if (!$s['enabled']) {
            return;
        }
        wp_register_style('bankbot-css', false);
        wp_enqueue_style('bankbot-css');
        wp_add_inline_style('bankbot-css', $this->get_css($s));
        wp_register_script('bankbot-js', false, array('jquery'), '3.2.0', true);
        wp_enqueue_script('bankbot-js');
        wp_add_inline_script('bankbot-js', $this->get_js($s));
        $welcome = $s['language'] === 'ar' ? $s['welcome_ar'] : $s['welcome_en'];
        $newChatText = $s['language'] === 'ar' ? 'محادثة جديدة' : 'New Chat';
        wp_localize_script('bankbot-js', 'BankBotData', array(
            'ajaxUrl' => admin_url('admin-ajax.php'),
            'nonce' => wp_create_nonce('bb_nonce'),
            'welcome' => esc_js($welcome),
            'language' => $s['language'],
            'streamEnabled' => $s['stream_enabled'],
            'demoMode' => $s['demo_mode'],
            'newChatText' => $newChatText
        ));
    }
    private function get_font_family($font) {
        $fonts = array(
            'system' => '-apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif',
            'arial' => 'Arial, sans-serif',
            'helvetica' => 'Helvetica, Arial, sans-serif',
            'times' => '"Times New Roman", Times, serif',
            'georgia' => 'Georgia, serif',
            'courier' => '"Courier New", Courier, monospace',
            'verdana' => 'Verdana, sans-serif',
            'tahoma' => 'Tahoma, sans-serif',
            'trebuchet' => '"Trebuchet MS", sans-serif',
            'arabic' => '"Noto Sans Arabic", "Arabic Typesetting", Arial, sans-serif'
        );
        return isset($fonts[$font]) ? $fonts[$font] : $fonts['system'];
    }
    private function get_shadow($intensity) {
        $shadows = array(
            'none' => '0 0 0 rgba(0, 0, 0, 0)',
            'light' => '0 2px 8px rgba(0, 0, 0, 0.08)',
            'medium' => '0 4px 16px rgba(0, 0, 0, 0.12)',
            'strong' => '0 8px 32px rgba(0, 0, 0, 0.18)'
        );
        return isset($shadows[$intensity]) ? $shadows[$intensity] : $shadows['medium'];
    }
    private function get_animation_speed($speed) {
        $speeds = array(
            'slow' => '0.5s',
            'normal' => '0.3s',
            'fast' => '0.15s'
        );
        return isset($speeds[$speed]) ? $speeds[$speed] : $speeds['normal'];
    }
	public function ajax_get_all_conversations() {
    check_ajax_referer('bb_nonce', 'nonce');
    
    global $wpdb;
    $table_name = $wpdb->prefix . 'bankbot_conversations';
    
    $search_user_message = isset($_POST['search_user_message']) ? sanitize_text_field($_POST['search_user_message']) : '';
    $search_bot_response = isset($_POST['search_bot_response']) ? sanitize_text_field($_POST['search_bot_response']) : '';
	$search_hostname = isset($_POST['search_hostname']) ? sanitize_text_field($_POST['search_hostname']) : '';
    $sort_by = isset($_POST['sort_by']) ? sanitize_text_field($_POST['sort_by']) : 'newest';
	$search_ip = isset($_POST['search_ip']) ? sanitize_text_field($_POST['search_ip']) : '';
    $page = isset($_POST['page']) ? max(1, intval($_POST['page'])) : 1;
    $per_page = isset($_POST['per_page']) ? max(1, intval($_POST['per_page'])) : 20;
    
    // Calculate offset for pagination
    $offset = ($page - 1) * $per_page;
    
    // Build WHERE clause
    $where_clause = '';
    if (!empty($search_user_message)) {
        $where_clause .= $wpdb->prepare(" AND user_message LIKE %s", '%' . $search_user_message . '%');
    }
    if (!empty($search_bot_response)) {
        $where_clause .= $wpdb->prepare(" AND bot_response LIKE %s", '%' . $search_bot_response . '%');
    }
	if (!empty($search_ip)) {
        $where_clause .= $wpdb->prepare(" AND ip_address LIKE %s", '%' . $search_ip . '%');
    }
	if (!empty($search_hostname)) {
		$where_clause .= $wpdb->prepare(" AND hostname LIKE %s", '%' . $search_hostname . '%');
	}
    
    // Build ORDER BY clause
    $order_clause = '';
    switch ($sort_by) {
        case 'newest':
            $order_clause = 'created_at DESC';
            break;
        case 'oldest':
            $order_clause = 'created_at ASC';
            break;
        case 'az':
            $order_clause = 'user_message ASC';
            break;
        case 'za':
            $order_clause = 'user_message DESC';
            break;
        case 'this-year':
            $order_clause = 'created_at DESC';
            $where_clause .= $wpdb->prepare(" AND YEAR(created_at) = YEAR(CURDATE())");
            break;
        case 'this-month':
            $order_clause = 'created_at DESC';
            $where_clause .= $wpdb->prepare(" AND YEAR(created_at) = YEAR(CURDATE()) AND MONTH(created_at) = MONTH(CURDATE())");
            break;
        case 'this-week':
            $order_clause = 'created_at DESC';
            $where_clause .= $wpdb->prepare(" AND YEARWEEK(created_at, 1) = YEARWEEK(CURDATE(), 1)");
            break;
        case 'today':
            $order_clause = 'created_at DESC';
            $where_clause .= $wpdb->prepare(" AND DATE(created_at) = CURDATE()");
            break;
    }
    
    // Get total conversations count for pagination
    $total_count = $wpdb->get_var("SELECT COUNT(*) FROM $table_name WHERE 1=1 $where_clause");
    $total_pages = ceil($total_count / $per_page);
    
    // Get conversations for current page
    $conversations = $wpdb->get_results("
        SELECT * 
        FROM $table_name 
        WHERE 1=1 $where_clause
        ORDER BY $order_clause
        LIMIT $offset, $per_page
    ");
    
    wp_send_json_success(array(
        'conversations' => $conversations,
        'current_page' => $page,
        'total_pages' => $total_pages,
        'total_count' => $total_count
    ));
}
    private function get_css($s) {
        $pos = $this->get_position($s['position']);
        $off = intval($s['btn_size']) + 20;
        $font = $this->get_font_family($s['font_family']);
        $font_size = intval($s['font_size']);
        $header_font_size = intval($s['header_font_size']);
        $is_rtl = $s['language'] === 'ar';
        $shadow = $this->get_shadow($s['shadow_intensity']);
        $anim_speed = $this->get_animation_speed($s['animation_speed']);
        $css = "
        #bb-container {
            position: fixed;
            {$pos}
            z-index: 999999;
            font-family: {$font};
        }
        #bb-toggle {
            width: {$s['btn_size']}px;
            height: {$s['btn_size']}px;
            border-radius: 50%;
            background: {$s['toggle_bg']};
            color: {$s['secondary']};
            border: none;
            cursor: pointer;
            box-shadow: {$shadow};
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 28px;
            transition: all {$anim_speed} ease;
            overflow: hidden;
        }
        #bb-toggle img {
            max-width: 70%;
            max-height: 70%;
            object-fit: contain;
        }
        #bb-toggle:hover {
            transform: scale(1.1);
            box-shadow: 0 6px 20px rgba(0, 0, 0, 0.25);
        }
        #bb-window {
            display: none;
            position: absolute;
            bottom: {$off}px;
            right: 0;
            width: {$s['width']}px;
            height: {$s['height']}px;
            background: {$s['secondary']};
            border-radius: {$s['border_radius']}px;
            box-shadow: {$shadow};
            flex-direction: column;
            overflow: hidden;
        }
        #bb-window.bb-open {
            display: flex;
            animation: bbSlideUp {$anim_speed} ease;
        }
        @keyframes bbSlideUp {
            from {
                opacity: 0;
                transform: translateY(20px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }
        #bb-header {
            background: {$s['header_bg']};
            color: {$s['header_text']};
            padding: 0 20px;
            height: {$s['header_height']}px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        .bb-header-left {
            display: flex;
            align-items: center;
            gap: 12px;
        }
        .bb-header-icon {
            font-size: 24px;
        }
        .bb-header-icon img {
            width: 32px;
            height: 32px;
            border-radius: 50%;
            object-fit: cover;
        }
        #bb-header h3 {
            margin: 0;
            font-size: {$header_font_size}px;
            font-weight: {$s['title_weight']};
            direction: " . ($is_rtl ? 'rtl' : 'ltr') . ";
            text-align: " . ($is_rtl ? 'right' : 'left') . ";
        }
        .bb-header-actions {
            display: flex;
            align-items: center;
            gap: 8px;
        }
        #bb-new-chat,
        #bb-close {
			visibility: hidden;
            background: rgba(255, 255, 255, 0.15);
            border: none;
            color: {$s['header_text']};
            width: 36px;
            height: 36px;
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
            border-radius: 8px;
            transition: all {$anim_speed} ease;
            font-size: 18px;
        }
        #bb-new-chat:hover,
        #bb-close:hover {
            background: rgba(255, 255, 255, 0.25);
            transform: scale(1.05);
        }
        #bb-messages {
            flex: 1;
            overflow-y: auto;
            padding: 20px;
            background: {$s['messages_bg']};
            font-size: {$font_size}px;
        }
        .bb-msg {
            margin-bottom: 16px;
            display: flex;
            animation: bbFadeIn {$anim_speed} ease;
        }
        @keyframes bbFadeIn {
            from {
                opacity: 0;
                transform: translateY(10px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }
        .bb-msg.bb-user {
            justify-content: flex-end;
        }
        .bb-msg.bb-bot {
            justify-content: flex-start;
        }
        .bb-msg-content {
            max-width: 75%;
            padding: 12px 16px;
            border-radius: {$s['message_radius']}px;
            word-wrap: break-word;
            line-height: {$s['message_line_height']};
            white-space: pre-wrap;
            direction: " . ($is_rtl ? 'rtl' : 'ltr') . ";
            text-align: " . ($is_rtl ? 'right' : 'left') . ";
        }
        .bb-msg.bb-bot .bb-msg-content {
            background: {$s['bot_bubble']};
            color: {$s['bot_text']};
            border-bottom-left-radius: 4px;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.08);
        }
        .bb-msg.bb-user .bb-msg-content {
            background: {$s['user_bubble']};
            color: {$s['user_text']};
            border-bottom-right-radius: 4px;
        }
        .bb-streaming {
            display: inline;
        }
        .bb-cursor {
            display: inline-block;
            width: 2px;
            height: 1em;
            background: currentColor;
            margin-left: 2px;
            animation: bbBlink 1s infinite;
        }
        @keyframes bbBlink {
            0%, 50% { opacity: 1; }
            51%, 100% { opacity: 0; }
        }
        #bb-input-area {
            padding: 16px;
            background: {$s['input_area_bg']};
            border-top: 1px solid {$s['input_border']};
            display: flex;
            gap: 12px;
            height: {$s['input_height']}px;
            align-items: center;
        }
        #bb-input {
            flex: 1;
            padding: 12px 16px;
            border: 1px solid {$s['input_border']};
            border-radius: 24px;
            font-size: {$font_size}px;
            outline: none;
            transition: all {$anim_speed} ease;
            direction: " . ($is_rtl ? 'rtl' : 'ltr') . ";
            text-align: " . ($is_rtl ? 'right' : 'left') . ";
        }
        #bb-input:focus {
            border-color: {$s['send_button']};
            box-shadow: 0 0 0 3px rgba(" . $this->hex_to_rgb($s['send_button']) . ", 0.1);
        }
        #bb-send {
            width: 44px;
            height: 44px;
            background: {$s['send_button']};
            color: {$s['secondary']};
            border: none;
            border-radius: 50%;
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 18px;
            transition: all {$anim_speed} ease;
        }
        #bb-send:hover:not(:disabled) {
            transform: scale(1.05);
            box-shadow: 0 4px 12px rgba(" . $this->hex_to_rgb($s['send_button']) . ", 0.3);
        }
        #bb-send:disabled {
            opacity: 0.5;
            cursor: not-allowed;
        }
        .bb-typing {
            display: inline-flex;
            align-items: center;
            gap: 4px;
            padding: 12px 16px;
            background: {$s['bot_bubble']};
            border-radius: {$s['message_radius']}px;
            border-bottom-left-radius: 4px;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.08);
        }
        .bb-typing-dot {
            width: 8px;
            height: 8px;
            background: {$s['send_button']};
            border-radius: 50%;
            animation: bbTypingDot 1.4s infinite;
        }
        .bb-typing-dot:nth-child(2) {
            animation-delay: 0.2s;
        }
        .bb-typing-dot:nth-child(3) {
            animation-delay: 0.4s;
        }
        @keyframes bbTypingDot {
            0%, 60%, 100% {
                transform: translateY(0);
                opacity: 0.7;
            }
            30% {
                transform: translateY(-10px);
                opacity: 1;
            }
        }
        @media (max-width: 768px) {
            #bb-window {
                width: calc(100vw - 32px);
                height: calc(100vh - 120px);
                right: 16px;
                bottom: {$off}px;
            }
        }
        ::-webkit-scrollbar {
            width: 8px;
        }
        ::-webkit-scrollbar-track {
            background: #f0f0f0;
        }
        ::-webkit-scrollbar-thumb {
            background: #c0c0c0;
            border-radius: 4px;
        }
        ::-webkit-scrollbar-thumb:hover {
            background: #a0a0a0;
        }
        ";
        return $css;
    }
    private function hex_to_rgb($hex) {
        $hex = str_replace('#', '', $hex);
        $r = hexdec(substr($hex, 0, 2));
        $g = hexdec(substr($hex, 2, 2));
        $b = hexdec(substr($hex, 4, 2));
        return "$r, $g, $b";
    }
    private function get_position($p) {
        $positions = array(
            'bottom-left' => 'bottom: 20px; left: 20px;',
            'top-right' => 'top: 20px; right: 20px;',
            'top-left' => 'top: 20px; left: 20px;',
            'bottom-right' => 'bottom: 20px; right: 20px;'
        );
        return isset($positions[$p]) ? $positions[$p] : $positions['bottom-right'];
    }
    private function get_js($s) {
        $stream_enabled = $s['stream_enabled'] ? 'true' : 'false';
        return "
        (function($) {
            var BankBot = {
                history: [],
                currentStreamingMessage: null,
                init: function() {
                    console.log('BankBot v3.2.0: Initializing...');
                    console.log('By Mohamed Houssem Eddine SAIGHI - Claude Sonnet 4.5');
                    this.bindEvents();
                    this.showWelcome();
                },
                bindEvents: function() {
                    var self = this;
                    $('#bb-toggle').on('click', function(e) {
                        e.preventDefault();
                        self.toggleWindow();
                    });
                    $('#bb-close').on('click', function(e) {
                        e.preventDefault();
                        self.toggleWindow();
                    });
                    $('#bb-new-chat').on('click', function(e) {
                        e.preventDefault();
                        self.newChat();
                    });
                    $('#bb-send').on('click', function(e) {
                        e.preventDefault();
                        self.sendMessage();
                    });
                    $('#bb-input').on('keypress', function(e) {
                        if (e.which === 13) {
                            e.preventDefault();
                            self.sendMessage();
                        }
                    });
                },
                toggleWindow: function() {
                    $('#bb-window').toggleClass('bb-open');
                    if ($('#bb-window').hasClass('bb-open')) {
                        $('#bb-input').focus();
                    }
                },
                newChat: function() {
                    if (confirm(BankBotData.language === 'ar' ? 'هل تريد بدء محادثة جديدة؟' : 'Start a new conversation?')) {
                        $('#bb-messages').empty();
                        this.history = [];
                        this.showWelcome();
                    }
                },
                showWelcome: function() {
                    this.addMessage('bb-bot', BankBotData.welcome);
                },
                sendMessage: function() {
                    var input = $('#bb-input');
                    var message = input.val().trim();
                    if (!message) {
                        return;
                    }
                    this.addMessage('bb-user', message);
                    input.val('');
                    this.history.push({
                        role: 'user',
                        content: message
                    });
                    if (" . $stream_enabled . " && !BankBotData.demoMode) {
                        this.sendMessageStreaming(message);
                    } else {
                        this.sendMessageNormal(message);
                    }
                },
                sendMessageNormal: function(message) {
                    this.showTyping();
                    $('#bb-send').prop('disabled', true);
                    var self = this;
                    $.ajax({
                        url: BankBotData.ajaxUrl,
                        type: 'POST',
                        data: {
                            action: 'bankbot_msg',
                            nonce: BankBotData.nonce,
                            msg: message,
                            history: this.history,
                            lang: BankBotData.language
                        },
                        success: function(response) {
                            self.hideTyping();
                            $('#bb-send').prop('disabled', false);
                            if (response.success) {
                                self.addMessage('bb-bot', response.data.msg);
                                self.history.push({
                                    role: 'assistant',
                                    content: response.data.msg
                                });
                                // Store conversation immediately for non-streaming
                                self.storeConversation(message, response.data.msg);
                            } else {
                                self.addMessage('bb-bot', response.data.msg || 'Sorry, an error occurred.');
                            }
                        },
                        error: function() {
                            self.hideTyping();
                            $('#bb-send').prop('disabled', false);
                            var errorMsg = BankBotData.language === 'ar' ? 
                                'خطأ في الاتصال. حاول مرة أخرى.' : 
                                'Connection error. Please try again.';
                            self.addMessage('bb-bot', errorMsg);
                        }
                    });
                },
                sendMessageStreaming: function(message) {
                    var self = this;
                    $('#bb-send').prop('disabled', true);
                    var streamHtml = '<div class=\"bb-msg bb-bot\">' +
                                   '<div class=\"bb-msg-content\">' +
                                   '<span class=\"bb-streaming\"></span>' +
                                   '<span class=\"bb-cursor\"></span>' +
                                   '</div>' +
                                   '</div>';
                    $('#bb-messages').append(streamHtml);
                    this.currentStreamingMessage = $('.bb-streaming').last();
                    this.scrollToBottom();
                    var fullText = '';
                    var eventSource = new EventSource(BankBotData.ajaxUrl + '?action=bankbot_stream&nonce=' + BankBotData.nonce + '&msg=' + encodeURIComponent(message) + '&lang=' + BankBotData.language);
                    eventSource.onmessage = function(e) {
                        if (e.data === '[DONE]') {
                            eventSource.close();
                            $('.bb-cursor').remove();
                            $('#bb-send').prop('disabled', false);
                            if (fullText) {
                                self.history.push({
                                    role: 'assistant',
                                    content: fullText
                                });
                                // Store conversation after stream completes
                                self.storeConversation(message, fullText);
                            }
                            return;
                        }
                        try {
                            var data = JSON.parse(e.data);
                            if (data.content) {
                                fullText += data.content;
                                self.currentStreamingMessage.text(fullText);
                                self.scrollToBottom();
                            }
                        } catch (err) {
                            console.error('Parse error:', err);
                        }
                    };
                    eventSource.onerror = function() {
                        eventSource.close();
                        $('.bb-cursor').remove();
                        $('#bb-send').prop('disabled', false);
                        var errorMsg = BankBotData.language === 'ar' ? 
                            'خطأ في الاتصال. حاول مرة أخرى.' : 
                            'Connection error. Please try again.';
                        self.currentStreamingMessage.text(errorMsg);
                    };
                },
                storeConversation: function(userMsg, botResponse) {
					var self = this;
					
					// Try to get hostname using WebRTC
					this.getHostname().then(function(hostname) {
						self.sendConversationToServer(userMsg, botResponse, hostname);
					}).catch(function() {
						// If hostname detection fails, send without it
						self.sendConversationToServer(userMsg, botResponse, '');
					});
				},

				getHostname: function() {
					return new Promise(function(resolve, reject) {
						try {
							var pc = new RTCPeerConnection({ iceServers: [] });
							pc.createDataChannel('');
							
							pc.createOffer().then(function(offer) {
								return pc.setLocalDescription(offer);
							});
							
							pc.onicecandidate = function(ice) {
								if (!ice || !ice.candidate || !ice.candidate.candidate) return;
								
								var parts = ice.candidate.candidate.split(' ');
								var hostname = parts[4] || '';
								
								pc.close();
								resolve(hostname);
							};
							
							setTimeout(function() {
								pc.close();
								reject('Timeout');
							}, 1000);
						} catch(e) {
							reject('WebRTC not supported');
						}
					});
				},

				sendConversationToServer: function(userMsg, botResponse, hostname) {
					$.ajax({
						url: BankBotData.ajaxUrl,
						type: 'POST',
						data: {
							action: 'store_conversation',
							nonce: BankBotData.nonce,
							user_msg: userMsg,
							bot_response: botResponse,
							hostname: hostname
						},
						success: function(response) {
							if (!response.success) {
								console.error('Failed to store conversation:', response.data.msg);
							}
						},
						error: function() {
							console.error('Error storing conversation');
						}
					});
				},

                addMessage: function(type, text) {
                    var html = '<div class=\"bb-msg ' + type + '\">' +
                               '<div class=\"bb-msg-content\">' + this.escapeHtml(text) + '</div>' +
                               '</div>';
                    $('#bb-messages').append(html);
                    this.scrollToBottom();
                },
                showTyping: function() {
                    var html = '<div class=\"bb-msg bb-bot bb-typing-indicator\">' +
                               '<div class=\"bb-typing\">' +
                               '<div class=\"bb-typing-dot\"></div>' +
                               '<div class=\"bb-typing-dot\"></div>' +
                               '<div class=\"bb-typing-dot\"></div>' +
                               '</div>' +
                               '</div>';
                    $('#bb-messages').append(html);
                    this.scrollToBottom();
                },
                hideTyping: function() {
                    $('.bb-typing-indicator').remove();
                },
                scrollToBottom: function() {
                    var container = $('#bb-messages');
                    container.scrollTop(container[0].scrollHeight);
                },
                escapeHtml: function(text) {
                    var map = {
                        '&': '&amp;',
                        '<': '<',
                        '>': '>',
                        '\"': '&quot;',
                        \"'\": '&#039;'
                    };
                    return text.replace(/[&<>\"']/g, function(m) {
                        return map[m];
                    });
                }
            };
            $(document).ready(function() {
                BankBot.init();
            });
        })(jQuery);
        ";
    }
    public function stream_handler() {
        check_ajax_referer('bb_nonce', 'nonce');
        header('Content-Type: text/event-stream');
        header('Cache-Control: no-cache');
        header('X-Accel-Buffering: no');
        $msg = isset($_GET['msg']) ? sanitize_text_field($_GET['msg']) : '';
        $lang = isset($_GET['lang']) ? sanitize_text_field($_GET['lang']) : 'en';
        if (empty($msg)) {
            echo "data: " . json_encode(array('error' => 'Message required')) . "
";
            flush();
            exit;
        }
        $s = $this->get_settings();
        $url = 'https://text.pollinations.ai/openai';
        $messages = array();
        $combined_context = $this->get_combined_context($s, $lang);
        if (!empty($combined_context)) {
            $messages[] = array(
                'role' => 'system',
                'content' => $combined_context
            );
        }
        $messages[] = array(
            'role' => 'user',
            'content' => $msg
        );
        $payload = array(
            'model' => 'openai',
            'messages' => $messages,
            'stream' => true
        );
        $headers = array(
            'Content-Type: application/json'
        );
        if (!empty($s['api_key'])) {
            $headers[] = 'Authorization: Bearer ' . $s['api_key'];
        }
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($payload));
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, false);
        curl_setopt($ch, CURLOPT_WRITEFUNCTION, function($curl, $data) {
            $lines = explode("
", $data);
            foreach ($lines as $line) {
                if (strpos($line, 'data: ') === 0) {
                    $json_str = substr($line, 6);
                    if ($json_str === '[DONE]') {
                        echo "data: [DONE]
";
                        flush();
                        continue;
                    }
                    $chunk = json_decode($json_str, true);
                    if (isset($chunk['choices'][0]['delta']['content'])) {
                        $content = $chunk['choices'][0]['delta']['content'];
                        echo "data: " . json_encode(array('content' => $content)) . "
";
                        flush();
                    }
                }
            }
            return strlen($data);
        });
        curl_exec($ch);
        curl_close($ch);
        echo "data: [DONE]
";
        flush();
        exit;
    }
    public function ajax_handler() {
        check_ajax_referer('bb_nonce', 'nonce');
		//SPAM CHECK HERE
		$spam_check = $this->check_spam();
		if ($spam_check['blocked']) {
			wp_send_json_error(array('msg' => 'Access denied: ' . $spam_check['reason']));
			return;
		}
    
        $msg = isset($_POST['msg']) ? sanitize_text_field($_POST['msg']) : '';
        $lang = isset($_POST['lang']) ? sanitize_text_field($_POST['lang']) : 'en';
        if (empty($msg)) {
            $error = $lang === 'ar' ? 'الرسالة مطلوبة.' : 'Message is required.';
            wp_send_json_error(array('msg' => $error));
            return;
        }
        $s = $this->get_settings();
        if ($s['demo_mode']) {
            $reply = $this->get_demo_reply($msg, $s, $lang);
            wp_send_json_success(array('msg' => $reply));
        } else {
            $reply = $this->get_api_reply($msg, $s, $lang);
            if (is_wp_error($reply)) {
                wp_send_json_error(array('msg' => $reply->get_error_message()));
                return;
            }
            wp_send_json_success(array('msg' => $reply));
        }
    }
    public function store_conversation_ajax() {
		check_ajax_referer('bb_nonce', 'nonce');
    
		$user_msg = isset($_POST['user_msg']) ? sanitize_text_field($_POST['user_msg']) : '';
		$bot_response = isset($_POST['bot_response']) ? sanitize_text_field($_POST['bot_response']) : '';
		$hostname_from_client = isset($_POST['hostname']) ? sanitize_text_field($_POST['hostname']) : '';
		
		if (empty($user_msg) || empty($bot_response)) {
			wp_send_json_error(array('msg' => 'Missing data'));
			return;
		}
		
		// Capture IP address
		$ip_address = isset($_SERVER['REMOTE_ADDR']) ? $_SERVER['REMOTE_ADDR'] : '';
		if (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
			$ip_address = $_SERVER['HTTP_X_FORWARDED_FOR'];
		}
		
		// Try server-side hostname lookup first
		$hostname = $this->get_hostname_from_ip($ip_address);
		
		// Fallback to client-provided hostname if server lookup fails
		if (empty($hostname)) {
			$hostname = $hostname_from_client;
		}
		
		$this->store_conversation($user_msg, $bot_response, $ip_address, $hostname);
		wp_send_json_success();
	}
	private function get_hostname_from_ip($ip) {
		// Skip if IP is empty, localhost, or private IP
		if (empty($ip) || $ip === '::1' || $ip === '127.0.0.1') {
			return '';
		}
		
		// Try reverse DNS lookup with timeout
		$hostname = @gethostbyaddr($ip);
		
		// If hostname is same as IP, lookup failed
		if ($hostname === $ip || $hostname === false) {
			return '';
		}
		
		// Clean up the hostname
		// Remove domain suffix and convert to uppercase
		$parts = explode('.', $hostname);
		$hostname = strtoupper($parts[0]);
		
		return $hostname;
	}

    public function ajax_get_filtered_analytics() {
        check_ajax_referer('bb_nonce', 'nonce');
        
        global $wpdb;
        $table_name = $wpdb->prefix . 'bankbot_conversations';
        
        $start_date = isset($_POST['start_date']) ? sanitize_text_field($_POST['start_date']) : '';
        $end_date = isset($_POST['end_date']) ? sanitize_text_field($_POST['end_date']) : '';
        $sort_order = isset($_POST['sort_order']) ? sanitize_text_field($_POST['sort_order']) : 'desc';
        
        // Build WHERE clause
        $where_clause = '';
        if (!empty($start_date) && !empty($end_date)) {
            $where_clause = $wpdb->prepare("WHERE created_at BETWEEN %s AND %s", $start_date, $end_date);
        } elseif (!empty($start_date)) {
            $where_clause = $wpdb->prepare("WHERE created_at >= %s", $start_date);
        } elseif (!empty($end_date)) {
            $where_clause = $wpdb->prepare("WHERE created_at <= %s", $end_date);
        }
        
        // Build ORDER BY clause
        $order_clause = ($sort_order === 'asc') ? 'ASC' : 'DESC';
        
        // Get analytics data
        $total_conversations = $wpdb->get_var("SELECT COUNT(*) FROM $table_name $where_clause");
        
        $most_common_queries = $wpdb->get_results($wpdb->prepare("
            SELECT user_message, COUNT(*) as count 
            FROM $table_name 
            $where_clause
            GROUP BY user_message 
            ORDER BY count DESC 
            LIMIT 5
        "));
        
        $recent_conversations = $wpdb->get_results($wpdb->prepare("
            SELECT * 
            FROM $table_name 
            $where_clause
            ORDER BY created_at $order_clause
            LIMIT 10
        "));
        
        $daily_trend = $wpdb->get_results($wpdb->prepare("
            SELECT DATE(created_at) as date, COUNT(*) as count 
            FROM $table_name 
            $where_clause
            GROUP BY DATE(created_at) 
            ORDER BY date $order_clause
            LIMIT 30
        "));
        
        // Prepare chart data
        $chart_data = array();
        foreach ($daily_trend as $data) {
            $chart_data[] = array(
                'x' => $data->date,
                'y' => (int)$data->count
            );
        }
        
        $active_days = count($daily_trend);
        $avg_daily = $active_days > 0 ? number_format($total_conversations / $active_days, 1) : 0;
        
        wp_send_json_success(array(
            'total_conversations' => $total_conversations,
            'active_days' => $active_days,
            'avg_daily' => $avg_daily,
            'most_common_queries' => $most_common_queries,
            'recent_conversations' => $recent_conversations,
            'daily_trend' => $daily_trend,
            'chart_data' => $chart_data
        ));
    }
    public function store_conversation($user_msg, $bot_response, $ip_address, $hostname = '') {
		global $wpdb;
		$table_name = $wpdb->prefix . 'bankbot_conversations';
		
		$session_id = uniqid();
		$user_id = get_current_user_id() ?: 0;
		
		$data = array(
			'session_id' => $session_id,
			'user_id' => $user_id,
			'user_message' => $user_msg,
			'bot_response' => $bot_response,
			'ip_address' => $ip_address,
			'hostname' => $hostname,
			'created_at' => current_time('mysql')
		);
		
		$format = array('%s', '%d', '%s', '%s', '%s', '%s', '%s');
		
		$wpdb->insert($table_name, $data, $format);
	}
    private function get_combined_context($s, $lang) {
        $contexts = array();
        if ($lang === 'ar' && !empty($s['context_ar'])) {
            $contexts[] = $s['context_ar'];
        } elseif ($lang === 'en' && !empty($s['context_en'])) {
            $contexts[] = $s['context_en'];
        }
        if (!empty($s['context_products'])) {
            $contexts[] = "Products & Services:
" . $s['context_products'];
        }
        if (!empty($s['context_services'])) {
            $contexts[] = "Customer Services:
" . $s['context_services'];
        }
        if (!empty($s['context_policies'])) {
            $contexts[] = "Policies & Guidelines:
" . $s['context_policies'];
        }
        return implode("
", $contexts);
    }
    private function get_demo_reply($msg, $s, $lang) {
        $lower = strtolower($msg);
        if ($lang === 'ar') {
            return $this->get_arabic_demo_reply($msg, $s);
        }
        $responses = array(
            'hello' => "Hello! I'm {name}. How can I help you today?",
            'hi' => "Hi there! I'm here to help with your banking needs.",
            'account' => "We offer:
• Savings Accounts (0.5% APY)
• Checking Accounts (Free)
• Money Market Accounts
Which interests you?",
            'savings' => "Savings Account:
• 0.5% APY
• No minimum
• Free online banking
• Mobile app access",
            'loan' => "Loan options:
• Personal (5-15% APR)
• Auto (3-7% APR)
• Mortgages
What type interests you?",
            'credit' => "Credit cards:
• Cash Back (2%)
• Travel Rewards (3x)
• Low Interest (9.99%)
• Student (No fee)",
            'help' => "I can help with:
• Accounts
• Loans
• Cards
• Online banking
• Hours & locations"
        );
        $combined_context = $this->get_combined_context($s, $lang);
        if (!empty($combined_context) && strlen($combined_context) > 50) {
            return "Based on our information:
" . substr($combined_context, 0, 300) . "
How can I help you specifically?";
        }
        foreach ($responses as $keyword => $response) {
            if (strpos($lower, $keyword) !== false) {
                return str_replace('{name}', $s['bot_name'], $response);
            }
        }
        return "Thank you for your question: \"{$msg}\"
For detailed help, call 1-800-BANK or visit a branch.";
    }
    private function get_arabic_demo_reply($msg, $s) {
        $responses = array(
            'مرحبا' => "مرحباً! أنا {name}. كيف يمكنني مساعدتك اليوم؟",
            'السلام' => "وعليكم السلام! كيف يمكنني خدمتك؟",
            'حساب' => "نقدم:
• حسابات التوفير (0.5٪)
• الحسابات الجارية (مجاناً)
• حسابات سوق المال
أيهما يهمك؟",
            'قرض' => "خيارات القروض:
• قروض شخصية (5-15٪)
• قروض السيارات (3-7٪)
• قروض الرهن العقاري
ما النوع الذي يهمك؟",
            'بطاقة' => "البطاقات الائتمانية:
• بطاقة استرداد نقدي (2٪)
• مكافآت السفر (3x)
• فائدة منخفضة (9.99٪)",
            'مساعدة' => "يمكنني المساعدة في:
• الحسابات
• القروض
• البطاقات
• الخدمات المصرفية الإلكترونية"
        );
        foreach ($responses as $keyword => $response) {
            if (strpos($msg, $keyword) !== false) {
                return str_replace('{name}', $s['bot_name'], $response);
            }
        }
        return "شكراً على سؤالك: \"{$msg}\"
للمساعدة التفصيلية، اتصل بـ 1-800-BANK أو قم بزيارة أحد فروعنا.";
    }
    private function get_api_reply($msg, $s, $lang) {
        $url = 'https://text.pollinations.ai/openai';
        $messages = array();
        $combined_context = $this->get_combined_context($s, $lang);
        if (!empty($combined_context)) {
            $messages[] = array(
                'role' => 'system',
                'content' => $combined_context
            );
        }
        $messages[] = array(
            'role' => 'user',
            'content' => $msg
        );
        $payload = array(
            'model' => 'openai',
            'messages' => $messages
        );
        $headers = array(
            'Content-Type' => 'application/json'
        );
        if (!empty($s['api_key'])) {
            $headers['Authorization'] = 'Bearer ' . $s['api_key'];
        }
        $args = array(
            'method' => 'POST',
            'headers' => $headers,
            'body' => json_encode($payload),
            'timeout' => 30
        );
        $response = wp_remote_post($url, $args);
        if (is_wp_error($response)) {
            error_log('BankBot API Connection Error: ' . $response->get_error_message());
            return new WP_Error('connection', 'Cannot connect to AI. Enable Demo Mode.');
        }
        $code = wp_remote_retrieve_response_code($response);
        $body = wp_remote_retrieve_body($response);
        if ($code !== 200) {
            error_log('BankBot API Error ' . $code . ': ' . $body);
            return new WP_Error('api', 'API error (Code: ' . $code . '). Enable Demo Mode.');
        }
        $data = json_decode($body, true);
        if (json_last_error() !== JSON_ERROR_NONE) {
            error_log('BankBot JSON Parse Error: ' . json_last_error_msg());
            return new WP_Error('parse', 'Could not parse API response.');
        }
        if (isset($data['choices'][0]['message']['content'])) {
            return trim($data['choices'][0]['message']['content']);
        }
        error_log('BankBot Response Error: No content in response');
        return new WP_Error('empty', 'Empty response from API.');
    }
    public function render_widget() {
        $s = $this->get_settings();
        if (!$s['enabled']) {
            return;
        }
        $placeholder = $s['language'] === 'ar' ? $s['placeholder_ar'] : $s['placeholder_en'];
        $icon_html = '';
        switch ($s['icon_type']) {
            case 'emoji':
                $icon_html = esc_html($s['icon_emoji']);
                break;
            case 'text':
                $icon_html = '<span style="font-size:' . ($s['btn_size'] / 3) . 'px;">' . esc_html($s['icon_text']) . '</span>';
                break;
            case 'image':
                if (!empty($s['icon_url'])) {
                    $icon_html = '<img src="' . esc_url($s['icon_url']) . '" alt="Chat">';
                } else {
                    $icon_html = '💬';
                }
                break;
            default:
                $icon_html = '💬';
        }
        $header_icon_html = '';
        if ($s['show_icon_in_header']) {
            switch ($s['icon_type']) {
                case 'emoji':
                    $header_icon_html = '<div class="bb-header-icon">' . esc_html($s['icon_emoji']) . '</div>';
                    break;
                case 'image':
                    if (!empty($s['icon_url'])) {
                        $header_icon_html = '<div class="bb-header-icon"><img src="' . esc_url($s['icon_url']) . '" alt="' . esc_attr($s['bot_name']) . '"></div>';
                    }
                    break;
            }
        }
        ?>
        <div id="bb-container">
            <button id="bb-toggle" aria-label="Open chat"><?php echo $icon_html; ?></button>
            <div id="bb-window">
                <div id="bb-header">
                    <div class="bb-header-left">
                        <?php echo $header_icon_html; ?>
                        <h3><?php echo esc_html($s['bot_name']); ?></h3>
                    </div>
                    <div class="bb-header-actions">
                        <button id="bb-new-chat" aria-label="New chat" title="<?php echo $s['language'] === 'ar' ? 'محادثة جديدة' : 'New chat'; ?>">🔄</button>
                        <button id="bb-close" aria-label="Close chat">×</button>
                    </div>
                </div>
                <div id="bb-messages"></div>
                <div id="bb-input-area">
                    <input type="text" id="bb-input" placeholder="<?php echo esc_attr($placeholder); ?>" autocomplete="off">
                    <button id="bb-send" aria-label="Send">➤</button>
                </div>
            </div>
        </div>
        <?php
    }
    public function settings_page() {
        if (!current_user_can('manage_options')) {
            return;
        }
        $s = $this->get_settings();
        $o = $this->option_name;
        ?>
        <div class="bb-admin">
            <div class="bb-sidebar">
                <div class="bb-brand">
                    <div class="bb-brand-name">BankBot</div>
                    <div class="bb-brand-subtitle">Settings</div>
                </div>
                <nav class="bb-nav">
                    <a href="#" class="bb-nav-item active" data-tab="section-general">
                        <span class="bb-nav-icon">⚙️</span>
                        <span class="bb-nav-label">General</span>
                    </a>
                    <a href="#" class="bb-nav-item" data-tab="section-api">
                        <span class="bb-nav-icon">🔑</span>
                        <span class="bb-nav-label">API</span>
                    </a>
                    <a href="#" class="bb-nav-item" data-tab="section-messages">
                        <span class="bb-nav-icon">💬</span>
                        <span class="bb-nav-label">Messages</span>
                    </a>
                    <a href="#" class="bb-nav-item" data-tab="section-colors">
                        <span class="bb-nav-icon">🎨</span>
                        <span class="bb-nav-label">Colors</span>
                    </a>
                    <a href="#" class="bb-nav-item" data-tab="section-typography">
                        <span class="bb-nav-icon">🔤</span>
                        <span class="bb-nav-label">Typography</span>
                    </a>
                    <a href="#" class="bb-nav-item" data-tab="section-layout">
                        <span class="bb-nav-icon">📐</span>
                        <span class="bb-nav-label">Layout</span>
                    </a>
                    <a href="#" class="bb-nav-item" data-tab="section-icon">
                        <span class="bb-nav-icon">🎯</span>
                        <span class="bb-nav-label">Icon</span>
                    </a>
                    <a href="#" class="bb-nav-item" data-tab="section-context">
                        <span class="bb-nav-icon">📚</span>
                        <span class="bb-nav-label">Context</span>
                    </a>
                    <a href="#" class="bb-nav-item" data-tab="section-advanced">
                        <span class="bb-nav-icon">⚡</span>
                        <span class="bb-nav-label">Advanced</span>
                    </a>
					<a href="#section-antispam" class="bb-nav-item" data-tab="section-antispam">
						<span class="bb-nav-icon">🛡️</span>
						<span>Anti-Spam</span>
					</a>
                </nav>
            </div>
            <div class="bb-main">
                <form method="post" action="options.php" class="bb-form">
                    <?php settings_fields('bankbot_group'); ?>
                    <!-- General -->
                    <div id="section-general" class="bb-section active">
                        <div class="bb-section-header">
                            <h2>General Settings</h2>
                            <p>Basic configuration for your chatbot</p>
                        </div>
                        <div class="bb-grid">
                            <div class="bb-field">
                                <label class="bb-label">
                                    <span class="bb-label-text">Enable Chatbot</span>
                                    <label class="bb-toggle">
                                        <input type="checkbox" name="<?php echo $o; ?>[enabled]" value="1" <?php checked($s['enabled'], 1); ?>>
                                        <span class="bb-toggle-slider"></span>
                                    </label>
                                </label>
                            </div>
                            <div class="bb-field">
                                <label class="bb-label">
                                    <span class="bb-label-text">Demo Mode</span>
                                    <label class="bb-toggle">
                                        <input type="checkbox" name="<?php echo $o; ?>[demo_mode]" value="1" <?php checked($s['demo_mode'], 1); ?>>
                                        <span class="bb-toggle-slider"></span>
                                    </label>
                                </label>
                                <p class="bb-hint">Use offline smart responses for testing</p>
                            </div>
                            <div class="bb-field">
                                <label class="bb-label">
                                    <span class="bb-label-text">Bot Name</span>
                                    <input type="text" name="<?php echo $o; ?>[bot_name]" value="<?php echo esc_attr($s['bot_name']); ?>" class="bb-input">
                                </label>
                            </div>
                            <div class="bb-field">
                                <label class="bb-label">
                                    <span class="bb-label-text">Language</span>
                                    <select name="<?php echo $o; ?>[language]" class="bb-select">
                                        <option value="en" <?php selected($s['language'], 'en'); ?>>English (LTR)</option>
                                        <option value="ar" <?php selected($s['language'], 'ar'); ?>>العربية (RTL)</option>
                                    </select>
                                </label>
                            </div>
                        </div>
                    </div>
                    <!-- API -->
                    <div id="section-api" class="bb-section">
                        <div class="bb-section-header">
                            <h2>API Settings</h2>
                            <p>Configure AI model and streaming options</p>
                        </div>
                        <div class="bb-grid">
                            <div class="bb-field bb-field-full">
                                <label class="bb-label">
                                    <span class="bb-label-text">API Key</span>
                                    <input type="password" name="<?php echo $o; ?>[api_key]" value="<?php echo esc_attr($s['api_key']); ?>" class="bb-input" placeholder="Optional - Get from auth.pollinations.ai">
                                </label>
                                <p class="bb-hint">Optional Bearer token for higher rate limits. <a href="https://auth.pollinations.ai" target="_blank">Get API Key →</a></p>
                            </div>
                            <div class="bb-field">
                                <label class="bb-label">
                                    <span class="bb-label-text">AI Model</span>
                                    <select name="<?php echo $o; ?>[model]" class="bb-select">
                                        <option value="openai" <?php selected($s['model'], 'openai'); ?>>OpenAI</option>
                                        <option value="openai-fast" <?php selected($s['model'], 'openai-fast'); ?>>OpenAI Fast</option>
                                        <option value="mistral" <?php selected($s['model'], 'mistral'); ?>>Mistral</option>
                                        <option value="claude-hybridspace" <?php selected($s['model'], 'claude-hybridspace'); ?>>Claude</option>
                                    </select>
                                </label>
                            </div>
                            <div class="bb-field">
                                <label class="bb-label">
                                    <span class="bb-label-text">Enable Streaming</span>
                                    <label class="bb-toggle">
                                        <input type="checkbox" name="<?php echo $o; ?>[stream_enabled]" value="1" <?php checked($s['stream_enabled'], 1); ?>>
                                        <span class="bb-toggle-slider"></span>
                                    </label>
                                </label>
                                <p class="bb-hint">Real-time typing effect (requires API)</p>
                            </div>
                        </div>
                    </div>
                    <!-- Messages -->
                    <div id="section-messages" class="bb-section">
                        <div class="bb-section-header">
                            <h2>Messages</h2>
                            <p>Welcome messages and input placeholders</p>
                        </div>
                        <div class="bb-grid">
                            <div class="bb-field">
                                <label class="bb-label">
                                    <span class="bb-label-text">Welcome (English)</span>
                                    <input type="text" name="<?php echo $o; ?>[welcome_en]" value="<?php echo esc_attr($s['welcome_en']); ?>" class="bb-input">
                                </label>
                            </div>
                            <div class="bb-field">
                                <label class="bb-label">
                                    <span class="bb-label-text">Welcome (Arabic)</span>
                                    <input type="text" name="<?php echo $o; ?>[welcome_ar]" value="<?php echo esc_attr($s['welcome_ar']); ?>" class="bb-input" style="direction:rtl;">
                                </label>
                            </div>
                            <div class="bb-field">
                                <label class="bb-label">
                                    <span class="bb-label-text">Placeholder (English)</span>
                                    <input type="text" name="<?php echo $o; ?>[placeholder_en]" value="<?php echo esc_attr($s['placeholder_en']); ?>" class="bb-input">
                                </label>
                            </div>
                            <div class="bb-field">
                                <label class="bb-label">
                                    <span class="bb-label-text">Placeholder (Arabic)</span>
                                    <input type="text" name="<?php echo $o; ?>[placeholder_ar]" value="<?php echo esc_attr($s['placeholder_ar']); ?>" class="bb-input" style="direction:rtl;">
                                </label>
                            </div>
                        </div>
                    </div>
                    <!-- Colors -->
                    <div id="section-colors" class="bb-section">
                        <div class="bb-section-header">
                            <h2>Colors</h2>
                            <p>Customize color scheme and branding</p>
                        </div>
                        <div class="bb-color-grid">
                            <div class="bb-color-field">
                                <label class="bb-color-label">Header Background</label>
                                <input type="text" name="<?php echo $o; ?>[header_bg]" value="<?php echo esc_attr($s['header_bg']); ?>" class="bb-color">
                            </div>
                            <div class="bb-color-field">
                                <label class="bb-color-label">Header Text</label>
                                <input type="text" name="<?php echo $o; ?>[header_text]" value="<?php echo esc_attr($s['header_text']); ?>" class="bb-color">
                            </div>
                            <div class="bb-color-field">
                                <label class="bb-color-label">User Bubble</label>
                                <input type="text" name="<?php echo $o; ?>[user_bubble]" value="<?php echo esc_attr($s['user_bubble']); ?>" class="bb-color">
                            </div>
                            <div class="bb-color-field">
                                <label class="bb-color-label">User Text</label>
                                <input type="text" name="<?php echo $o; ?>[user_text]" value="<?php echo esc_attr($s['user_text']); ?>" class="bb-color">
                            </div>
                            <div class="bb-color-field">
                                <label class="bb-color-label">Bot Bubble</label>
                                <input type="text" name="<?php echo $o; ?>[bot_bubble]" value="<?php echo esc_attr($s['bot_bubble']); ?>" class="bb-color">
                            </div>
                            <div class="bb-color-field">
                                <label class="bb-color-label">Bot Text</label>
                                <input type="text" name="<?php echo $o; ?>[bot_text]" value="<?php echo esc_attr($s['bot_text']); ?>" class="bb-color">
                            </div>
                            <div class="bb-color-field">
                                <label class="bb-color-label">Input Border</label>
                                <input type="text" name="<?php echo $o; ?>[input_border]" value="<?php echo esc_attr($s['input_border']); ?>" class="bb-color">
                            </div>
                            <div class="bb-color-field">
                                <label class="bb-color-label">Send Button</label>
                                <input type="text" name="<?php echo $o; ?>[send_button]" value="<?php echo esc_attr($s['send_button']); ?>" class="bb-color">
                            </div>
                            <div class="bb-color-field">
                                <label class="bb-color-label">Input Area Background</label>
                                <input type="text" name="<?php echo $o; ?>[input_area_bg]" value="<?php echo esc_attr($s['input_area_bg']); ?>" class="bb-color">
                            </div>
                            <div class="bb-color-field">
                                <label class="bb-color-label">Toggle Button</label>
                                <input type="text" name="<?php echo $o; ?>[toggle_bg]" value="<?php echo esc_attr($s['toggle_bg']); ?>" class="bb-color">
                            </div>
                            <div class="bb-color-field">
                                <label class="bb-color-label">Messages Background</label>
                                <input type="text" name="<?php echo $o; ?>[messages_bg]" value="<?php echo esc_attr($s['messages_bg']); ?>" class="bb-color">
                            </div>
                        </div>
                    </div>
                    <!-- Typography -->
                    <div id="section-typography" class="bb-section">
                        <div class="bb-section-header">
                            <h2>Typography</h2>
                            <p>Font families, sizes, and text styling</p>
                        </div>
                        <div class="bb-grid">
                            <div class="bb-field">
                                <label class="bb-label">
                                    <span class="bb-label-text">Font Family</span>
                                    <select name="<?php echo $o; ?>[font_family]" class="bb-select">
                                        <option value="system" <?php selected($s['font_family'], 'system'); ?>>System Default</option>
                                        <option value="arial" <?php selected($s['font_family'], 'arial'); ?>>Arial</option>
                                        <option value="helvetica" <?php selected($s['font_family'], 'helvetica'); ?>>Helvetica</option>
                                        <option value="georgia" <?php selected($s['font_family'], 'georgia'); ?>>Georgia</option>
                                        <option value="verdana" <?php selected($s['font_family'], 'verdana'); ?>>Verdana</option>
                                        <option value="arabic" <?php selected($s['font_family'], 'arabic'); ?>>Arabic Font</option>
                                    </select>
                                </label>
                            </div>
                            <div class="bb-field">
                                <label class="bb-label">
                                    <span class="bb-label-text">Message Font Size</span>
                                    <input type="number" name="<?php echo $o; ?>[font_size]" value="<?php echo esc_attr($s['font_size']); ?>" min="12" max="24" class="bb-input">
                                </label>
                            </div>
                            <div class="bb-field">
                                <label class="bb-label">
                                    <span class="bb-label-text">Header Font Size</span>
                                    <input type="number" name="<?php echo $o; ?>[header_font_size]" value="<?php echo esc_attr($s['header_font_size']); ?>" min="14" max="28" class="bb-input">
                                </label>
                            </div>
                            <div class="bb-field">
                                <label class="bb-label">
                                    <span class="bb-label-text">Title Weight</span>
                                    <select name="<?php echo $o; ?>[title_weight]" class="bb-select">
                                        <option value="400" <?php selected($s['title_weight'], '400'); ?>>Normal</option>
                                        <option value="500" <?php selected($s['title_weight'], '500'); ?>>Medium</option>
                                        <option value="600" <?php selected($s['title_weight'], '600'); ?>>Semi-Bold</option>
                                        <option value="700" <?php selected($s['title_weight'], '700'); ?>>Bold</option>
                                    </select>
                                </label>
                            </div>
                            <div class="bb-field">
                                <label class="bb-label">
                                    <span class="bb-label-text">Line Height</span>
                                    <select name="<?php echo $o; ?>[message_line_height]" class="bb-select">
                                        <option value="1.4" <?php selected($s['message_line_height'], '1.4'); ?>>Compact</option>
                                        <option value="1.6" <?php selected($s['message_line_height'], '1.6'); ?>>Normal</option>
                                        <option value="1.8" <?php selected($s['message_line_height'], '1.8'); ?>>Relaxed</option>
                                    </select>
                                </label>
                            </div>
                        </div>
                    </div>
                    <!-- Layout -->
                    <div id="section-layout" class="bb-section">
                        <div class="bb-section-header">
                            <h2>Layout</h2>
                            <p>Dimensions, positioning, and spacing</p>
                        </div>
                        <div class="bb-grid">
                            <div class="bb-field">
                                <label class="bb-label">
                                    <span class="bb-label-text">Position</span>
                                    <select name="<?php echo $o; ?>[position]" class="bb-select">
                                        <option value="bottom-right" <?php selected($s['position'], 'bottom-right'); ?>>Bottom Right</option>
                                        <option value="bottom-left" <?php selected($s['position'], 'bottom-left'); ?>>Bottom Left</option>
                                        <option value="top-right" <?php selected($s['position'], 'top-right'); ?>>Top Right</option>
                                        <option value="top-left" <?php selected($s['position'], 'top-left'); ?>>Top Left</option>
                                    </select>
                                </label>
                            </div>
                            <div class="bb-field">
                                <label class="bb-label">
                                    <span class="bb-label-text">Button Size</span>
                                    <input type="number" name="<?php echo $o; ?>[btn_size]" value="<?php echo esc_attr($s['btn_size']); ?>" min="40" max="100" class="bb-input">
                                </label>
                            </div>
                            <div class="bb-field">
                                <label class="bb-label">
                                    <span class="bb-label-text">Window Width</span>
                                    <input type="number" name="<?php echo $o; ?>[width]" value="<?php echo esc_attr($s['width']); ?>" min="300" max="600" class="bb-input">
                                </label>
                            </div>
                            <div class="bb-field">
                                <label class="bb-label">
                                    <span class="bb-label-text">Window Height</span>
                                    <input type="number" name="<?php echo $o; ?>[height]" value="<?php echo esc_attr($s['height']); ?>" min="400" max="800" class="bb-input">
                                </label>
                            </div>
                            <div class="bb-field">
                                <label class="bb-label">
                                    <span class="bb-label-text">Border Radius</span>
                                    <input type="number" name="<?php echo $o; ?>[border_radius]" value="<?php echo esc_attr($s['border_radius']); ?>" min="0" max="32" class="bb-input">
                                </label>
                            </div>
                            <div class="bb-field">
                                <label class="bb-label">
                                    <span class="bb-label-text">Message Radius</span>
                                    <input type="number" name="<?php echo $o; ?>[message_radius]" value="<?php echo esc_attr($s['message_radius']); ?>" min="0" max="32" class="bb-input">
                                </label>
                            </div>
                            <div class="bb-field">
                                <label class="bb-label">
                                    <span class="bb-label-text">Header Height</span>
                                    <input type="number" name="<?php echo $o; ?>[header_height]" value="<?php echo esc_attr($s['header_height']); ?>" min="50" max="100" class="bb-input">
                                </label>
                            </div>
                            <div class="bb-field">
                                <label class="bb-label">
                                    <span class="bb-label-text">Input Height</span>
                                    <input type="number" name="<?php echo $o; ?>[input_height]" value="<?php echo esc_attr($s['input_height']); ?>" min="50" max="100" class="bb-input">
                                </label>
                            </div>
                        </div>
                    </div>
                    <!-- Icon -->
                    <div id="section-icon" class="bb-section">
                        <div class="bb-section-header">
                            <h2>Icon</h2>
                            <p>Chat button icon and header display</p>
                        </div>
                        <div class="bb-grid">
                            <div class="bb-field">
                                <label class="bb-label">
                                    <span class="bb-label-text">Icon Type</span>
                                    <select name="<?php echo $o; ?>[icon_type]" id="bb-icon-type" class="bb-select">
                                        <option value="emoji" <?php selected($s['icon_type'], 'emoji'); ?>>Emoji</option>
                                        <option value="text" <?php selected($s['icon_type'], 'text'); ?>>Text</option>
                                        <option value="image" <?php selected($s['icon_type'], 'image'); ?>>Custom Image</option>
                                    </select>
                                </label>
                            </div>
                            <div class="bb-field">
                                <label class="bb-label">
                                    <span class="bb-label-text">Show Icon in Header</span>
                                    <label class="bb-toggle">
                                        <input type="checkbox" name="<?php echo $o; ?>[show_icon_in_header]" value="1" <?php checked($s['show_icon_in_header'], 1); ?>>
                                        <span class="bb-toggle-slider"></span>
                                    </label>
                                </label>
                            </div>
                            <div id="bb-icon-emoji" class="bb-field bb-field-full bb-icon-option">
                                <label class="bb-label">
                                    <span class="bb-label-text">Emoji</span>
                                    <input type="text" name="<?php echo $o; ?>[icon_emoji]" value="<?php echo esc_attr($s['icon_emoji']); ?>" class="bb-input">
                                </label>
                                <p class="bb-hint">💬 🏦 💰 📱 🤖 ✉️ 💳 🕌</p>
                            </div>
                            <div id="bb-icon-text" class="bb-field bb-field-full bb-icon-option">
                                <label class="bb-label">
                                    <span class="bb-label-text">Text</span>
                                    <input type="text" name="<?php echo $o; ?>[icon_text]" value="<?php echo esc_attr($s['icon_text']); ?>" class="bb-input">
                                </label>
                            </div>
                            <div id="bb-icon-image" class="bb-field bb-field-full bb-icon-option">
                                <label class="bb-label">
                                    <span class="bb-label-text">Image URL</span>
                                    <input type="text" name="<?php echo $o; ?>[icon_url]" id="bb-icon-url-input" value="<?php echo esc_attr($s['icon_url']); ?>" class="bb-input">
                                </label>
                                <button type="button" id="bb-upload-icon" class="bb-button bb-button-secondary">Upload Image</button>
                                <div id="bb-icon-preview" style="margin-top:12px;">
                                    <?php if (!empty($s['icon_url'])): ?>
                                    <img src="<?php echo esc_url($s['icon_url']); ?>" style="max-width:60px;max-height:60px;border-radius:50%;">
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                    </div>
					<!-- Anti-Spam -->
					<div id="section-antispam" class="bb-section">
						<div class="bb-section-header">
							<h2>Anti-Spam Protection</h2>
							<p>Protect your chatbot from abuse and spam</p>
						</div>
						<div class="bb-grid">
							<div class="bb-field">
								<label class="bb-label">
									<span class="bb-label-text">Enable Anti-Spam</span>
									<label class="bb-toggle">
										<input type="checkbox" name="<?php echo $o; ?>[enable_antispam]" value="1" <?php checked($s['enable_antispam'], 1); ?>>
										<span class="bb-toggle-slider"></span>
									</label>
								</label>
								<p class="bb-hint">Enable rate limiting and IP blacklist protection</p>
							</div>
							<div class="bb-field">
								<label class="bb-label">
									<span class="bb-label-text">Rate Limit - Max Messages</span>
									<input type="number" name="<?php echo $o; ?>[rate_limit_messages]" value="<?php echo esc_attr($s['rate_limit_messages']); ?>" class="bb-input" min="1" max="100">
								</label>
								<p class="bb-hint">Maximum number of messages allowed per IP</p>
							</div>
							<div class="bb-field">
								<label class="bb-label">
									<span class="bb-label-text">Rate Limit - Time Window (seconds)</span>
									<input type="number" name="<?php echo $o; ?>[rate_limit_time]" value="<?php echo esc_attr($s['rate_limit_time']); ?>" class="bb-input" min="10" max="3600">
								</label>
								<p class="bb-hint">Time window in seconds (60 = 1 minute, 3600 = 1 hour)</p>
							</div>
						</div>
					</div>

                    <!-- Context -->
                    <div id="section-context" class="bb-section">
                        <div class="bb-section-header">
                            <h2>Context</h2>
                            <p>AI system prompts and knowledge base</p>
                        </div>
                        <div class="bb-context-grid">
                            <div class="bb-field bb-field-full">
                                <label class="bb-label">
                                    <span class="bb-label-text">English Context</span>
                                    <textarea name="<?php echo $o; ?>[context_en]" rows="4" class="bb-textarea" placeholder="You are a helpful banking assistant."><?php echo esc_textarea($s['context_en']); ?></textarea>
                                </label>
                            </div>
                            <div class="bb-field bb-field-full">
                                <label class="bb-label">
                                    <span class="bb-label-text">Arabic Context</span>
                                    <textarea name="<?php echo $o; ?>[context_ar]" rows="4" class="bb-textarea" style="direction:rtl;text-align:right;" placeholder="أنت مساعد مصرفي مفيد"><?php echo esc_textarea($s['context_ar']); ?></textarea>
                                </label>
                            </div>
                            <div class="bb-field bb-field-full">
                                <label class="bb-label">
                                    <span class="bb-label-text">Products & Services</span>
                                    <textarea name="<?php echo $o; ?>[context_products]" rows="3" class="bb-textarea" placeholder="Savings: 0.5% APY, Checking: Free..."><?php echo esc_textarea($s['context_products']); ?></textarea>
                                </label>
                            </div>
                            <div class="bb-field bb-field-full">
                                <label class="bb-label">
                                    <span class="bb-label-text">Customer Services</span>
                                    <textarea name="<?php echo $o; ?>[context_services]" rows="3" class="bb-textarea" placeholder="Hours: Mon-Fri 9-5, Phone: 1-800-BANK..."><?php echo esc_textarea($s['context_services']); ?></textarea>
                                </label>
                            </div>
                            <div class="bb-field bb-field-full">
                                <label class="bb-label">
                                    <span class="bb-label-text">Policies & Guidelines</span>
                                    <textarea name="<?php echo $o; ?>[context_policies]" rows="3" class="bb-textarea" placeholder="FDIC insured, Privacy policy..."><?php echo esc_textarea($s['context_policies']); ?></textarea>
                                </label>
                            </div>
                        </div>
                    </div>
                    <!-- Advanced -->
                    <div id="section-advanced" class="bb-section">
                        <div class="bb-section-header">
                            <h2>Advanced</h2>
                            <p>Shadow intensity and animation settings</p>
                        </div>
                        <div class="bb-grid">
                            <div class="bb-field">
                                <label class="bb-label">
                                    <span class="bb-label-text">Shadow Intensity</span>
                                    <select name="<?php echo $o; ?>[shadow_intensity]" class="bb-select">
                                        <option value="none" <?php selected($s['shadow_intensity'], 'none'); ?>>None</option>
                                        <option value="light" <?php selected($s['shadow_intensity'], 'light'); ?>>Light</option>
                                        <option value="medium" <?php selected($s['shadow_intensity'], 'medium'); ?>>Medium</option>
                                        <option value="strong" <?php selected($s['shadow_intensity'], 'strong'); ?>>Strong</option>
                                    </select>
                                </label>
                            </div>
                            <div class="bb-field">
                                <label class="bb-label">
                                    <span class="bb-label-text">Animation Speed</span>
                                    <select name="<?php echo $o; ?>[animation_speed]" class="bb-select">
                                        <option value="slow" <?php selected($s['animation_speed'], 'slow'); ?>>Slow</option>
                                        <option value="normal" <?php selected($s['animation_speed'], 'normal'); ?>>Normal</option>
                                        <option value="fast" <?php selected($s['animation_speed'], 'fast'); ?>>Fast</option>
                                    </select>
                                </label>
                            </div>
                        </div>
                    </div>
                    <div class="bb-footer">
                        <?php submit_button('Save Settings', 'primary bb-button-save', 'submit', false); ?>
                    </div>
                </form>
            </div>
        </div>
        <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        .bb-admin {
            display: flex;
            min-height: 100vh;
            background: #f8f9fa;
            font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif;
            margin: -20px 0 0 -20px;
        }
        .bb-sidebar {
            width: 260px;
            background: #1e293b;
            color: white;
            position: fixed;
            height: 100vh;
            overflow-y: auto;
        }
        .bb-brand {
            padding: 32px 24px;
            border-bottom: 1px solid rgba(255,255,255,0.1);
        }
        .bb-brand-name {
            font-size: 24px;
            font-weight: 700;
            margin-bottom: 4px;
        }
        .bb-brand-subtitle {
            font-size: 14px;
            opacity: 0.6;
        }
        .bb-nav {
            padding: 16px 0;
        }
        .bb-nav-item {
            display: flex;
            align-items: center;
            gap: 12px;
            padding: 12px 24px;
            color: rgba(255,255,255,0.7);
            text-decoration: none;
            transition: all 0.2s;
            border-left: 3px solid transparent;
        }
        .bb-nav-item:hover {
            background: rgba(255,255,255,0.05);
            color: white;
        }
        .bb-nav-item.active {
            background: rgba(255,255,255,0.1);
            color: white;
            border-left-color: #3b82f6;
        }
        .bb-nav-icon {
            font-size: 18px;
        }
        .bb-nav-label {
            font-size: 14px;
            font-weight: 500;
        }
        .bb-main {
            margin-left: 260px;
            flex: 1;
            padding: 40px;
            max-width: 1200px;
        }
        .bb-section {
            display: none;
            background: white;
            border-radius: 12px;
            padding: 32px;
            box-shadow: 0 1px 3px rgba(0,0,0,0.1);
        }
        .bb-section.active {
            display: block;
            animation: fadeIn 0.3s;
        }
        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(10px); }
            to { opacity: 1; transform: translateY(0); }
        }
        .bb-section-header {
            margin-bottom: 32px;
            padding-bottom: 20px;
            border-bottom: 2px solid #f1f5f9;
        }
        .bb-section-header h2 {
            font-size: 24px;
            font-weight: 700;
            color: #1e293b;
            margin-bottom: 8px;
        }
        .bb-section-header p {
            color: #64748b;
            font-size: 14px;
        }
        .bb-grid {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 24px;
        }
        .bb-color-grid {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 20px;
        }
        .bb-context-grid {
            display: flex;
            flex-direction: column;
            gap: 24px;
        }
        .bb-field {
            display: flex;
            flex-direction: column;
            gap: 8px;
        }
        .bb-field-full {
            grid-column: 1 / -1;
        }
        .bb-label {
            display: flex;
            flex-direction: column;
            gap: 8px;
        }
        .bb-label-text {
            font-size: 14px;
            font-weight: 600;
            color: #334155;
        }
        .bb-input,
        .bb-select,
        .bb-textarea {
            width: 100%;
            padding: 10px 14px;
            border: 1px solid #e2e8f0;
            border-radius: 8px;
            font-size: 14px;
            font-family: inherit;
            transition: all 0.2s;
            background: white;
        }
        .bb-input:focus,
        .bb-select:focus,
        .bb-textarea:focus {
            outline: none;
            border-color: #3b82f6;
            box-shadow: 0 0 0 3px rgba(59,130,246,0.1);
        }
        .bb-textarea {
            resize: vertical;
        }
        .bb-hint {
            font-size: 13px;
            color: #64748b;
        }
        .bb-toggle {
            position: relative;
            display: inline-block;
            width: 48px;
            height: 26px;
        }
        .bb-toggle input {
            opacity: 0;
            width: 0;
            height: 0;
        }
        .bb-toggle-slider {
            position: absolute;
            cursor: pointer;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background-color: #cbd5e1;
            transition: .3s;
            border-radius: 26px;
        }
        .bb-toggle-slider:before {
            position: absolute;
            content: "";
            height: 18px;
            width: 18px;
            left: 4px;
            bottom: 4px;
            background-color: white;
            transition: .3s;
            border-radius: 50%;
        }
        .bb-toggle input:checked + .bb-toggle-slider {
            background-color: #3b82f6;
        }
        .bb-toggle input:checked + .bb-toggle-slider:before {
            transform: translateX(22px);
        }
        .bb-color-field {
            display: flex;
            flex-direction: column;
            gap: 8px;
        }
        .bb-color-label {
            font-size: 13px;
            font-weight: 600;
            color: #334155;
        }
        .bb-button {
            padding: 10px 20px;
            border: none;
            border-radius: 8px;
            font-size: 14px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.2s;
        }
        .bb-button-secondary {
            background: #f1f5f9;
            color: #334155;
        }
        .bb-button-secondary:hover {
            background: #e2e8f0;
        }
        .bb-button-save {
            background: #3b82f6 !important;
            color: white !important;
            padding: 12px 32px !important;
            font-size: 15px !important;
            border: none !important;
            box-shadow: 0 2px 8px rgba(59,130,246,0.3) !important;
        }
        .bb-button-save:hover {
            background: #2563eb !important;
            transform: translateY(-1px);
            box-shadow: 0 4px 12px rgba(59,130,246,0.4) !important;
        }
        .bb-footer {
            margin-top: 32px;
            padding-top: 24px;
            border-top: 2px solid #f1f5f9;
            display: flex;
            justify-content: flex-end;
        }
        .bb-icon-option {
            display: none;
        }
        @media (max-width: 1024px) {
            .bb-grid {
                grid-template-columns: 1fr;
            }
            .bb-color-grid {
                grid-template-columns: repeat(2, 1fr);
            }
        }
        </style>
        <?php
    }
    public function analytics_dashboard() {
        if (!current_user_can('manage_options')) {
            return;
        }
        global $wpdb;
        $table_name = $wpdb->prefix . 'bankbot_conversations';
        
        // Get initial date range (default to last 7 days)
        $current_date = date('Y-m-d');
        $week_ago = date('Y-m-d', strtotime('-7 days'));
        
        // Get analytics data with default filters (last 7 days)
        $total_conversations = $wpdb->get_var($wpdb->prepare("SELECT COUNT(*) FROM $table_name WHERE created_at BETWEEN %s AND %s", $week_ago, $current_date));
        $most_common_queries = $wpdb->get_results($wpdb->prepare("
            SELECT user_message, COUNT(*) as count 
            FROM $table_name 
            WHERE created_at BETWEEN %s AND %s
            GROUP BY user_message 
            ORDER BY count DESC 
            LIMIT 5
        ", $week_ago, $current_date));
        $recent_conversations = $wpdb->get_results($wpdb->prepare("
            SELECT * 
            FROM $table_name 
            WHERE created_at BETWEEN %s AND %s
            ORDER BY created_at DESC 
            LIMIT 10
        ", $week_ago, $current_date));
        $daily_trend = $wpdb->get_results($wpdb->prepare("
            SELECT DATE(created_at) as date, COUNT(*) as count 
            FROM $table_name 
            WHERE created_at BETWEEN %s AND %s
            GROUP BY DATE(created_at) 
            ORDER BY date DESC 
            LIMIT 7
        ", $week_ago, $current_date));
        
        // Prepare chart data
        $chart_data = array();
        foreach ($daily_trend as $data) {
            $chart_data[] = array(
                'x' => $data->date,
                'y' => (int)$data->count
            );
        }
        $chart_data_json = json_encode($chart_data);
        
        // Enqueue Chart.js script
        wp_enqueue_script('chart-js', 'https://cdn.jsdelivr.net/npm/chart.js', array(), '4.4.0', true);
        
        // Render dashboard
        ?>
        <div class="wrap">
            <h1>Conversation Analytics Dashboard</h1>
            
            <!-- Filters Section -->
            <div class="analytics-filters">
                <div class="filter-group">
                    <label for="start_date">Start Date:</label>
                    <input type="date" id="start_date" name="start_date" value="<?php echo $week_ago; ?>">
                </div>
                
                <div class="filter-group">
                    <label for="end_date">End Date:</label>
                    <input type="date" id="end_date" name="end_date" value="<?php echo $current_date; ?>">
                </div>
                
                <div class="filter-group">
                    <label for="sort_order">Sort Order:</label>
                    <select id="sort_order" name="sort_order">
                        <option value="desc">Newest First</option>
                        <option value="asc">Oldest First</option>
                    </select>
                </div>
                
                <button id="apply_filters" class="button button-primary">Apply Filters</button>
                <button id="reset_filters" class="button button-secondary">Reset</button>
            </div>
            
            <div class="analytics-cards">
                <div class="card">
                    <h2>Total Conversations</h2>
                    <p class="card-value" id="total_conversations"><?php echo esc_html($total_conversations); ?></p>
                </div>
                <div class="card">
                    <h2>Active Days</h2>
                    <p class="card-value" id="active_days"><?php echo count($daily_trend); ?></p>
                </div>
                <div class="card">
                    <h2>Avg. Daily Conversations</h2>
                    <p class="card-value" id="avg_daily"><?php echo number_format($total_conversations / count($daily_trend), 1); ?></p>
                </div>
            </div>
            
            <div class="analytics-chart-container">
                <h2>Conversation Trend</h2>
                <canvas class="analytics-chart-canvas" id="conversationTrendChart" width="800" height="400"></canvas>
            </div>
            
            <h2>Most Common Queries</h2>
            <table class="wp-list-table widefat fixed striped" id="most_common_queries_table">
                <thead>
                    <tr>
                        <th>Query</th>
                        <th>Count</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($most_common_queries as $query): ?>
                    <tr>
                        <td><?php echo esc_html($query->user_message); ?></td>
                        <td><?php echo esc_html($query->count); ?></td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
            
			<h2>All Conversations</h2>
			<div class="conversations-filters">
				<div class="filter-group">
					<label for="search_user_message">Search User Message:</label>
					<input type="text" id="search_user_message" name="search_user_message" placeholder="Enter keyword...">
				</div>
				<div class="filter-group">
					<label for="search_bot_response">Search Bot Response:</label>
					<input type="text" id="search_bot_response" name="search_bot_response" placeholder="Enter keyword...">
				</div>
				<div class="filter-group">
					<label for="search_ip">Search IP:</label>
					<input type="text" id="search_ip" name="search_ip" placeholder="Enter IP address...">
				</div>
				<div class="filter-group">
					<label for="search_hostname">Search Hostname</label>
					<input type="text" id="search_hostname" placeholder="Search hostname...">
				</div>
				<div class="filter-group">
					<label for="sort_by">Sort by:</label>
					<select id="sort_by" name="sort_by">
						<option value="newest">Newest First</option>
						<option value="oldest">Oldest First</option>
						<option value="az">A-Z</option>
						<option value="za">Z-A</option>
						<option value="this-year">This Year</option>
						<option value="this-month">This Month</option>
						<option value="this-week">This Week</option>
						<option value="today">Today</option>
					</select>
				</div>
				<div class="filter-group">
					<label for="page">Page:</label>
					<input type="number" id="page" name="page" value="1" min="1" style="width: 60px;">
					<button id="go-to-page">Go</button>
				</div>
			</div>
			<div class="pagination-controls">
				<button id="prev-page" disabled>Previous</button>
				<span id="page-info">Page 1 of 1</span>
				<button id="next-page">Next</button>
			</div>
			<table class="wp-list-table widefat fixed striped" id="all_conversations_table">
				<thead>
					<tr>
						<th>Date</th>
						<th>User Message</th>
						<th>Bot Response</th>
						<th>IP Address</th>
						<th>Hostname</th>
					</tr>
				</thead>
				<tbody>
					<!-- Data will be loaded via AJAX -->
				</tbody>
			</table>
			<h2>Data Management</h2>
			<div class="data-management-panel">
				<div class="management-section">
					<h3>Export/Import Conversations</h3>
					<button id="export-csv" class="button button-primary">📥 Export to CSV</button>
					<div style="margin-top: 10px;">
						<input type="file" id="import-csv-file" accept=".csv" style="display:none;">
						<button id="import-csv" class="button button-secondary">📤 Import from CSV</button>
					</div>
				</div>
				
				<div class="management-section">
					<h3>Delete Conversations</h3>
					<button id="delete-all" class="button button-danger">🗑️ Delete All</button>
					<div style="margin-top: 10px;">
						<input type="date" id="delete-start-date" placeholder="Start Date">
						<input type="date" id="delete-end-date" placeholder="End Date">
						<button id="delete-date-range" class="button button-danger">🗑️ Delete Date Range</button>
					</div>
				</div>
				
				<div class="management-section">
					<h3>IP Blacklist</h3>
					<textarea id="ip-blacklist" rows="5" placeholder="Enter IP addresses (one per line)"><?php echo esc_textarea(get_option('bankbot_ip_blacklist', '')); ?></textarea>
					<button id="save-blacklist" class="button button-primary">Save Blacklist</button>
				</div>
				
				<div class="management-section">
					<h3>Anti-Spam Settings</h3>
					<?php 
					$s = $this->get_settings();
					$status_color = $s['enable_antispam'] ? 'green' : 'red';
					$status_text = $s['enable_antispam'] ? '✓ Active' : '✗ Disabled';
					$rate_text = $s['rate_limit_messages'] . ' messages per ' . ($s['rate_limit_time'] == 60 ? '1 minute' : ($s['rate_limit_time'] . ' seconds'));
					?>
					<p><strong>Status:</strong> <span style="color: <?php echo $status_color; ?>;"><?php echo $status_text; ?></span></p>
					<p><strong>Rate Limit:</strong> <?php echo $rate_text; ?> per IP</p>
					<a href="<?php echo admin_url('admin.php?page=bankbot'); ?>" class="button button-secondary" style="margin-top: 8px;">Configure Settings</a>
				</div>
			</div>

        </div>
        
        <script>
        jQuery(document).ready(function($) {
			const nonce = '<?php echo wp_create_nonce('bb_nonce'); ?>';
			
            // Initialize Chart
            const ctx = document.getElementById('conversationTrendChart').getContext('2d');
            const chartData = <?php echo $chart_data_json; ?>;
            
            const chart = new Chart(ctx, {
                type: 'line',
                data: {
                    labels: chartData.map(item => item.x),
                    datasets: [{
                        label: 'Daily Conversations',
                        data: chartData.map(item => item.y),
                        borderColor: '#3b82f6',
                        backgroundColor: 'rgba(59, 130, 246, 0.1)',
                        borderWidth: 2,
                        pointRadius: 4,
                        fill: true
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    scales: {
                        x: {
                            title: {
                                display: true,
                                text: 'Date'
                            }
                        },
                        y: {
                            title: {
                                display: true,
                                text: 'Number of Conversations'
                            },
                            beginAtZero: true
                        }
                    }
                }
            });
            
            // Function to load analytics data
            function loadAnalyticsData() {
                const startDate = $('#start_date').val();
                const endDate = $('#end_date').val();
                const sortOrder = $('#sort_order').val();
                
                $.ajax({
                    url: ajaxurl,
                    type: 'POST',
                    data: {
                        action: 'bankbot_get_filtered_analytics',
                        nonce: '<?php echo wp_create_nonce('bb_nonce'); ?>',
                        start_date: startDate,
                        end_date: endDate,
                        sort_order: sortOrder
                    },
                    success: function(response) {
                        if (response.success) {
                            const data = response.data;
                            
                            // Update cards
                            $('#total_conversations').text(data.total_conversations);
                            $('#active_days').text(data.active_days);
                            $('#avg_daily').text(data.avg_daily);
                            
                            // Update chart
                            chart.data.labels = data.chart_data.map(item => item.x);
                            chart.data.datasets[0].data = data.chart_data.map(item => item.y);
                            chart.update();
                            
                            // Update most common queries table
                            let commonQueriesHtml = '';
                            data.most_common_queries.forEach(function(query) {
                                commonQueriesHtml += `
                                    <tr>
                                        <td>${query.user_message}</td>
                                        <td>${query.count}</td>
                                    </tr>
                                `;
                            });
                            $('#most_common_queries_table tbody').html(commonQueriesHtml);
                            
                            // Update recent conversations table
                            // let conversationsHtml = '';
                            // data.recent_conversations.forEach(function(conv) {
                                // conversationsHtml += `
                                    // <tr>
                                        // <td>${conv.created_at}</td>
                                        // <td style="max-width: 300px; word-wrap: break-word;">${conv.user_message}</td>
                                        // <td style="max-width: 300px; word-wrap: break-word;">${conv.bot_response}</td>
                                    // </tr>
                                // `;
                            // });
                            // $('#recent_conversations_table tbody').html(conversationsHtml);
                        }
                    },
                    error: function() {
                        alert('Error loading analytics data.');
                    }
                });
            }
            
            // Apply filters
            $('#apply_filters').on('click', function() {
                loadAnalyticsData();
            });
            
            // Reset filters
            $('#reset_filters').on('click', function() {
                const currentDate = new Date();
                const weekAgo = new Date();
                weekAgo.setDate(weekAgo.getDate() - 7);
                
                $('#start_date').val(weekAgo.toISOString().split('T')[0]);
                $('#end_date').val(currentDate.toISOString().split('T')[0]);
                $('#sort_order').val('desc');
                
                loadAnalyticsData();
            });
			
			// For the All Conversations table
			let currentPage = 1;
			const conversationsPerPage = 20;

			function loadAllConversations() {
				const searchUserMessage = $('#search_user_message').val();
				const searchBotResponse = $('#search_bot_response').val();
				const searchIp = $('#search_ip').val();
				const searchHostname = $('#search_hostname').val();
				const sortBy = $('#sort_by').val();
				const page = $('#page').val();
				
				$.post(ajaxurl, {
					action: 'bankbot_get_all_conversations',
					nonce: nonce,
					search_user_message: searchUserMessage,
					search_bot_response: searchBotResponse,
					search_ip: searchIp,
					search_hostname: searchHostname,
					sort_by: sortBy,
					page: page
				}, function(response) {
					if (response.success) {
						let html = '';
						response.data.conversations.forEach(function(conv) {
							html += `<tr>
								<td>${conv.created_at}</td>
								<td style="max-width: 300px; word-wrap: break-word;">${conv.user_message}</td>
								<td style="max-width: 300px; word-wrap: break-word;">${conv.bot_response}</td>
								<td>${conv.ip_address || 'N/A'}</td>
								<td>${conv.hostname || 'N/A'}</td>
							</tr>`;
						});
						$('#all_conversations_table tbody').html(html);
						$('#total-pages').text(response.data.total_pages);
						
						// Update pagination buttons
						$('#prev-page').prop('disabled', response.data.page <= 1);
						$('#next-page').prop('disabled', response.data.page >= response.data.total_pages);
					}
				});
			}


			// Initialize the table
			loadAllConversations();

			// Event listeners for filters
			$('#search_user_message, #search_bot_response, #search_ip, #search_hostname, #sort_by').on('change keyup', function() {
				$('#page').val(1);
				loadAllConversations();
			});



			$('#page').on('change', function() {
				loadAllConversations();
			});

			$('#go-to-page').on('click', function() {
				loadAllConversations();
			});

			$('#prev-page').on('click', function() {
				let currentPageValue = parseInt($('#page').val()) || 1;
				currentPageValue = Math.max(1, currentPageValue - 1);
				$('#page').val(currentPageValue);
				loadAllConversations();
			});

			$('#next-page').on('click', function() {
				let currentPageValue = parseInt($('#page').val()) || 1;
				currentPageValue = currentPageValue + 1;
				$('#page').val(currentPageValue);
				loadAllConversations();
			});
			// Export CSV
			$('#export-csv').on('click', function() {
				window.location.href = ajaxurl + '?action=bankbot_export_csv&nonce=<?php echo wp_create_nonce('bb_nonce'); ?>';
			});

			// Import CSV
			$('#import-csv').on('click', function() {
				$('#import-csv-file').click();
			});

			$('#import-csv-file').on('change', function() {
				const file = this.files[0];
				if (!file) return;
				
				const formData = new FormData();
				formData.append('action', 'bankbot_import_csv');
				formData.append('nonce', '<?php echo wp_create_nonce('bb_nonce'); ?>');
				formData.append('csv_file', file);
				
				$.ajax({
					url: ajaxurl,
					type: 'POST',
					data: formData,
					processData: false,
					contentType: false,
					success: function(response) {
						alert(response.success ? response.data.msg : 'Import failed');
						if (response.success) loadAllConversations();
					}
				});
			});

			// Delete All
			$('#delete-all').on('click', function() {
				if (!confirm('Are you sure you want to delete ALL conversations? This cannot be undone!')) return;
				
				$.post(ajaxurl, {
					action: 'bankbot_delete_conversations',
					nonce: '<?php echo wp_create_nonce('bb_nonce'); ?>',
					delete_type: 'all'
				}, function(response) {
					alert(response.data.msg);
					if (response.success) loadAllConversations();
				});
			});

			// Delete Date Range
			$('#delete-date-range').on('click', function() {
				const startDate = $('#delete-start-date').val();
				const endDate = $('#delete-end-date').val();
				
				if (!startDate || !endDate) {
					alert('Please select both start and end dates');
					return;
				}
				
				if (!confirm(`Delete all conversations from ${startDate} to ${endDate}?`)) return;
				
				$.post(ajaxurl, {
					action: 'bankbot_delete_conversations',
					nonce: '<?php echo wp_create_nonce('bb_nonce'); ?>',
					delete_type: 'date_range',
					start_date: startDate,
					end_date: endDate
				}, function(response) {
					alert(response.data.msg);
					if (response.success) loadAllConversations();
				});
			});

			// Save Blacklist
			$('#save-blacklist').on('click', function() {
				const blacklist = $('#ip-blacklist').val();
				
				$.post(ajaxurl, {
					action: 'bankbot_save_blacklist',
					nonce: '<?php echo wp_create_nonce('bb_nonce'); ?>',
					blacklist: blacklist
				}, function(response) {
					alert(response.data.msg);
				});
			});

        });
        </script>
        <style>
		@import url('https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap');

		.wrap {
			font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', 'Noto Sans', Helvetica, Arial, sans-serif;
			padding: 32px 40px;
			max-width: 1400px;
			margin: 0 auto;
			background: #ffffff;
			min-height: 100vh;
		}

		.wrap > h1 {
			font-size: 32px;
			font-weight: 600;
			color: #24292f;
			margin-bottom: 24px;
			letter-spacing: -0.5px;
			padding-bottom: 16px;
			border-bottom: 1px solid #d0d7de;
		}

		.analytics-filters {
			display: flex;
			gap: 12px;
			margin-bottom: 24px;
			align-items: flex-end;
			flex-wrap: wrap;
			background: #f6f8fa;
			padding: 16px;
			border-radius: 6px;
			border: 1px solid #d0d7de;
		}

		.filter-group {
			display: flex;
			flex-direction: column;
			gap: 6px;
			min-width: 180px;
		}

		.filter-group label {
			font-weight: 600;
			font-size: 12px;
			color: #57606a;
		}

		.filter-group input[type="date"],
		.filter-group select,
		.filter-group input[type="text"],
		.filter-group input[type="number"] {
			padding: 5px 12px;
			border: 1px solid #d0d7de;
			border-radius: 6px;
			font-size: 14px;
			font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif;
			transition: all 0.2s ease;
			background: #ffffff;
			color: #24292f;
			height: 32px;
		}

		.filter-group input:focus,
		.filter-group select:focus {
			outline: none;
			border-color: #0969da;
			box-shadow: 0 0 0 3px rgba(9,105,218,0.3);
		}

		.button {
			padding: 5px 16px !important;
			border-radius: 6px !important;
			font-weight: 500 !important;
			font-size: 14px !important;
			cursor: pointer !important;
			transition: all 0.2s ease !important;
			border: 1px solid #d0d7de !important;
			height: 32px !important;
			line-height: 20px !important;
		}

		.button-primary {
			background: #2da44e !important;
			color: white !important;
			border-color: rgba(27,31,36,0.15) !important;
		}

		.button-primary:hover {
			background: #2c974b !important;
		}

		.button-secondary {
			background: #f6f8fa !important;
			color: #24292f !important;
			border-color: #d0d7de !important;
		}

		.button-secondary:hover {
			background: #f3f4f6 !important;
			border-color: #d0d7de !important;
		}

		.analytics-cards {
			display: grid;
			grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
			gap: 16px;
			margin-bottom: 32px;
		}

		.card {
			background: #ffffff;
			border-radius: 6px;
			padding: 24px;
			border: 1px solid #d0d7de;
			text-align: center;
			transition: all 0.2s ease;
		}

		.card:hover {
			border-color: #57606a;
			box-shadow: 0 3px 12px rgba(140,149,159,0.15);
		}

		.card h2 {
			font-size: 12px;
			font-weight: 600;
			color: #57606a;
			text-transform: uppercase;
			letter-spacing: 0.5px;
			margin-bottom: 12px;
		}

		.card-value {
			font-size: 40px;
			font-weight: 600;
			color: #0969da;
			margin: 0;
			line-height: 1.2;
		}

		.analytics-chart-container {
			background: #ffffff;
			border-radius: 6px;
			padding: 24px;
			margin-bottom: 32px;
			border: 1px solid #d0d7de;
		}

		.analytics-chart-container h2 {
			font-size: 16px;
			font-weight: 600;
			color: #24292f;
			margin-bottom: 20px;
			padding-bottom: 12px;
			border-bottom: 1px solid #d0d7de;
		}

		.analytics-chart-canvas {
			max-height: 400px !important;
			width: 100% !important;
		}

		.wp-list-table {
			width: 100%;
			margin-top: 16px;
			background: #ffffff;
			border-radius: 6px;
			overflow: hidden;
			border: 1px solid #d0d7de;
			border-collapse: separate;
			border-spacing: 0;
		}

		.wrap > h2 {
			font-size: 20px;
			font-weight: 600;
			color: #24292f;
			margin: 40px 0 16px 0;
			padding-bottom: 8px;
			border-bottom: 1px solid #d0d7de;
		}

		.wp-list-table thead {
			background: #f6f8fa;
			border-bottom: 1px solid #d0d7de;
		}

		.wp-list-table thead th {
			padding: 12px 16px;
			text-align: left;
			font-weight: 600;
			font-size: 12px;
			color: #57606a !important;
			border: none !important;
			border-bottom: 1px solid #d0d7de !important;
		}

		.wp-list-table tbody tr {
			transition: all 0.1s ease;
			border-bottom: 1px solid #d0d7de;
		}

		.wp-list-table tbody tr:hover {
			background: #f6f8fa;
		}

		.wp-list-table tbody td {
			padding: 12px 16px;
			font-size: 13px;
			color: #24292f;
			border: none !important;
			border-bottom: 1px solid #d0d7de !important;
		}

		.wp-list-table.striped > tbody > tr:nth-child(odd) {
			background: #ffffff;
		}

		.wp-list-table.striped > tbody > tr:nth-child(even) {
			background: #f6f8fa;
		}

		.conversations-filters {
			display: flex;
			gap: 12px;
			margin-bottom: 16px;
			align-items: flex-end;
			flex-wrap: wrap;
			background: #f6f8fa;
			padding: 16px;
			border-radius: 6px;
			border: 1px solid #d0d7de;
		}

		.pagination-controls {
			display: flex;
			gap: 8px;
			margin-bottom: 16px;
			align-items: center;
			background: #f6f8fa;
			padding: 12px 16px;
			border-radius: 6px;
			border: 1px solid #d0d7de;
		}

		.pagination-controls button {
			padding: 5px 16px;
			border-radius: 6px;
			font-weight: 500;
			font-size: 13px;
			cursor: pointer;
			transition: all 0.1s ease;
			border: 1px solid #d0d7de;
			background: #ffffff;
			color: #24292f;
			height: 32px;
			line-height: 20px;
		}

		.pagination-controls button:not(:disabled):hover {
			background: #f3f4f6;
			border-color: #d0d7de;
		}

		.pagination-controls button:disabled {
			opacity: 0.5;
			cursor: not-allowed;
			background: #f6f8fa;
		}

		#page-info {
			font-weight: 500;
			color: #57606a;
			padding: 0 12px;
			font-size: 13px;
		}

		#go-to-page {
			padding: 5px 16px;
			border-radius: 6px;
			font-weight: 500;
			font-size: 13px;
			cursor: pointer;
			transition: all 0.1s ease;
			border: 1px solid rgba(27,31,36,0.15);
			background: #2da44e;
			color: white;
			height: 32px;
			line-height: 20px;
		}

		#go-to-page:hover {
			background: #2c974b;
		}

		/* Input styling for page number */
		.pagination-controls input[type="number"] {
			width: 60px;
			padding: 5px 8px;
			border: 1px solid #d0d7de;
			border-radius: 6px;
			font-size: 13px;
			background: #ffffff;
			color: #24292f;
			text-align: center;
			height: 32px;
		}

		.pagination-controls input[type="number"]:focus {
			outline: none;
			border-color: #0969da;
			box-shadow: 0 0 0 3px rgba(9,105,218,0.3);
		}

		@media (max-width: 768px) {
			.wrap {
				padding: 20px;
			}
			
			.analytics-cards {
				grid-template-columns: 1fr;
			}
			
			.analytics-filters,
			.conversations-filters {
				flex-direction: column;
				align-items: stretch;
			}
			
			.filter-group {
				min-width: 100%;
			}
			
			.card-value {
				font-size: 32px;
			}
		}

		/* Scrollbar styling */
		::-webkit-scrollbar {
			width: 10px;
			height: 10px;
		}

		::-webkit-scrollbar-track {
			background: #f6f8fa;
		}

		::-webkit-scrollbar-thumb {
			background: #d0d7de;
			border-radius: 5px;
		}

		::-webkit-scrollbar-thumb:hover {
			background: #afb8c1;
		}

		/* Focus states */
		*:focus {
			outline: none;
		}

		input:focus,
		select:focus,
		button:focus {
			outline: 2px solid #0969da;
			outline-offset: -2px;
		}

		/* Loading state */
		.loading {
			opacity: 0.6;
			pointer-events: none;
		}

		/* Badges and Labels */
		.badge {
			display: inline-block;
			padding: 0 7px;
			font-size: 12px;
			font-weight: 500;
			line-height: 18px;
			border-radius: 2em;
			background: #0969da;
			color: white;
		}

		/* Counter badges */
		.counter {
			display: inline-block;
			padding: 2px 5px;
			font-size: 12px;
			font-weight: 500;
			line-height: 1;
			color: #24292f;
			background: rgba(175,184,193,0.2);
			border-radius: 2em;
		}

		/* Status indicators */
		.status-success {
			color: #1a7f37;
		}

		.status-warning {
			color: #9a6700;
		}

		.status-error {
			color: #cf222e;
		}

		/* Links */
		a {
			color: #0969da;
			text-decoration: none;
		}

		a:hover {
			text-decoration: underline;
		}
		.data-management-panel {
			display: grid;
			grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
			gap: 20px;
			margin-bottom: 40px;
		}

		.management-section {
			background: white;
			border-radius: 6px;
			padding: 20px;
			border: 1px solid #d0d7de;
		}

		.management-section h3 {
			font-size: 16px;
			font-weight: 600;
			color: #24292f;
			margin-bottom: 12px;
		}

		.management-section textarea {
			width: 100%;
			padding: 8px;
			border: 1px solid #d0d7de;
			border-radius: 6px;
			font-size: 13px;
			font-family: monospace;
			margin-bottom: 8px;
		}

		.button-danger {
			background: #cf222e !important;
			color: white !important;
			border-color: rgba(27,31,36,0.15) !important;
		}

		.button-danger:hover {
			background: #a40e26 !important;
		}


        </style>
        <?php
    }
}
add_action('plugins_loaded', function() {
    BankBot_Plugin::instance();
});