<?php
// This file is part of Moodle - http://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.

/**
 * TODO describe file middleware
 *
 * @package    local_presentyou
 * @copyright  2025 Piero Proietti <piero.proietti@gmail.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require('../../config.php');

// No direct access.
defined('MOODLE_INTERNAL') || die();

/**
 * Middleware to check if the user's department and position are set.
 * If not, redirect them to the profile completion page.
 *
 * @param \moodle_page $PAGE The current Moodle page object.
 * @param \core_renderer $OUTPUT The current Moodle output object.
 */
function local_presentyou_middleware(\moodle_page $PAGE, \core_renderer $OUTPUT) {
    global $CFG, $USER;

    // Only apply this logic if a user is logged in.
    if (!isloggedin()) {
        return;
    }

    // Define the short names of the profile fields we're checking.
    $departmentfieldname = 'department'; // Ensure this matches the short name created in Step 1
    $positionfieldname = 'position';   // Ensure this matches the short name created in Step 1

    // Get the values of the profile fields for the current user.
    #$departmentvalue = get_user_preferences($departmentfieldname, null, $USER);
    $positionvalue = get_user_preferences($positionfieldname, null, $USER);

    // Check if either field is empty.
    $profileincomplete = (empty($departmentvalue) || empty($positionvalue));

    // Define the URL of our profile completion page.
    $completionpageurl = new moodle_url('/local/presentyou/complete_profile.php');

    // Get the URL of the logout page.
    $logoutpageurl = new moodle_url('/login/logout.php');

    // Check if the user is logged in, their profile is incomplete,
    // AND they are NOT already on the completion page,
    // AND they are NOT trying to logout.
    if (isloggedin() && $profileincomplete &&
        !$PAGE->url->equals($completionpageurl, true) &&
        !$PAGE->url->equals($logoutpageurl, true)) {

        // Redirect the user to the profile completion page.
        // Pass the original requested URL so we can redirect them back later (optional).
        $urltogo = new moodle_url($completionpageurl, ['redirect' => $PAGE->url->out_as_local_url(false)]);
        redirect($urltogo);

    } elseif (isloggedin() && !$profileincomplete &&
              $PAGE->url->equals($completionpageurl, true)) {

        // If the user *has* completed their profile but somehow landed on the completion page,
        // redirect them away (e.g., to the dashboard).
        // We could also redirect them to the 'redirect' parameter if it exists.
        $redirecturl = optional_param('redirect', null, PARAM_LOCALURL);
        if (!empty($redirecturl)) {
            redirect(new moodle_url($redirecturl));
        } else {
            redirect(new moodle_url('/my/')); // Default to dashboard
        }
    }
}
