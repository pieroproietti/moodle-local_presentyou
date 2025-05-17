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

// <<< AGGIUNGI QUESTA RIGA
use core\user\field\manager;
// >>> FINE RIGA AGGIUNTA

/**
 * Form for users to select their department and position.
 * @package local_presentyou
 */
class complete_profile_form extends \moodleform {

    /**
     * Define the form elements.
     */
    protected function definition() {
        global $CFG, $USER;

        $mform = $this->_form;

        // --- Department Select ---
        $departments = ['Down' => get_string('departmentdown', 'local_presentyou'),
                        'Upper' => get_string('departmentupper', 'local_presentyou')];
        // Add an empty option to force selection
        $departmentoptions = ['' => get_string('selectdepartment', 'local_presentyou')] + $departments;

        $mform->addElement('select', 'department', get_string('department', 'local_presentyou'), $departmentoptions);
        $mform->addRule('department', get_string('required'), 'required');
        $mform->setDefault('department', get_user_preferences('department', '', $USER));


        // --- Position Select ---
        $positions = ['Teacher' => get_string('positionteacher', 'local_presentyou'),
                      'Janitor' => get_string('positionjanitor', 'local_presentyou')];
         // Add an empty option to force selection
        $positionoptions = ['' => get_string('selectposition', 'local_presentyou')] + $positions;

        $mform->addElement('select', 'position', get_string('position', 'local_presentyou'), $positionoptions);
        $mform->addRule('position', get_string('required'), 'required');
        $mform->setDefault('position', get_user_preferences('position', '', $USER));

        // Add hidden element for redirect URL
        $mform->addElement('hidden', 'redirect', optional_param('redirect', '', PARAM_LOCALURL));
        $mform->setType('redirect', PARAM_LOCALURL);

        // --- Buttons ---
        $buttonarray = [];
        $buttonarray[] = $mform->createElement('submit', 'submitbutton', get_string('confirm', 'local_presentyou'));
        // Add a cancel button that acts as a logout link
        $buttonarray[] = $mform->createElement('cancel', 'cancelbutton', get_string('logout'));
        $mform->addGroup($buttonarray, 'buttonar', '', [' '], false);
        // $mform->closeHeader(); NON USATA
    }

    /**
     * Custom validation rules (optional but good practice).
     * Ensure that the submitted values match the expected values from the profile fields.
     * Although Moodle's select element handles this somewhat, this adds an extra layer.
     */
    public function validation($data, $files) {
        $errors = parent::validation($data, $files);

        // Get the custom field objects using the correct API.
        $departmentfield = \core\user\field\manager::get_custom_field('department');
        $positionfield = \core\user\field\manager::get_custom_field('position');

        // Validate the submitted values against the allowed options for the profile fields.
        // Use the field object's is_valid_value method.

        // Only validate if the field exists (should exist if setup is done)
        // AND if a value was actually submitted (the 'required' rule handles empty,
        // but this checks if the submitted non-empty value is valid).
        if ($departmentfield && isset($data['department']) && !empty($data['department'])) {
             if (!$departmentfield->is_valid_value($data['department'])) {
                 $errors['department'] = get_string('invalidselection', 'local_presentyou');
             }
        } else if ($departmentfield && empty($data['department']) && $departmentfield->is_required()) {
             // This case should ideally be caught by parent::validation() due to addRule('required'),
             // but good to double check if needed based on how required is handled.
             // For simple select 'required', parent::validation is enough.
        }


         if ($positionfield && isset($data['position']) && !empty($data['position'])) {
             if (!$positionfield->is_valid_value($data['position'])) {
                 $errors['position'] = get_string('invalidselection', 'local_presentyou');
             }
         } else if ($positionfield && empty($data['position']) && $positionfield->is_required()) {
             // Similar to department, parent::validation likely covers this.
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
    // Use the correct API to get custom field information.
    $departmentfield = \core_user\field\manager::get_custom_field('department');
    $positionfield = \core_user\field\manager::get_custom_field('position');

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
 * Solo per bloccare l'accesso diretto
 *
 * @package    local_presentyou
 * @copyright  2025 Piero Proietti <piero.proietti@gmail.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
defined('MOODLE_INTERNAL') || die();

// Define the target URL
$targeturl = new moodle_url('/local/presentyou/complete_profile.php');

// Redirect the user
redirect($targeturl);

// redirect() normalmente termina lo script, ma aggiungere die() è una sicurezza extra.
die();
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
$string['logout'] = 'Logout'; // Re-using core logout string might be fine
$string['required'] = 'This field is required.';
$string['profilesaved'] = 'Your profile information has been saved.';
$string['saveprofileerror'] = 'An error occurred while saving your profile information.';
// $string['departmentdown'] = 'Down';
// $string['departmentupper'] = 'Upper';
// $string['positionteacher'] = 'Teacher';
// $string['positionjanitor'] = 'Janitor';
$string['invalidselection'] = 'Invalid selection.';
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
$string['completeprofileintro'] = 'Seleziona il tuo dipartimento e posizione per contuniare.';
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
 * Middleware to check if the user's department and position are set.
 * If not, redirect them to the profile completion page.
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

// Nota: Non è necessario controllare l'esistenza dei campi profilo qui
// dato che l'obiettivo è reindirizzare SEMPRE. Eventuali problemi con i campi
// si manifesteranno (più correttamente) nella pagina complete_profile.php.
// Ho rimosso quel controllo per questa specifica logica "reindirizza sempre".


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
$plugin->version = 2025051702; // YYYYMMDD Revision - Update this when you make changes
$plugin->requires = 2025041400.05; // Moodle 5+ last version
$plugin->maturity = MATURITY_STABLE; // MATURITY_ALPHA, MATURITY_BETA, MATURITY_RC, MATURITY_STABLE
$plugin->release = 'v1.0';

// This line tells Moodle to load our middleware.php file on every request.
$plugin->middlewarefile = true;

```

