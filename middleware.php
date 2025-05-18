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
 * Middleware: Dopo il login ci reindirizza su /local/presentyou/complete_profile.php
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
