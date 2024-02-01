<?php
/*
Plugin Name: My Backup Plugin
Description: A simple WordPress plugin for site and database backup.
Version: 1.3
Author: Levis J.
Plugin URI: https://levisdemo.kualotest1.com/wp-content/plugins/wp-bkp/icon.png
*/

// Add menu item in admin dashboard
add_action('admin_menu', 'my_backup_menu');

function my_backup_menu() {
    add_menu_page(
        'Backup Settings',
        'Backup',
        'manage_options',
        'my_backup_settings',
        'my_backup_settings_page'
    );
}

// Settings page content
function my_backup_settings_page() {
    if (isset($_POST['backup_now'])) {
        // Get selected backup option
        $backup_option = sanitize_text_field($_POST['backup_option']);

        // Perform backup based on the selected option
        if ($backup_option === 'files') {
            backup_files();
        } elseif ($backup_option === 'database') {
            backup_database();
        } elseif ($backup_option === 'both') {
            backup_files();
            backup_database();
        }

        echo '<div class="updated"><p>Backup completed successfully!</p></div>';
    }
    ?>
    <div class="wrap">
        <h1>Backup Settings</h1>
        <p>Plugin created by Levis J.</p>
        <form method="post">
            <p>Choose backup option:</p>
            <label>
                <input type="radio" name="backup_option" value="files" checked> Backup Files Only
            </label>
            <br>
            <label>
                <input type="radio" name="backup_option" value="database"> Backup Database Only
            </label>
            <br>
            <label>
                <input type="radio" name="backup_option" value="both"> Backup Both Files and Database
            </label>
            <br><br>
            <?php wp_nonce_field('my_backup_nonce', 'my_backup_nonce'); ?>
            <input type="submit" name="backup_now" class="button button-primary" value="Backup Now">
        </form>
    </div>
    <?php
}

// Function to perform backup of files
function backup_files() {
    $backup_dir = ABSPATH . '/wp-content/backups/';
    $backup_file = 'site_backup_' . date('Ymd_His') . '.zip';

    if (!is_dir($backup_dir)) {
        mkdir($backup_dir, 0755, true);
    }

    $backup_command = escapeshellcmd("zip -r {$backup_dir}{$backup_file} " . ABSPATH . " -x '*.git*' -x '*wp-content/backups*'");
    shell_exec($backup_command);
}

// Function to perform backup of the database
function backup_database() {
    $backup_dir = ABSPATH . '/wp-content/backups/';
    $db_backup_file = 'database_backup_' . date('Ymd_His') . '.sql';

    if (!is_dir($backup_dir)) {
        mkdir($backup_dir, 0755, true);
    }

    // Check if wp-cli is available
    $wp_cli_check_command = "command -v wp";
    $wp_cli_check_result = shell_exec($wp_cli_check_command);

    if (empty($wp_cli_check_result)) {
        $wp_cli_check_command = "command -v wp-cli";
        $wp_cli_check_result = shell_exec($wp_cli_check_command);

        if (empty($wp_cli_check_result)) {
            echo '<div class="error"><p>wp-cli not found. Please install wp-cli or make sure it is in your PATH.</p></div>';
            return;
        }
    }

    // Determine the binary name (wp or wp-cli)
    $wp_binary = (empty(shell_exec("command -v wp"))) ? 'wp-cli' : 'wp';

    // Construct wp-cli db export command
    $db_backup_command = "$wp_binary db export --path=" . ABSPATH . " --allow-root --skip-plugins --skip-themes --file={$backup_dir}{$db_backup_file}";
}