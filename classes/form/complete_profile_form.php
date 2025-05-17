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
