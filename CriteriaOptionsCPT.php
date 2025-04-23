<?php

class Custom_Crud {
    private $table_name;
    private $label;

    public function __construct($table_name, $label) {
        global $wpdb;
        $this->table_name = $wpdb->prefix . $table_name;  // Set table name with prefix
        $this->label = $label;

        // Create the table if it doesn't exist
        add_action('admin_init', [$this, 'create_table']);
        
        // Add custom menu and submenu items
        add_action('admin_menu', [$this, 'add_admin_menu']);
        
        // Handle save and delete AJAX actions
        add_action('wp_ajax_save_entry', [$this, 'save_entry']);
        add_action('wp_ajax_delete_entry', [$this, 'delete_entry']);
        add_action('admin_post_save_entry',  [$this,'handle_save_entry']);
        add_action('admin_post_delete_entry',  [$this,'delete_entry']);
    }

    public function create_table() {
        global $wpdb;

        $table_name = $this->table_name;
        $charset_collate = $wpdb->get_charset_collate();

        $sql = "CREATE TABLE IF NOT EXISTS $table_name (
            id BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT,
            name VARCHAR(255) NOT NULL,
            PRIMARY KEY (id)
        ) $charset_collate;";

        require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
        dbDelta($sql);
    }

    // Add custom admin menu for managing the entries
    public function add_admin_menu() {
        if (!isset($GLOBALS['admin_page_hooks']['criteria-options'])) {
            add_menu_page(
                'Criteria Options', 
                'Criteria Options', 
                'manage_options', 
                'criteria-options', 
                '', 
                'dashicons-list-view',
                20
            );
        }
        add_submenu_page(
            'criteria-options', // Parent menu slug (must be a top-level menu)
            $this->label, // Page title
            $this->label, // Menu title
            'manage_options', 
            $this->table_name, // Unique menu slug
            [$this, 'render_admin_page'], // Callback function
            'dashicons-list-view',
            20
        );
        add_action('admin_menu', function() {
            remove_submenu_page('criteria-options', 'criteria-options');
        }, 999);
    }

    // Render the admin page
    public function render_admin_page() {
        global $wpdb;

        $table_name = $this->table_name;
        $entries = $wpdb->get_results("SELECT * FROM $table_name"); 
         if ($message = get_transient('crud_success_message')) {
            echo '<div class="updated notice is-dismissible"><p>' . esc_html($message) . '</p></div>';
            delete_transient('crud_success_message');
        }
        if ($message = get_transient('crud_error_message')) {
            echo '<div class="error notice is-dismissible"><p>' . esc_html($message) . '</p></div>';
            delete_transient('crud_error_message');
        }

        ?>
        <div class="wrap">
            <h1 class="wp-heading-inline"><?php echo esc_html($this->label); ?></h1>
            <button id="addNew" class="button button-primary">+ Add New</button>
            <hr>
            <table class="wp-list-table widefat fixed striped" id="grampage-table">
                <thead>
                    <tr>
                        <th>Name</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($entries as $entry): ?>
                        <tr>
                            <td><?php echo esc_html($entry->name); ?></td>
                            <td style="display: flex; gap: 10px;">
                                <button class="edit-entry button button-primary" data-id="<?php echo $entry->id; ?>" data-name="<?php echo esc_attr($entry->name); ?>"><span class="dashicons dashicons-edit"></span> Edit</button>
                                <form method="POST" action="<?php echo admin_url('admin-post.php'); ?>" onsubmit="return confirm('Are you sure you want to delete this entry?');">
                                <?php wp_nonce_field('custom_crud_nonce', 'security'); ?>
                                <input type="hidden" name="post_id" value="<?php echo $entry->id; ?>">
                                <input type="hidden" name="post_page_delete" id="post_page_delete" value="<?php echo $this->table_name; ?>">
                                <input type="hidden" name="action" value="delete_entry">
                                <button type="submit" class="delete-entry button button-danger button-danger"  style="background: #d63638; color: #fff;"><span class="dashicons dashicons-trash"></span> Delete</button>
                            </form>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        <script>
                jQuery(document).ready(function($) {
					$('#grampage-table').DataTable({
						"paging": true,
						"searching": true,
						"ordering": true,
						"order": [[0, "asc"]], // Sort by "Option Name" (Column Index 1)
						"language": {
							"search": "Search:"
						},
						"autoWidth": false,
						"columnDefs": [
							{ "orderable": true, "targets": [0, 1] }, 
						]
					});
					$('#grampage-table_filter').addClass('search-box').css({'float': 'right', 'margin-bottom': '10px'});
				});

				</script>
                <style>
					
					.dataTables_wrapper { margin-top: 15px; }
					.dataTables_length { float: left; margin-bottom: 10px; }
					.dataTables_filter { float: right !important; }
					.dataTables_paginate { float: right !important; margin-top: 10px; }
					.dataTables_info { float: left; margin-top: 10px; }
					.search-box input { border: 1px solid #ccc; padding: 5px; }
					.dataTables_length select {
						width: 50px !important; 
						max-width: 100%;
						padding: 5px;
						border: 1px solid #ccc;
						font-size: 14px;
					}
                    .custom-modal-overlay { 
                        display: none; 
                        position: fixed; 
                        top: 0; 
                        left: 0; 
                        width: 100%; 
                        height: 100%; 
                        background: rgba(0, 0, 0, 0.4); 
                        z-index: 9998; 
                    }

                    /* Modal window */
                    .custom-modal { 
                        display: none; 
                        position: fixed; 
                        top: 50%; 
                        left: 50%; 
                        transform: translate(-50%, -50%); 
                        background: #fff; 
                        padding: 30px; 
                        border-radius: 8px; 
                        box-shadow: 0 4px 15px rgba(0, 0, 0, 0.2); 
                        z-index: 9999; 
                        width: 500px; 
                        max-width: 90%; 
                        overflow-y: auto;
                    }

                    /* Modal header */
                    .custom-modal h2 {
                        font-size: 24px;
                        font-weight: 600;
                        margin-bottom: 20px;
                        color: #3498db;
                        text-transform: uppercase;
                        border-bottom: 3px solid #3498db;
                        padding-bottom: 10px;
                    }

                    /* Form inputs */
                    .custom-modal label {
                        font-size: 16px;
                        margin-bottom: 8px;
                        color: #555;
                    }

                    .custom-modal input[type="text"] {
                        width: 100%;
                        padding: 12px;
                        margin-bottom: 20px;
                        border: 1px solid #ddd;
                        border-radius: 4px;
                        font-size: 16px;
                        color: #333;
                        box-sizing: border-box;
                        margin-top: 10px;
                    }

                    .custom-modal input[type="text"]:focus {
                        border-color: #3498db;
                        outline: none;
                        margin-top: 10px;
                    }

                    /* Buttons */
                    .custom-modal button {
                        padding: 10px 20px;
                        font-size: 16px;
                        border-radius: 4px;
                        cursor: pointer;
                        transition: background-color 0.3s ease;
                    }

                    /* Save button */
                    .custom-modal .button-primary {
                        background-color: #3498db;
                        color: #fff;
                        border: none;
                    }

                    .custom-modal .button-primary:hover {
                        background-color: #2980b9;
                    }

                    /* Cancel button */
                    .custom-modal .close-modal {
                        background-color: #e74c3c;
                        color: #fff;
                        border: none;
                    }

                    .custom-modal .close-modal:hover {
                        background-color: #c0392b;
                    }

                    /* Responsive design */
                    @media (max-width: 600px) {
                        .custom-modal {
                            width: 90%;
                        }
                    }
                </style>

        <div class="custom-modal-overlay"></div>
        <div id="crudModal" class="custom-modal">
            <h2 id="modalTitle">Add New </h2>
            <form id="crudForm" method="post" action="<?php echo admin_url('admin-post.php'); ?>">
                <input type="hidden" name="action" value="save_entry">
                <?php wp_nonce_field('custom_crud_nonce', 'security'); ?>
                <input type="hidden" name="post_id" id="post_id">
                <input type="hidden" name="post_page" id="post_page" value="<?php echo $this->table_name; ?>">
                
                <label for="name">Name:</label>
                <input type="text" name="name" id="name" required>
                
                <button type="submit" class="button button-primary">Save</button>
                <button type="button" class="close-modal button">Cancel</button>
            </form>
        </div>

        <script>
            jQuery(document).ready(function ($) {
                function openModal(title, id = '', name = '') {
                    $('#modalTitle').text(title);
                    $('#post_id').val(id);
                    $('#name').val(name);
                    $('.custom-modal, .custom-modal-overlay').fadeIn();
                }

                function closeModal() {
                    $('.custom-modal, .custom-modal-overlay').fadeOut();
                }

                $('#addNew').click(function () {
                    openModal('Add New');
                });

                $('.edit-entry').click(function () {
                    var id = $(this).data('id');
                    var name = $(this).data('name');
                    openModal('Edit Option Name', id, name);
                });

                $('.close-modal, .custom-modal-overlay').click(function () {
                    closeModal();
                });
            });
        </script>
        <?php
    }


    public function handle_save_entry() {
        if (!isset($_POST['security']) || !wp_verify_nonce($_POST['security'], 'custom_crud_nonce')) {
            wp_die('Security check failed');
        }
    
        global $wpdb;
        $post_id = isset($_POST['post_id']) ? intval($_POST['post_id']) : 0;
        $name = sanitize_text_field($_POST['name']);
        $table_name = $_POST['post_page'];
        if (!$post_id) {
            $existing_entry = $wpdb->get_var($wpdb->prepare("SELECT COUNT(*) FROM $table_name WHERE name = %s", $name));
            if ($existing_entry > 0) {
                set_transient('crud_error_message', 'Option Name already exists. Please choose a different name.', 30);
                wp_redirect(admin_url('admin.php?page=' . $table_name));
                exit;
            }
        }
        if ($post_id) {
            $wpdb->update($table_name, ['name' => $name], ['id' => $post_id]);
            set_transient('crud_success_message', 'Option Name updated successfully.', 30);
        } else {
            $wpdb->insert($table_name, ['name' => $name]);
            set_transient('crud_success_message', 'Option Name added successfully.', 30);
        }
        wp_redirect(admin_url('admin.php?page=' . $table_name));
        exit;
    }
    public function delete_entry() {
        if (!isset($_POST['security']) || !wp_verify_nonce($_POST['security'], 'custom_crud_nonce')) {
            wp_die('Security check failed');
        }
    
        global $wpdb;
        $post_id = isset($_POST['post_id']) ? intval($_POST['post_id']) : 0;
        $table_name = $_POST['post_page_delete'];
    
        if ($post_id) {
            $deleted = $wpdb->delete($table_name, ['id' => $post_id]);
    
            if ($deleted) {
                $message = 'Option Name deleted successfully.';
                set_transient('crud_success_message', $message, 30);
            } else {
                $message = 'Failed to delete the Option Name.';
                set_transient('crud_error_message', $message, 30);
            }
        } else {
            $message = 'Invalid Entry ID.';
            set_transient('crud_error_message', $message, 30);
        }
        wp_redirect(admin_url('admin.php?page=' . $table_name));
        exit;
    }
}
new Custom_Crud('grampage', 'Grampage');
new Custom_Crud('paper_type', 'Paper Type');
new Custom_Crud('finishing', 'Finishing');
new Custom_Crud('quantity', 'Quantity');
?>
