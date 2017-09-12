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
 * Form for editing HTML block instances.
 *
 * @package   block_reporttiles
 * @copyright 2009 Tim Hunt
 * @license   http://www.gnu.org/copyleft/gpl.reporttiles GNU GPL v3 or later
 */
/**
 * Form for editing HTML block instances.
 *
 * @copyright 2009 Tim Hunt
 * @license   http://www.gnu.org/copyleft/gpl.reporttiles GNU GPL v3 or later
 */
require_once $CFG->dirroot . '/blocks/cobalt_reports/report.class.php';

class block_reporttiles_edit_form extends block_edit_form {

    protected function specific_definition($mform) {
        global $CFG, $DB, $PAGE,$OUTPUT;
        $PAGE->requires->js('/blocks/reporttiles/js/jscolor.js');

        // Fields for editing HTML block title and contents.
        $mform->addElement('header', 'configheader', get_string('blocksettings', 'block'));

         $mform->addElement('filepicker', 'config_logo', get_string('file'), null,
                            array('maxbytes' => 51200, 'maxfiles'=>1,'accepted_types' => array('.jpg','.jpeg','.png','.gif')));
        
        $mform->addElement('text', 'config_tilescolourpicker', get_string('tilesbackground', 'block_reporttiles'),array('class'=>'jscolor'));
        $mform->setType('config_tilescolourpicker', PARAM_RAW);
        
        $mform->addElement('text', 'config_tilescolour', get_string('tilestextcolour', 'block_reporttiles'),array('class'=>'jscolor','value'=>'000000'));
        $mform->setType('config_tilescolour', PARAM_RAW);

        $mform->addElement('text', 'config_url', get_string('url', 'block_reporttiles'),array('size'=>100));
        $mform->setType('config_url', PARAM_TEXT);

    }

    function set_data($defaults) {

        if (!$this->block->user_can_edit() && !empty($this->block->config->title)) {
            // If a title has been set but the user cannot edit it format it nicely
            $title = $this->block->config->title;
            $defaults->config_title = format_string($title, true, $this->page->context);
            // Remove the title from the config so that parent::set_data doesn't set it.
            unset($this->block->config->title);
        }
        parent::set_data($defaults);
        // restore $text
        if (!isset($this->block->config)) {
            $this->block->config = new stdClass();
        }
        if (isset($title)) {
            // Reset the preserved title
            $this->block->config->title = $title;
        }
    }
}
