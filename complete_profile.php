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
 * Page to complete user profile information (department and position).
 *
 * @package    local_presentyou
 * @copyright  2025 Piero Proietti <piero.proietti@gmail.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require_once('../../config.php');
require_once($CFG->libdir.'/adminlib.php'); // Needed for nav
require_once(__DIR__ . '/classes/form/complete_profile_form.php');
// Include the library for user profile fields (needed for profile_load_custom_fields after save)
require_once($CFG->dirroot . '/user/profile/lib.php');

// Require login - this is handled by middleware usually, but good practice here too.
require_login();

// Ensure the user exists and is logged in.
if (!isloggedin() || isguestuser()) {
    // This shouldn't happen if middleware works, but good fallback.
    redirect(new moodle_url('/login/index.php'));
}

// Check if required custom fields exist and are correctly configured before proceeding
$departmentfield = $DB->get_record('user_info_field', array('shortname' => 'department'));
$positionfield = $DB->get_record('user_info_field', array('shortname' => 'position'));

$fields_missing_or_wrong_type = false;
if (!$departmentfield || $departmentfield->datatype !== 'menu') {
    debugging('Department profile field not found or is not a menu type.', DEBUG_DEVELOPER);
    $fields_missing_or_wrong_type = true;
}
if (!$positionfield || $positionfield->datatype !== 'menu') {
     debugging('Position profile field not found or is not a menu type.', DEBUG_DEVELOPER);
    $fields_missing_or_wrong_type = true;
}

// If the required fields are not found or are the wrong type, display a warning and allow logout.
if ($fields_missing_or_wrong_type) {
    $PAGE->set_context(context_system::instance());
    $PAGE->set_url('/local/presentyou/complete_profile.php');
    $PAGE->set_title(get_string('completeprofiletitle', 'local_presentyou'));
    $PAGE->set_heading(get_string('completeprofileheading', 'local_presentyou'));
    echo $OUTPUT->header();
    // Use specific messages for missing/wrong type fields
    if (!$departmentfield || $departmentfield->datatype !== 'menu') {
         echo $OUTPUT->box(get_string('departmentfieldmissing', 'local_presentyou') . ' ' . get_string('configurecustomfields', 'local_presentyou'), 'warning');
    }
    if (!$positionfield || $positionfield->datatype !== 'menu') {
         echo $OUTPUT->box(get_string('positionfieldmissing', 'local_presentyou') . ' ' . get_string('configurecustomfields', 'local_presentyou'), 'warning');
    }

    echo $OUTPUT->single_button(new moodle_url('/login/logout.php'), get_string('logout'));
    echo $OUTPUT->footer();
    exit; // Stop execution as fields are missing or misconfigured
}


$PAGE->set_context(context_system::instance()); // System context is usually appropriate for site-wide forms
$PAGE->set_url('/local/presentyou/complete_profile.php');
$PAGE->set_title(get_string('completeprofiletitle', 'local_presentyou'));
$PAGE->set_heading(get_string('completeprofileheading', 'local_presentyou'));

// Create the form instance.
$form = new local_presentyou\form\complete_profile_form();


// Handle form submission.
if ($form->is_cancelled()) {
    // Handle cancellation (Logout button).
     redirect(new moodle_url('/login/logout.php'));

} else if ($fromform = $form->get_data()) {
    // Form was submitted and validated by the form's validation() method.
    // Additional server-side validation here is largely covered by the form,
    // but we'll perform a quick check against the options again for safety.

    $department = $fromform->department;
    $position = $fromform->position;
    $redirecturl = $fromform->redirect; // Get the saved redirect URL

    $validationerror = false;
    $errormessage = ''; // Per un messaggio di errore più specifico

    // Server-side validation against field options (redundant with form validation but safer)
    if ($departmentfield->datatype === 'menu') {
         $fieldoptions = explode("\n", $departmentfield->param1);
         $validoptions = [];
         foreach ($fieldoptions as $option) {
             $option = trim($option);
             if ($option !== '') {
                 $validoptions[] = $option;
             }
         }
         if (!in_array($department, $validoptions) && $department !== '') { // Allow empty if not required
              $validationerror = true;
              $errormessage = get_string('saveprofileerror', 'local_presentyou') . ': ' . get_string('invalidselection', 'local_presentyou');
              debugging("local_presentyou: Invalid department selection on server side validation: $department", DEBUG_DEVELOPER);
         }
    }

    if ($positionfield->datatype === 'menu') {
         $fieldoptions = explode("\n", $positionfield->param1);
         $validoptions = [];
         foreach ($fieldoptions as $option) {
             $option = trim($option);
             if ($option !== '') {
                 $validoptions[] = $option;
             }
         }
         if (!in_array($position, $validoptions) && $position !== '') { // Allow empty if not required
              $validationerror = true;
              // Append to existing error message or set a new one
              $errormessage = empty($errormessage) ? get_string('saveprofileerror', 'local_presentyou') . ': ' . get_string('invalidselection', 'local_presentyou') : $errormessage . ' ' . get_string('invalidselection', 'local_presentyou');
              debugging("local_presentyou: Invalid position selection on server side validation: $position", DEBUG_DEVELOPER);
         }
    }


    if (!$validationerror) {
        // Save the selected values to the user's profile fields in mdl_user_info_data.

        // Department field
        $departmentdata = $DB->get_record('user_info_data', array('userid' => $USER->id, 'fieldid' => $departmentfield->id));
        if ($departmentdata) {
            // Update existing record
            $departmentdata->data = $department;
            $DB->update_record('user_info_data', $departmentdata);
        } else {
            // Insert new record
            $departmentdata = new stdClass();
            $departmentdata->userid = $USER->id;
            $departmentdata->fieldid = $departmentfield->id;
            $departmentdata->data = $department;
            $departmentdata->dataformat = 0; // Plain text format
            $DB->insert_record('user_info_data', $departmentdata);
        }

        // Position field
        $positiondata = $DB->get_record('user_info_data', array('userid' => $USER->id, 'fieldid' => $positionfield->id));
        if ($positiondata) {
            // Update existing record
            $positiondata->data = $position;
            $DB->update_record('user_info_data', $positiondata);
        } else {
            // Insert new record
            $positiondata = new stdClass();
            $positiondata->userid = $USER->id;
            $positiondata->fieldid = $positionfield->id;
            $positiondata->data = $position;
            $positiondata->dataformat = 0; // Plain text format
            $DB->insert_record('user_info_data', $positiondata);
        }

        // Reload the user object with updated profile data
        profile_load_custom_fields($USER);

        // Show a success notification. (Will be shown on the next page)
        \core\notification::success(get_string('profilesaved', 'local_presentyou'));

        // Redirect the user.
        // In this "mandatory welcome page" scenario, redirecting to the dashboard makes more sense.
        redirect(new moodle_url('/my/'));


    } else {
        // Handle validation error.
        // Displaying a general notification helps.
        \core\notification::error($errormessage);
        // The form will redisplay with its own validation errors if any.
    }

} else {
    // Form has not been submitted yet, or submission failed validation and is being re-displayed.
    // (The $form->display() call below handles rendering).
}

// Output the page header.
echo $OUTPUT->header();

// Output introductory message.
echo $OUTPUT->box(get_string('completeprofileintro', 'local_presentyou'), 'generalbox');

// Display the form.
$form->display();

// Output the page footer.
echo $OUTPUT->footer();

// Aggiungi queste stringhe (o simili) in lang/en/local_presentyou.php e lang/it/local_presentyou.php:
// $string['departmentfieldmissing'] = 'Department profile field is missing or misconfigured.';
// $string['positionfieldmissing'] = 'Position profile field is missing or misconfigured.';
// $string['configurecustomfields'] = 'Please contact an administrator to configure the required custom profile fields.';