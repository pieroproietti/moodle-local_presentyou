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
 * TODO describe file complete_profile
 *
 * @package    local_presentyou
 * @copyright  2025 Piero Proietti <piero.proietti@gmail.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require_once('../../config.php');
require_once($CFG->libdir.'/adminlib.php'); // Needed for nav
require_once(__DIR__ . '/classes/form/complete_profile_form.php');

// Require login - this is handled by middleware usually, but good practice here too.
require_login();

// Ensure the user exists and is logged in.
if (!isloggedin() || isguestuser()) {
    // This shouldn't happen if middleware works, but good fallback.
    redirect(new moodle_url('/login/index.php'));
}

$PAGE->set_context(context_system::instance()); // System context is usually appropriate for site-wide forms
$PAGE->set_url('/local/presentyou/complete_profile.php');
$PAGE->set_title(get_string('completeprofiletitle', 'local_presentyou'));
$PAGE->set_heading(get_string('completeprofileheading', 'local_presentyou'));

// Create the form instance.
$form = new local_presentyou\form\complete_profile_form();

// Handle form submission.
if ($form->is_cancelled()) {
    // Handle cancellation (Logout button). Moodle's formslib 'cancel' element
    // automatically links to the cancel URL (which defaults to $CFG->wwwroot or similar
    // if not explicitly set in form definition, but clicking the button
    // actually submits with 'cancel' value). We'll explicitly redirect to logout.
     redirect(new moodle_url('/login/logout.php'));

} else if ($fromform = $form->get_data()) {
    // Form was submitted and validated.
    $department = $fromform->department;
    $position = $fromform->position;
    $redirecturl = $fromform->redirect; // Get the saved redirect URL

    // Validate again against profile fields just in case (extra safety).
    $departmentfield = get_user_field_info('department');
    $positionfield = get_user_field_info('position');

    $validationerror = false;
    if (!$departmentfield || !array_key_exists($department, $departmentfield->customdata['options'])) {
        $validationerror = true;
        // Log error or display message if needed
        // error_log("local_presentyou: Invalid department selected by user {$USER->id}: $department");
    }
     if (!$positionfield || !array_key_exists($position, $positionfield->customdata['options'])) {
        $validationerror = true;
        // error_log("local_presentyou: Invalid position selected by user {$USER->id}: $position");
     }

    if (!$validationerror) {
        // Save the selected values to the user's profile fields.
        set_user_preference('department', $department, $USER->id);
        set_user_preference('position', $position, $USER->id);

        // Show a success notification.
        // \core\notification::success(get_string('profilesaved', 'local_presentyou')); // Doesn't show until next page if redirecting

        // Redirect the user.
        if (!empty($redirecturl)) {
            // Redirect to the page they were trying to reach.
            redirect(new moodle_url($redirecturl, ['profilecomplete' => 1])); // Add flag to prevent immediate re-redirect if middleware fires again too quickly
        } else {
            // Default redirect to dashboard if no redirect URL was saved.
            redirect(new moodle_url('/my/', ['profilecomplete' => 1]));
        }

    } else {
         // Handle validation error (should be caught by form validation, but extra check).
         // The form will typically redisplay with errors.
         // Optionally add an error message manually if needed.
         \core\notification::error(get_string('saveprofileerror', 'local_presentyou'));
    }

} else {
    // Form has not been submitted yet, or submission failed validation.
    // (The form->display() call below handles rendering).
}

// Output the page header.
echo $OUTPUT->header();

// Output introductory message.
echo $OUTPUT->box(get_string('completeprofileintro', 'local_presentyou'), 'generalbox');

// Display the form.
$form->display();

// Output the page footer.
echo $OUTPUT->footer();