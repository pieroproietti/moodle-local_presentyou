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