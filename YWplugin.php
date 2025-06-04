<?php
/*
Plugin Name: YelloWizard Plugin
Description: Προσαρμοσμένο admin theme με μαύρο και κίτρινο χρώμα για το WordPress backend.
Version: 1.0.0
Author: YelloWizard Digital Agency
Author URI: https://yellowizard.gr/
*/

if ( !class_exists('Puc_v4_Factory') ) {
    require 'plugin-update-checker/plugin-update-checker.php';
}

$updateChecker = Puc_v4_Factory::buildUpdateChecker(
    'https://github.com/yelloWizard/YWplugin/',
    __FILE__,
    'ywplugin' // This slug should match your folder name
);

// Optional: if you're using a different branch
// $updateChecker->setBranch('main');


// Αφαίρεση του WP logo με υψηλή προτεραιότητα
add_action('admin_bar_menu', 'custom_remove_wp_logo', 999);
function custom_remove_wp_logo($wp_admin_bar) {
    $wp_admin_bar->remove_node('wp-logo');
}

// Προσθήκη custom logo ΠΡΙΝ από όλα τα άλλα (π.χ. priority 0)
add_action('admin_bar_menu', 'custom_add_company_logo', 1); // μικρή προτεραιότητα = εμφανίζεται πρώτο
function custom_add_company_logo($wp_admin_bar) {
    $wp_admin_bar->add_node([
        'id'    => 'custom-logo',
        'title' => '<span class="custom-admin-logo"></span>',
        'href'  => admin_url(), // Ή βάλε 'https://yellowizard.gr'
        'meta'  => [
            'title' => 'Yellowizard',
        ],
    ]);
}

// CSS για το logo
add_action('admin_head', 'custom_admin_logo_css');
function custom_admin_logo_css() {
    echo '
    <style>
        #wpadminbar #wp-admin-bar-custom-logo > .ab-item .custom-admin-logo {
            background-image: url("https://yellowizard.gr/wp-content/uploads/2021/06/yellowizard-logo-yellow.png");
            background-size: contain;
            background-repeat: no-repeat;
            display: inline-block;
            width: 20px;
            height: 20px;
            margin-top: 6px;
        }
    </style>
    ';
}


// Καταχώρηση custom admin color scheme
function yellowizard_custom_admin_color() {
    wp_admin_css_color(
        'yellowizard-theme',
        __('YelloWizard Theme'),
        plugin_dir_url(__FILE__) . 'admin-colors.css',
        ['#1d1f20', '#000', '#000', '#ffde10']
    );
}
add_action('admin_init', 'yellowizard_custom_admin_color');

// Ορισμός default admin color scheme σε νέους χρήστες
function yellowizard_set_default_admin_color($user_id) {
    update_user_meta($user_id, 'admin_color', 'yellowizard-theme');
}
add_action('user_register', 'yellowizard_set_default_admin_color');

// Επιβολή χρώματος admin panel σε όλους τους χρήστες
function yellowizard_force_admin_color() {
    $current_user = wp_get_current_user();
    if ($current_user && $current_user->exists()) {
        update_user_meta($current_user->ID, 'admin_color', 'yellowizard-theme');
    }
}
add_action('init', 'yellowizard_force_admin_color');

// Προσθήκη σελίδας "Οδηγίες" στο admin menu
add_action('admin_menu', 'yellowizard_add_instructions_page');

function yellowizard_add_instructions_page() {
    add_menu_page(
        'Οδηγίες Χρήσης',         // Τίτλος σελίδας
        'Οδηγίες',             // Τίτλος στο μενού
        'read',                   // Χρήστες με δικαίωμα πρόσβασης
        'yellowizard-instructions', // Slug
        'yellowizard_render_instructions_page', // Συνάρτηση περιεχομένου
        'dashicons-info',         // Εικονίδιο
        2                         // Θέση στο μενού
    );
}

function yellowizard_render_instructions_page() {
    ?>
    <div class="wrap">
        <h1>Οδηγίες Χρήσης Ιστοσελίδας</h1>
        <p>Ακολουθούν βασικές οδηγίες για τη διαχείριση του site:</p>
        <ol>
            <li>Μετάβαση στις <strong>Σελίδες</strong> για επεξεργασία περιεχομένου.</li>
            <li>Ανεβάστε φωτογραφίες στο <strong>Πολυμέσα</strong>.</li>
            <li>Για προϊόντα, δείτε την ενότητα <strong>Προϊόντα</strong>.</li>
            <li>Για βοήθεια, επικοινωνήστε: <strong>support@example.com</strong></li>
        </ol>
        <p><em>Η ενότητα αυτή προστέθηκε από το πρόσθετο Yellowizard Plugin.</em></p>
    </div>
    <?php
}


//σελιδα πελατη
add_action('admin_menu', 'yellowizard_add_site_status_page');

function yellowizard_add_site_status_page() {
    if (!current_user_can('administrator')) return; // μόνο για administrators

    add_menu_page(
        get_bloginfo('name'), // Τίτλος site ως τίτλος σελίδας
        get_bloginfo('name').' Info',
        'manage_options',
        'yellowizard-site-info',
        'yellowizard_render_site_info_page',
        'dashicons-admin-site',
        2.1
    );
}

function yellowizard_render_site_info_page() {
    // Αν υποβλήθηκε η φόρμα
    $current_user = wp_get_current_user();
    $can_edit = ($current_user->user_email === 'devyellowizard@gmail.com'); // Εσύ μόνο

    if ($can_edit && isset($_POST['yellowizard_save_info'])) {
        update_option('yellowizard_domain_date', sanitize_text_field($_POST['domain_date']));
        update_option('yellowizard_hosting_date', sanitize_text_field($_POST['hosting_date']));
        update_option('yellowizard_support_date', sanitize_text_field($_POST['support_date']));
        update_option('yellowizard_email_info', sanitize_text_field($_POST['email_info']));
        update_option('yellowizard_plugins_info', sanitize_text_field($_POST['plugins_info']));
        echo '<div class="updated"><p>Οι πληροφορίες αποθηκεύτηκαν!</p></div>';
    }

    // Λήψη τιμών
    $domain_date = get_option('yellowizard_domain_date', '');
    $hosting_date = get_option('yellowizard_hosting_date', '');
    $support_date = get_option('yellowizard_support_date', '');
    $email_info = get_option('yellowizard_email_info', '');
    $plugins_info = get_option('yellowizard_plugins_info', '');

    echo '<div class="wrap"><h1>Πληροφορίες Πελάτη</h1><form method="post">';

    yellowizard_render_date_field('Ανανέωση Domain', 'domain_date', $domain_date, $can_edit);
    yellowizard_render_date_field('Ανανέωση Hosting', 'hosting_date', $hosting_date, $can_edit);
    yellowizard_render_date_field('Ανανέωση Υποστήριξης', 'support_date', $support_date, $can_edit);

    echo '<h3>Emails</h3>';
    if ($can_edit) {
        echo '<input type="text" name="email_info" value="' . esc_attr($email_info) . '" class="regular-text">';
    } else {
        echo '<p><strong>' . esc_html($email_info) . '</strong></p>';
    }

    echo '<h3>Ανανεώσεις Plugin</h3>';
    if ($can_edit) {
        echo '<textarea name="plugins_info" rows="5" class="large-text">' . esc_textarea($plugins_info) . '</textarea>';
    } 
    echo '</form></div>';

    echo '<hr style="margin: 30px 0;">';
    echo '<h2>🔗 Χρήσιμα Links</h2>';

    // Σύντομη Περιγραφή
    echo '<p>Ακολουθούν μερικά χρήσιμα εργαλεία που μπορείς να χρησιμοποιήσεις:</p>';

    // Δημιουργία πίνακα με χρήσιμα links και περιγραφές
    echo '<table class="widefat">';
    echo '<thead><tr><th>Χρησιμότητα</th><th>Link</th></tr></thead>';
    echo '<tbody>';
    
    // ChatGPT
    echo '<tr><td>🧠 ChatGPT: Εργαλείο τεχνητής νοημοσύνης για δημιουργία και επεξεργασία κειμένου</td><td><a href="https://chat.openai.com/" target="_blank">ChatGPT</a></td></tr>';
    
    // RedKetchup
    echo '<tr><td>🛠️ RedKetchup: Εργαλεία για την ανάπτυξη και βελτιστοποίηση WordPress ιστοσελίδων</td><td><a href="https://redketchup.io/" target="_blank">RedKetchup Tools</a></td></tr>';
    
    // Προσθήκη άλλων εργαλειών αν χρειάζεται
    // echo '<tr><td>🔧 Άλλο εργαλείο: Περιγραφή εργαλείου</td><td><a href="URL" target="_blank">Link</a></td></tr>';
    
    echo '</tbody>';
    echo '</table>';

    wp_register_script(
        'yellowizard-plugin-news',
        plugins_url('plugin-news-widget.js', __FILE__),
        [], 
        null, 
        true
    );
}

add_shortcode('yellowizard_news_widget', 'yellowizard_render_news_widget');
function yellowizard_render_news_widget() {
    wp_enqueue_script('yellowizard-plugin-news');
    return '<div id="plugin-news-widget"></div>';
}

// ➕ Βοηθητική συνάρτηση: ημερομηνία + χρώμα
function yellowizard_render_date_field($label, $name, $value) {
    $color = 'black';
    if ($value) {
        $days_left = (strtotime($value) - time()) / (60 * 60 * 24);
        if ($days_left <= 3) $color = 'red';
        elseif ($days_left <= 7) $color = 'orange';
    }

    echo "<h3>{$label}</h3>";
    if ($can_edit) {
        echo "<input type='date' name='{$name}' value='{$value}' style='border-left: 10px solid {$color}; padding:5px;'>";
    } else {
        echo "<p style='padding:5px; border-left: 10px solid {$color}; background: #f9f9f9; display:inline-block;'><strong>{$value}</strong></p>";
    }
}

// Χρονοπρογραμματισμός (μία φορά τη μέρα)
add_action('wp', 'yellowizard_schedule_email_check');
function yellowizard_schedule_email_check() {
    if (!wp_next_scheduled('yellowizard_daily_check_event')) {
        wp_schedule_event(time(), 'daily', 'yellowizard_daily_check_event');
    }
}

// Ενέργεια όταν τρέχει το cron
add_action('yellowizard_daily_check_event', 'yellowizard_check_expiration_dates');

function yellowizard_check_expiration_dates() {
    $fields = [
        'domain_date' => 'Domain',
        'hosting_date' => 'Hosting',
        'support_date' => 'Support'
    ];

    $today = time();
    $notify_email = 'devyellowizard@gmail.com'; // άλλαξε το με το δικό σου

    foreach ($fields as $key => $label) {
        $date_str = get_option('yellowizard_' . $key);
        if (!$date_str) continue;

        $timestamp = strtotime($date_str);
        $days_left = floor(($timestamp - $today) / (60 * 60 * 24));

        if ($days_left <= 7 && $days_left >= 0) {
            // Φτιάξε το μήνυμα
            $subject = "🔔 Υπενθύμιση: Η {$label} λήγει σε {$days_left} μέρες για το site " . get_bloginfo('name');
            $body = "Η ημερομηνία λήξης για το {$label} πλησιάζει!\n\n"
                  . "Site: " . get_bloginfo('name') . "\n"
                  . "Ημερομηνία Λήξης: {$date_str}\n"
                  . "Απομένουν: {$days_left} μέρες\n"
                  . "URL: " . get_site_url();

            wp_mail($notify_email, $subject, $body);
        }
    }
}

//Προσθήκη unschedule κατά την απενεργοποίηση του plugin
register_deactivation_hook(__FILE__, 'yellowizard_clear_scheduled_event');
function yellowizard_clear_scheduled_event() {
    $timestamp = wp_next_scheduled('yellowizard_daily_check_event');
    if ($timestamp) {
        wp_unschedule_event($timestamp, 'yellowizard_daily_check_event');
    }
}

// ✅ Απενεργοποιούμε το προεπιλεγμένο WordPress welcome panel
remove_action('welcome_panel', 'wp_welcome_panel');

// ✅ Προσθήκη custom χαιρετισμού στο dashboard
add_action('wp_dashboard_setup', 'my_plugin_add_custom_welcome_panel');
function my_plugin_add_custom_welcome_panel() {
    // Χαιρετισμοί
    $user = wp_get_current_user();
    $username = $user->display_name ?: $user->user_login;

    $greetings = [
        "Πίσω ξανά, ε {$username};",
        "Δεν χορταίνεις, ε {$username};",
        "{$username}... δεν μπορούσες να μείνεις μακριά, ε;",
        "Χαίρομαι που σε ξαναβλέπω, {$username}!",
        "Ο γνωστός ύποπτος επέστρεψε! Καλώς ήρθες, {$username}.",
        "Κοίτα ποιος γύρισε... {$username}!",
        "Συνδέθηκες, {$username}. Πάμε!",
        "Πάλι εσύ, {$username}; Δεν βαρέθηκες;",
        "Ώρα να συνεχίσουμε, {$username}!",
        "Γεια σου, {$username}!",
        "Χωρίς εσένα τίποτα δεν δουλεύει, {$username}.",
        "{$username}, κάτσε αναπαυτικά. Τα έχουμε όλα έτοιμα.",
        "Χαίρομαι που σε ξαναβλέπω, {$username}!",
        "{$username}, βάλε ζώνη. Ξεκινάμε!"
    ];

    // Επιλογή τυχαίου χαιρετισμού
    $random_greeting = $greetings[array_rand($greetings)];

    // Δημιουργία custom welcome panel με τον χαιρετισμό
    add_action('welcome_panel', function() use ($random_greeting) {
        ?>
        <div class="welcome-panel-content custom">
            <h2 class="welcome-panel-header"><?php echo esc_html($random_greeting); ?></h2>
        </div>
        <?php
    });
}

// ✅ Κρύβουμε όλα τα widgets του dashboard εκτός από το custom welcome panel
add_action('wp_dashboard_setup', 'my_plugin_remove_default_dashboard_widgets');
function my_plugin_remove_default_dashboard_widgets() {
    // Κλείνουμε όλα τα default widgets
    global $wp_meta_boxes;

    unset($wp_meta_boxes['dashboard']['normal']['core']['dashboard_right_now']);
    unset($wp_meta_boxes['dashboard']['normal']['core']['dashboard_recent_comments']);
    unset($wp_meta_boxes['dashboard']['normal']['core']['dashboard_incoming_links']);
    unset($wp_meta_boxes['dashboard']['normal']['core']['dashboard_plugins']);
    unset($wp_meta_boxes['dashboard']['side']['core']['dashboard_quick_press']);
    unset($wp_meta_boxes['dashboard']['side']['core']['dashboard_recent_drafts']);
}

// ✅ Εξασφαλίζουμε ότι το wp_welcome_panel δεν εμφανίζεται ξανά
add_filter('welcome_panel_show', '__return_false');


// Δημιουργία του Widget για την επιλογή γλώσσας στο Dashboard
function yellowizard_language_widget() {
    wp_add_dashboard_widget(
        'yellowizard_language_widget',        // Widget ID
        'Επιλογή Γλώσσας',                    // Τίτλος Widget
        'yellowizard_language_widget_content' // Συνάρτηση περιεχομένου του widget
    );
}
add_action('wp_dashboard_setup', 'yellowizard_language_widget');

// Περιεχόμενο του Widget για την αλλαγή γλώσσας
function yellowizard_language_widget_content() {
    // Λήψη της γλώσσας του τρέχοντος χρήστη
    $current_user = wp_get_current_user();
    $preferred_language = get_user_meta($current_user->ID, 'preferred_language', true);
    if (empty($preferred_language)) {
        $preferred_language = 'el'; // Προεπιλογή: Ελληνικά
    }

    // Φόρμα για την αλλαγή γλώσσας
    ?>
    <form method="post">
        <label for="locale">Επιλέξτε γλώσσα:</label>
        <select name="locale" id="locale">
            <optgroup label="Εγκατεστημένες">
                <option value="site-default" data-installed="1" <?php selected($preferred_language, 'site-default'); ?>>Προεπιλογή ιστότοπου</option>
                <option value="en" lang="en" data-installed="1" <?php selected($preferred_language, 'en'); ?>>English (United States)</option>
                <option value="el" lang="el" data-installed="1" <?php selected($preferred_language, 'el'); ?>>Ελληνικά</option>
                <option value="en_GB" lang="en" data-installed="1" <?php selected($preferred_language, 'en_GB'); ?>>English (UK)</option>
            </optgroup>
        </select>
        <input type="submit" value="Αποθήκευση" class="button button-primary" />
    </form>
    <?php

    // Επεξεργασία της φόρμας όταν υποβληθεί
    if (isset($_POST['locale'])) {
        $new_language = sanitize_text_field($_POST['locale']);
        update_user_meta($current_user->ID, 'preferred_language', $new_language);

        // Εφαρμογή της γλώσσας
        if ($new_language == 'en') {
            switch_to_locale('en_US'); // Αγγλικά
        } elseif ($new_language == 'el') {
            switch_to_locale('el'); // Ελληνικά
        } else {
            switch_to_locale('site-default'); // Επιστροφή στην προεπιλογή
        }

        // Ανακατεύθυνση για να εφαρμοστεί η αλλαγή
        wp_redirect(admin_url());
        exit;
    }
}

// Εφαρμογή της γλώσσας του χρήστη στο Dashboard
function yellowizard_apply_user_language() {
    $current_user = wp_get_current_user();
    $preferred_language = get_user_meta($current_user->ID, 'preferred_language', true);

    // Αν υπάρχει προτιμώμενη γλώσσα, την εφαρμόζουμε
    if ($preferred_language) {
        if ($preferred_language == 'en') {
            switch_to_locale('en_US');
        } elseif ($preferred_language == 'el') {
            switch_to_locale('el');
        } else {
            switch_to_locale('site-default'); // Επιστροφή στην προεπιλογή
        }
    }
}
add_action('admin_init', 'yellowizard_apply_user_language');

function yellowizard_custom_dashboard_widget_styles() {
    echo '<style>
        #yellowizard_language_widget select {
            width: 100%;
            padding: 8px;
            font-size: 14px;
            border: 1px solid #ddd;
            border-radius: 4px;
        }
        #yellowizard_language_widget input[type="submit"] {
            margin-top: 10px;
        }
    </style>';
}
add_action('admin_head', 'yellowizard_custom_dashboard_widget_styles');
