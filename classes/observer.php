<?php
namespace local_presentyou;

defined('MOODLE_INTERNAL') || die();

class observer {
    public static function before_http_headers(\core\hook\output\before_http_headers $hook) {
        global $USER, $PAGE;

        // Skip if not logged in or guest user
        if (!isloggedin() || isguestuser()) {
            return;
        }

        // Skip during CLI execution
        if (CLI_SCRIPT) {
            return;
        }

        error_log("OBSERVER: Method user_loggedin called");

        // Get current URL without parameters for comparison
        $currenturl = $PAGE->url->out_as_local_url(false);
        $targeturl = new \moodle_url('/local/presentyou/complete_profile.php');
        $loginurl = new \moodle_url('/login/index.php');
        $logouturl = new \moodle_url('/login/logout.php');
        
        // Skip if already on target pages
        if (strpos($currenturl, $targeturl->out_as_local_url(false)) !== false) {
            return;
        }
        
        if (strpos($currenturl, $loginurl->out_as_local_url(false)) !== false) {
            return;
        }
        
        if (strpos($currenturl, $logouturl->out_as_local_url(false)) !== false) {
            return;
        }

        // Load profile fields and check completion
        profile_load_custom_fields($USER);
        $profilecomplete = !empty($USER->profile['department']) && !empty($USER->profile['position']);

        if (!$profilecomplete) {
            redirect($targeturl);
        }
    }

    public static function user_loggedin(\core\event\user_loggedin $event) {
        global $USER;
        
        // Force profile check immediately after login
        profile_load_custom_fields($USER);
        if (empty($USER->profile['department']) || empty($USER->profile['position'])) {
            redirect(new \moodle_url('/local/presentyou/complete_profile.php'));
        }
    }
}
