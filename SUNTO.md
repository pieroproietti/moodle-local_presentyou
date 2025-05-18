# Struttura della cartella 'presentyou'

```ascii
presentyou/
├── classes/
│   ├── form/
│   │   └── complete_profile_form.php
│   └── privacy/
│       └── provider.php
├── lang/
│   ├── en/
│   │   └── local_presentyou.php
│   └── it/
│       └── local_presentyou.php
├── README.md
├── SUNTO.md
├── complete_profile.php
├── index.php
├── middleware.php
├── sunto.py
└── version.php
```

# File PHP trovati (percorsi relativi)

* classes/form/complete_profile_form.php
* classes/privacy/provider.php
* complete_profile.php
* index.php
* lang/en/local_presentyou.php
* lang/it/local_presentyou.php
* middleware.php
* version.php

# Contenuto dei file PHP

1. classes/form/complete_profile_form.php

```php
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

namespace local_presentyou\form;

defined('MOODLE_INTERNAL') || die();

require_once($CFG->libdir.'/formslib.php');
// Include the library for user profile fields
require_once($CFG->dirroot . '/user/profile/lib.php'); // This library contains functions for user profile fields


/**
 * Form for users to select their department and position.
 * @package local_presentyou
 */
class complete_profile_form extends \moodleform {

    // Store field definitions
    protected $departmentfield = null;
    protected $positionfield = null;

    /**
     * Define the form elements.
     */
    protected function definition() {
        global $CFG, $USER, $DB;

        $mform = $this->_form;

        // --- Department Select ---
        $departmentoptions = ['' => get_string('selectdepartment', 'local_presentyou')]; // Start with empty option
        $currentdepartment = '';

        // Get the custom department field definition from the database
        $this->departmentfield = $DB->get_record('user_info_field', array('shortname' => 'department'));

        if ($this->departmentfield && $this->departmentfield->datatype === 'menu') {
            // Menu options are stored as newline-separated values in param1
            $fieldoptions = explode("\n", $this->departmentfield->param1);
            $options_formatted = [];
            foreach ($fieldoptions as $option) {
                 $option = trim($option);
                 if ($option !== '') {
                      // The key and value are the same for simple menu options
                     $options_formatted[$option] = $option;
                 }
            }

            if (!empty($options_formatted)) {
                $departmentoptions += $options_formatted; // Add custom field options to the form options
            } else {
                 debugging('Custom profile field "department" found but has no options defined.', DEBUG_DEVELOPER);
            }

             // Get the user's current value for this field
             // profile_load_custom_fields loads custom field data onto the $USER object
             profile_load_custom_fields($USER);
             // Custom field data is accessible via $USER->profile[shortname]
             if (isset($USER->profile['department']) && $USER->profile['department'] !== '') {
                  $currentdepartment = $USER->profile['department'];
             }


        } else {
            debugging('Custom profile field "department" not found or is not a menu type.', DEBUG_DEVELOPER);
            // Add a warning if the field is missing or misconfigured
            $mform->addElement('static', 'department_warning', get_string('departmentfieldmissing', 'local_presentyou'), get_string('configurecustomfields', 'local_presentyou')); // configurecustomfields needs to be added to lang
        }


        // Only add the element if the field definition was found and it's a menu type
        // and we have options (at least the empty one)
        if ($this->departmentfield && $this->departmentfield->datatype === 'menu' && !empty($departmentoptions)) {
             $mform->addElement('select', 'department', get_string('department', 'local_presentyou'), $departmentoptions);
             $mform->addRule('department', get_string('required'), 'required');
             $mform->setDefault('department', $currentdepartment); // Use the retrieved current value
        }


        // --- Position Select ---
        $positionoptions = ['' => get_string('selectposition', 'local_presentyou')]; // Start with empty option
        $currentposition = '';

        // Get the custom position field definition from the database
        $this->positionfield = $DB->get_record('user_info_field', array('shortname' => 'position'));

        if ($this->positionfield && $this->positionfield->datatype === 'menu') {
             $fieldoptions = explode("\n", $this->positionfield->param1);
             $options_formatted = [];
             foreach ($fieldoptions as $option) {
                  $option = trim($option);
                  if ($option !== '') {
                      $options_formatted[$option] = $option;
                  }
             }

             if (!empty($options_formatted)) {
                 $positionoptions += $options_formatted; // Add custom field options to the form options
             } else {
                 debugging('Custom profile field "position" found but has no options defined.', DEBUG_DEVELOPER);
             }

             // User profile data is already loaded onto $USER by profile_load_custom_fields above
             if (isset($USER->profile['position']) && $USER->profile['position'] !== '') {
                  $currentposition = $USER->profile['position'];
             }

        } else {
             debugging('Custom profile field "position" not found or is not a menu type.', DEBUG_DEVELOPER);
             $mform->addElement('static', 'position_warning', get_string('positionfieldmissing', 'local_presentyou'), get_string('configurecustomfields', 'local_presentyou')); // configurecustomfields needs to be added to lang
        }

         // Only add the element if the field definition was found and it's a menu type
         // and we have options (at least the empty one)
         if ($this->positionfield && $this->positionfield->datatype === 'menu' && !empty($positionoptions)) {
             $mform->addElement('select', 'position', get_string('position', 'local_presentyou'), $positionoptions);
             $mform->addRule('position', get_string('required'), 'required');
             $mform->setDefault('position', $currentposition); // Use the retrieved current value
         }


        // Add hidden element for redirect URL
        $mform->addElement('hidden', 'redirect', optional_param('redirect', '', PARAM_LOCALURL));
        $mform->setType('redirect', PARAM_LOCALURL);

        // --- Buttons ---
        $buttonarray = [];
        // Conditionally add buttons only if at least one required field was successfully added
        if (($this->departmentfield && $this->departmentfield->datatype === 'menu' && !empty($departmentoptions)) ||
            ($this->positionfield && $this->positionfield->datatype === 'menu' && !empty($positionoptions))) {
            $buttonarray[] = $mform->createElement('submit', 'submitbutton', get_string('confirm', 'local_presentyou'));
        }
         // Always provide a logout option even if fields are missing/incorrectly configured
         $buttonarray[] = $mform->createElement('cancel', 'cancelbutton', get_string('logout'));

        $mform->addGroup($buttonarray, 'buttonar', '', [' '], false);
    }

    /**
     * Custom validation rules.
     * Ensure that the submitted values match the expected values from the profile fields.
     * We need to re-fetch fields here or ensure they are available.
     */
    public function validation($data, $files) {
        global $DB; // Need DB access for validation against field options

        $errors = parent::validation($data, $files);

        // Re-fetch field definitions if they weren't stored or if validation is called separately
        if (!$this->departmentfield) {
            $this->departmentfield = $DB->get_record('user_info_field', array('shortname' => 'department'));
        }
        if (!$this->positionfield) {
            $this->positionfield = $DB->get_record('user_info_field', array('shortname' => 'position'));
        }


        // Validate the submitted values against the allowed options for the profile fields.
        // For menu type, the value should be one of the options defined in param1.

        // Only validate if the field exists and is a menu type
        if ($this->departmentfield && $this->departmentfield->datatype === 'menu') {
             if (isset($data['department']) && $data['department'] !== '') { // Check if value is set and not the empty option
                 $fieldoptions = explode("\n", $this->departmentfield->param1);
                 $validoptions = [];
                 foreach ($fieldoptions as $option) {
                     $option = trim($option);
                     if ($option !== '') {
                         $validoptions[] = $option;
                     }
                 }
                 if (!in_array($data['department'], $validoptions)) {
                     $errors['department'] = get_string('invalidselection', 'local_presentyou');
                 }
             }
             // The 'required' rule added in definition() handles the empty case validation.
        } else {
             // If the custom field is missing or wrong type, we cannot validate the selection against it.
             debugging('Validation skipped for department: custom field missing or not menu type during validation.', DEBUG_DEVELOPER);
        }


         if ($this->positionfield && $this->positionfield->datatype === 'menu') {
              if (isset($data['position']) && $data['position'] !== '') { // Check if value is set and not the empty option
                  $fieldoptions = explode("\n", $this->positionfield->param1);
                  $validoptions = [];
                  foreach ($fieldoptions as $option) {
                      $option = trim($option);
                      if ($option !== '') {
                          $validoptions[] = $option;
                      }
                  }
                  if (!in_array($data['position'], $validoptions)) {
                      $errors['position'] = get_string('invalidselection', 'local_presentyou');
                  }
              }
              // The 'required' rule handles the empty case.
         } else {
             debugging('Validation skipped for position: custom field missing or not menu type during validation.', DEBUG_DEVELOPER);
         }

        return $errors;
    }
}
```

2. classes/privacy/provider.php

```php
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

namespace local_presentyou\privacy;

use core_privacy\local\metadata\null_provider;

/**
 * Privacy Subsystem for local_presentyou implementing null_provider.
 *
 * @package    local_presentyou
 * @copyright  2025 Piero Proietti <piero.proietti@gmail.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class provider implements null_provider {

    /**
     * Get the language string identifier with the component's language
     * file to explain why this plugin stores no data.
     *
     * @return  string
     */
    public static function get_reason(): string {
        return 'privacy:metadata';
    }
}

```

3. complete_profile.php

```php
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
```

4. index.php

```php
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

// INTENTIONALLY BLANK


```

5. lang/en/local_presentyou.php

```php
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
 * English language pack for Presentyou
 *
 * @package    local_presentyou
 * @category   string
 * @copyright  2025 Piero Proietti <piero.proietti@gmail.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

$string['pluginname'] = 'PresentYou Profile Completion';
$string['completeprofiletitle'] = 'Complete Your Profile';
$string['completeprofileheading'] = 'Welcome - Complete Your Profile';
$string['completeprofileintro'] = 'Please select your department and position to continue.';
$string['department'] = 'Department';
$string['position'] = 'Position';
$string['privacy:metadata'] = 'Privacy:metadata';
$string['selectdepartment'] = '--- Select Department ---';
$string['selectposition'] = '--- Select Position ---';
$string['confirm'] = 'Confirm';
// $string['logout'] = 'Logout'; // Re-using core logout string might be fine
$string['required'] = 'This field is required.';
$string['profilesaved'] = 'Your profile information has been saved.';
$string['saveprofileerror'] = 'An error occurred while saving your profile information.';
$string['invalidselection'] = 'Invalid selection.';
$string['departmentfieldmissing'] = 'Department profile field is missing or misconfigured.';
$string['positionfieldmissing'] = 'Position profile field is missing or misconfigured.';
$string['configurecustomfields'] = 'Please contact an administrator to configure the required custom profile fields.';
```

6. lang/it/local_presentyou.php

```php
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
 * English language pack for Presentyou
 *
 * @package    local_presentyou
 * @category   string
 * @copyright  2025 Piero Proietti <piero.proietti@gmail.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

$string['pluginname'] = 'PresentYou Completa il tuo profilo';
$string['completeprofiletitle'] = 'Completa il tuo profilo';
$string['completeprofileheading'] = 'Benvenuto - Completa il tuo profilo';
$string['completeprofileintro'] = 'Seleziona il tuo dipartimento e posizione per continuare.';
$string['department'] = 'Dipartimento';
$string['position'] = 'Posizione';
$string['privacy:metadata'] = 'Privacy:metadata';
$string['selectdepartment'] = '--- Selezione Dipartimento ---';
$string['selectposition'] = '--- Selezione Posizione ---';
$string['confirm'] = 'Confirma';
//$string['logout'] = 'Logout'; // Re-using core logout string might be fine
$string['required'] = 'Questo campo è richiesto.';
$string['profilesaved'] = 'Le tue informazioni di profilo sono state salvate.';
$string['saveprofileerror'] = 'Errore durante il salvataggio delle informazioni di profilo.';
$string['invalidselection'] = 'Selezione non valida.';
$string['departmentfieldmissing'] = 'Il campo Dipartimento del profilo è assente o non configurato.';
$string['positionfieldmissing'] = 'Il capor Position del profilo è assente o non configurato.';
$string['configurecustomfields'] = 'Per favore contatta l\'amministratore del sito per configurare il campo custom profile richiesto.';
```

7. middleware.php

```php
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

// require('../../config.php'); gia caricato

// No direct access.
defined('MOODLE_INTERNAL') || die();


/**
 * Middleware: dopo il login ci reindirizza SEMPRE 
 *             su /local/presentyou/complete_profile.php
 *             per qualche motivo NON succede!
 *
 * @param \moodle_page $PAGE The current Moodle page object.
 * @param \core_renderer $OUTPUT The current Moodle output object.
 */
function local_presentyou_middleware(\moodle_page $PAGE, \core_renderer $OUTPUT) {
    global $CFG, $USER;

    // Only apply this logic if a user is logged in.
    if (!isloggedin() || isguestuser()) { // <<< Aggiunto controllo 'isguestuser()'
        return;
    }
    // Define the URL of our profile completion page.
    $completionpageurl = new moodle_url('/local/presentyou/complete_profile.php');
    
    // Get the URLs of pages we should NOT interrupt.
    $logoutpageurl = new moodle_url('/login/logout.php');
    $selfregistrationurl = new moodle_url('/login/signup.php');
    $forgotpasswordurl = new moodle_url('/login/forgot_password.php');
    $confirmemailurl = new moodle_url('/login/confirm.php');
    $loginpageurl = new moodle_url('/login/index.php'); // Non reindirizzare dalla pagina di login stessa

    // Check if the user is logged in, is NOT a guest,
    // AND they are NOT already on the completion page,
    // AND they are NOT trying to access any of the excluded auth-related pages.
    // In TUTTI gli altri casi, reindirizza alla pagina di completamento profilo.
    if (isloggedin() && !isguestuser() &&
        !$PAGE->url->equals($completionpageurl, true) &&
        !$PAGE->url->equals($logoutpageurl, true) &&
        !$PAGE->url->equals($selfregistrationurl, true) &&
        !$PAGE->url->equals($forgotpasswordurl, true) &&
        !$PAGE->url->equals($confirmemailurl, true) &&
        !$PAGE->url->equals($loginpageurl, true)
       ) {

            // Reindirizza l'utente alla pagina di completamento profilo.
            // Salviamo l'URL originale come parametro 'redirect' - utile per complete_profile.php DOPO il salvataggio.
            $urltogo = new moodle_url($completionpageurl, ['redirect' => $PAGE->url->out(false)]);
            redirect($urltogo);
       }
}

```

8. version.php

```php
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
 * Version information for Presentyou
 *
 * @package    local_presentyou
 * @copyright  2025 Piero Proietti <piero.proietti@gmail.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

$plugin->component = 'local_presentyou';
$plugin->version = 2025051801; // YYYYMMDD Revision - Update this when you make changes
$plugin->requires = 2025041400.05; // Moodle 5+ last version
$plugin->maturity = MATURITY_STABLE; // MATURITY_ALPHA, MATURITY_BETA, MATURITY_RC, MATURITY_STABLE
$plugin->release = 'v1.0';
$departmentfieldmissing= 'Manca il campo del Dipartimento';
$positionfieldmissing= 'Manca il campo della Posizione';

// This line tells Moodle to load our middleware.php file on every request.
$plugin->middlewarefile = true;

```

