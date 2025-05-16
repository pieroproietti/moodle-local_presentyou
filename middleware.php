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

    // Define the short names of the profile fields we're checking.
    $departmentfieldname = 'department'; // Ensure this matches the short name created in Step 1
    $positionfieldname = 'position';   // Ensure this matches the short name created in Step 1

    // <<< INIZIO BLOCCO AGGIUNTO: Controllo esistenza campi profilo
    // Questo previene errori se i campi non sono stati creati in Moodle Admin -> User profile fields
    $departmentfieldexists = get_user_field_info($departmentfieldname) !== false;
    $positionfieldexists = get_user_field_info($positionfieldname) !== false;

    // Se i campi non esistono, il plugin non può funzionare correttamente.
    // Lasciamo passare l'utente, l'amministratore dovrà creare i campi.
    // Oppure potresti reindirizzare a una pagina di errore/setup, ma per semplicità usciamo dal middleware.
    if (!$departmentfieldexists || !$positionfieldexists) {
        return;
    }
    // <<< FINE BLOCCO AGGIUNTO


    // Get the values of the profile fields for the current user.
    /**
     * 
     * rimosso DELIBERATAMENTE
     * $departmentvalue = get_user_preferences($departmentfieldname, null, $USER); 
     */
    $departmentvalue = get_user_preferences($departmentfieldname, null, $USER); 
    $positionvalue = get_user_preferences($positionfieldname, null, $USER);

    // Check if either field is empty.
    $profileincomplete = (empty($departmentvalue) || empty($positionvalue));

    // Define the URL of our profile completion page.
    $completionpageurl = new moodle_url('/local/presentyou/complete_profile.php');

    // Get the URL of the logout page.
    $logoutpageurl = new moodle_url('/login/logout.php');

    // <<< INIZIO BLOCCO AGGIUNTO: URLs delle pagine di autenticazione/registrazione da ESCLUDERE
    // Non vogliamo reindirizzare l'utente se sta usando queste pagine.
    $selfregistrationurl = new moodle_url('/login/signup.php');
    $forgotpasswordurl = new moodle_url('/login/forgot_password.php');
    $confirmemailurl = new moodle_url('/login/confirm.php');
    // <<< FINE BLOCCO AGGIUNTO

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
            // <<< INIZIO BLOCCO AGGIUNTO: Validazione URL di redirect per sicurezza
            // Assicurati che l'URL sia valido e interno al sito Moodle
            if (is_valid_internal_url($redirecturl, true)) {
                redirect(new moodle_url($redirecturl));
            } else {
                // Fallback a una pagina sicura (dashboard) se l'URL di redirect non è valido
                redirect(new moodle_url('/my/'));
            }
            // <<< FINE BLOCCO AGGIUNTO
        } else {
            redirect(new moodle_url('/my/')); // Default to dashboard
        }
    }
}

// Nota: nessuna altra istruzione o chiamata a funzione al di fuori della funzione
// local_presentyou_middleware() in un file middleware.php.

