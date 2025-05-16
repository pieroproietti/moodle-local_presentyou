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
$plugin->version = 2025051603; // YYYYMMDD Revision - Update this when you make changes
$plugin->requires = 2022112800; // Moodle 4.1 (or your target Moodle version)
$plugin->maturity = MATURITY_STABLE; // MATURITY_ALPHA, MATURITY_BETA, MATURITY_RC, MATURITY_STABLE
$plugin->release = 'v1.0';

// This line tells Moodle to load our middleware.php file on every request.
$plugin->middlewarefile = true;
