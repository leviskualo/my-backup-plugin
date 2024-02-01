<?php
/*
Plugin Name: My Backup Plugin
Description: A simple WordPress plugin for site and database backup.
Version: 1.1
Author: Levis J.
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
        $backup_option = isset($_POST['backup_option']) ? $_POST['backup_option'] : '';

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
                <input type="radio" name="backup_option" value="both"> Complete Backup
            </label>
            <br><br>
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

    $backup_command = "zip -r {$backup_dir}{$backup_file} " . ABSPATH . " -x '*.git*' -x '*wp-content/backups*'";
    shell_exec($backup_command);
}

// Function to perform backup of the database
function backup_database() {
    $backup_dir = ABSPATH . '/wp-content/backups/';
    $db_backup_file = 'database_backup_' . date('Ymd_His') . '.sql';

    if (!is_dir($backup_dir)) {
        mkdir($backup_dir, 0755, true);
    }

    $db_backup_command = "mysqldump -u" . DB_USER . " -p" . DB_PASSWORD . " -h" . DB_HOST . " " . DB_NAME . " > {$backup_dir}{$db_backup_file}";
    shell_exec($db_backup_command);
}
