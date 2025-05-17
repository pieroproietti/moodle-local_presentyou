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

use core\user\field\manager; // Assicurati che questa riga sia qui

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
    // Use the correct API to get custom field information.
    $departmentfield = \core\user\field\manager::get_custom_field('department');
    $positionfield = \core\user\field\manager::get_custom_field('position');

    $validationerror = false;
    $errormessage = ''; // Per un messaggio di errore più specifico

    // Check if fields exist and if selected values are valid options.
    if (!$departmentfield) {
        // Questo caso dovrebbe essere gestito dal middleware o dal setup,
        // ma è una sicurezza in più.
        $validationerror = true;
        $errormessage = get_string('saveprofileerror', 'local_presentyou') . ': ' . get_string('departmentfieldmissing', 'local_presentyou'); // Stringa da aggiungere in lang/en
        // error_log("local_presentyou: Department field is missing but user submitted form.");
    } elseif (!$departmentfield->is_valid_value($department)) { // Usa il metodo is_valid_value del campo
         $validationerror = true;
         $errormessage = get_string('saveprofileerror', 'local_presentyou') . ': ' . get_string('invalidselection', 'local_presentyou'); // Stringa esistente
         // error_log("local_presentyou: Invalid department selected by user {$USER->id}: $department");
    }


     if (!$positionfield) {
         $validationerror = true;
         $errormessage = get_string('saveprofileerror', 'local_presentyou') . ': ' . get_string('positionfieldmissing', 'local_presentyou'); // Stringa da aggiungere in lang/en
         // error_log("local_presentyou: Position field is missing but user submitted form.");
     } elseif (!$positionfield->is_valid_value($position)) { // Usa il metodo is_valid_value del campo
         $validationerror = true;
         $errormessage = get_string('saveprofileerror', 'local_presentyou') . ': ' . get_string('invalidselection', 'local_presentyou'); // Stringa esistente
         // error_log("local_presentyou: Invalid position selected by user {$USER->id}: $position");
     }


    if (!$validationerror) {
        // Save the selected values to the user's profile fields.
        // set_user_preference works for custom user fields.
        set_user_preference('department', $department, $USER->id);
        set_user_preference('position', $position, $USER->id);

        // Show a success notification. (Will be shown on the next page)
        // \core\notification::success(get_string('profilesaved', 'local_presentyou'));

        // Redirect the user.
        // In questo scenario "pagina di benvenuto obbligatoria", reindirizzare alla dashboard ha più senso.
        // Se l'utente è arrivato qui dal middleware dopo login, non sta "continuando a navigare" verso un URL specifico.
        redirect(new moodle_url('/my/'));


    } else {
        // Handle validation error.
        // The form should redisplay with its own validation errors,
        // but displaying a general notification helps.
        \core\notification::error($errormessage);
        // Re-display the form with errors. The form object already has the submitted data and errors.
        // The code continues to $form->display() below.
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

// Aggiungi queste stringhe (o simili) in lang/en/local_presentyou.php
// $string['departmentfieldmissing'] = 'Department profile field is missing.';
// $string['positionfieldmissing'] = 'Position profile field is missing.';