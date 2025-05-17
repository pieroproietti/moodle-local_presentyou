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

use core\user\field\manager; // Assicurati che questa riga sia qui

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
        $departmentoptions = ['' => get_string('selectdepartment', 'local_presentyou')]; // Start with empty option
        // Get the custom department field
        $departmentfield = manager::get_custom_field('department');

        if ($departmentfield && $departmentfield->get_field_type() === 'menu') {
            // Check if it's a menu type field and get its options
            $fieldoptions = $departmentfield->get_field_options(); // This method gets the value => label array
            if (!empty($fieldoptions)) {
                $departmentoptions += $fieldoptions; // Add custom field options to the form options
            } else {
                 // Handle case where the field exists but has no options defined
                 debugging('Custom profile field "department" found but has no options.', DEBUG_DEVELOPER);
                 // Optionally add a disabled warning or prevent form display
                 // For now, the select will just show the empty option.
            }
        } else {
            // Handle case where the field does not exist or is not a menu type
            debugging('Custom profile field "department" not found or is not a menu type.', DEBUG_DEVELOPER);
            // Optionally add a warning message to the form or hide the field
            // $mform->addElement('static', 'department_warning', get_string('departmentfieldmissing', 'local_presentyou'), get_string('configurecustomfields', 'local_presentyou')); // Need configurecustomfields string
        }

        // Only add the element if we have options (at least the empty one)
        if (!empty($departmentoptions)) {
             $mform->addElement('select', 'department', get_string('department', 'local_presentyou'), $departmentoptions);
             $mform->addRule('department', get_string('required'), 'required');
             // Use the field object to get the user's current value if preferred, or stick with get_user_preferences
             // $currentdepartment = $departmentfield ? $departmentfield->get_user_value($USER->id) : '';
             $mform->setDefault('department', get_user_preferences('department', '', $USER->id)); // Added $USER->id
        }


        // --- Position Select ---
        $positionoptions = ['' => get_string('selectposition', 'local_presentyou')]; // Start with empty option
        // Get the custom position field
        $positionfield = manager::get_custom_field('position');

        if ($positionfield && $positionfield->get_field_type() === 'menu') {
            // Check if it's a menu type field and get its options
            $fieldoptions = $positionfield->get_field_options(); // This method gets the value => label array
            if (!empty($fieldoptions)) {
                $positionoptions += $fieldoptions; // Add custom field options to the form options
            } else {
                debugging('Custom profile field "position" found but has no options.', DEBUG_DEVELOPER);
            }
        } else {
             debugging('Custom profile field "position" not found or is not a menu type.', DEBUG_DEVELOPER);
             // $mform->addElement('static', 'position_warning', get_string('positionfieldmissing', 'local_presentyou'), get_string('configurecustomfields', 'local_presentyou'));
        }

         // Only add the element if we have options (at least the empty one)
         if (!empty($positionoptions)) {
             $mform->addElement('select', 'position', get_string('position', 'local_presentyou'), $positionoptions);
             $mform->addRule('position', get_string('required'), 'required');
             // $currentposition = $positionfield ? $positionfield->get_user_value($USER->id) : '';
             $mform->setDefault('position', get_user_preferences('position', '', $USER->id)); // Added $USER->id
         }


        // Add hidden element for redirect URL
        $mform->addElement('hidden', 'redirect', optional_param('redirect', '', PARAM_LOCALURL));
        $mform->setType('redirect', PARAM_LOCALURL);

        // --- Buttons ---
        $buttonarray = [];
        // Conditionally add buttons only if at least one field was added (implies fields exist)
        if (!empty($departmentoptions) || !empty($positionoptions)) {
            $buttonarray[] = $mform->createElement('submit', 'submitbutton', get_string('confirm', 'local_presentyou'));
        }
         // Always provide a logout option even if fields are missing/incorrectly configured
         $buttonarray[] = $mform->createElement('cancel', 'cancelbutton', get_string('logout'));

        $mform->addGroup($buttonarray, 'buttonar', '', [' '], false);
    }

    /**
     * Custom validation rules (optional but good practice).
     * Ensure that the submitted values match the expected values from the profile fields.
     * Although Moodle's select element handles this somewhat, this adds an extra layer.
     */
    public function validation($data, $files) {
        $errors = parent::validation($data, $files);

        // Get the custom field objects using the correct API.
        // It's important to fetch these again here as the form object's definition
        // is run when the form is constructed, but validation is a separate step.
        $departmentfield = manager::get_custom_field('department');
        $positionfield = manager::get_custom_field('position');

        // Validate the submitted values against the allowed options for the profile fields.
        // Use the field object's is_valid_value method.

        // Only validate if the field exists and is a menu type
        if ($departmentfield && $departmentfield->get_field_type() === 'menu') {
             if (isset($data['department']) && !empty($data['department'])) {
                 if (!$departmentfield->is_valid_value($data['department'])) {
                     $errors['department'] = get_string('invalidselection', 'local_presentyou');
                 }
             }
             // The 'required' rule added in definition() handles the empty case validation.
        } else {
             // If the custom field is missing or wrong type, we cannot validate the selection.
             // This scenario might need a different error handling depending on requirements.
             // For now, we just won't run the is_valid_value check.
             // The complete_profile.php page should ideally also check for missing fields before saving.
             debugging('Validation skipped for department: custom field missing or not menu type.', DEBUG_DEVELOPER);
        }


         if ($positionfield && $positionfield->get_field_type() === 'menu') {
             if (isset($data['position']) && !empty($data['position'])) {
                 if (!$positionfield->is_valid_value($data['position'])) {
                     $errors['position'] = get_string('invalidselection', 'local_presentyou');
                 }
             }
             // The 'required' rule added in definition() handles the empty case validation.
         } else {
             debugging('Validation skipped for position: custom field missing or not menu type.', DEBUG_DEVELOPER);
         }


        return $errors;
    }
}