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
 * Form for editing Cobalt report dashboard block instances.
 * @package  block_reporttiles
 * @author sreekanth <sreekanth@eabyas.in>
 */
class block_reporttiles extends block_base {

    function init() {
        $this->title = get_string('pluginname', 'block_reporttiles');
    }

    function has_config() {
        return true;
    }
    function get_required_javascript() {
        $this->page->requires->js('/blocks/reporttiles/js/jscolor.js');
    }

    function applicable_formats() {
        return array('all' => true);
    }

    function specialization() {
        $this->title = isset($this->config->title) ? format_string($this->config->title) : format_string(get_string('newreportdashboardblock', 'block_reportdashboard'));
    }

    function hide_header() {
        return true;
    }

    function instance_config_save($data, $nolongerused = false) {
        global  $DB;
        $blockcontext = context_block::instance($this->instance->id);
        file_save_draft_area_files($data->logo, $blockcontext->id, 'block_reporttiles', 'reporttiles',
                   $data->logo, array('maxfiles' => 1));
        $DB->set_field('block_instances', 'configdata', base64_encode(serialize($data)),
                array('id' => $this->instance->id));
    }
    
    function get_content() {
        
        global $CFG, $DB, $PAGE,$USER, $OUTPUT;

        require_once($CFG->dirroot . '/blocks/cobalt_reports/report.class.php');
        require_once($CFG->dirroot . '/blocks/cobalt_reports/locallib.php');
        require_once($CFG->dirroot . '/blocks/reporttiles/lib.php');
        
        $reporttileslib = New reporttiles();

        if ($this->content !== null) {
            return $this->content;
        }
        
        $filteropt = new stdClass();
        $filteropt->overflowdiv = true;
        $this->content = new stdClass;
        $this->content->footer = '';
        $this->content->text = "";
        
        if (isset($this->config->reportlist) && $this->config->reportlist && $DB->record_exists('block_cobalt_reports', array('id' => $this->config->reportlist))) {
            
            $blockinstanceid = $this->instance->id;

            $blockinstance = unserialize(base64_decode($this->instance->configdata));

            $style_tilescolour = (isset($blockinstance->tilescolour)) ? ['style' => 'color:#'.$blockinstance->tilescolour.';'] : [];
            $this->content->text .= html_writer::start_div('dashboard_reporttile_container', $style_tilescolour);

            $this->content->text .= (isset($blockinstance->url) && !empty($blockinstance->url)) ? html_writer::start_tag('a', array('href' => $blockinstance->url)) : '';

            $reportid = $this->config->reportlist;
            $reportclass = create_reportclass($reportid);

            if(!empty($blockinstance->logo)){
                $logo = $reporttileslib->reporttiles_log($blockinstance->logo, $this->instance->id);
            } else{
                $logo = html_writer::empty_tag('img', array('src' => $OUTPUT->image_url('download_icon', 'block_reportdashboard'), 'alt' => get_string('upload')));
            }
            $report = cr_get_reportinstance($this->config->reportlist);
            
            if (isset($report) && !$report->global) {
                $this->content->text .= '';
            } elseif (isset($this->config->reportlist)) {

                $style_colorpicker = (isset($blockinstance->tilescolourpicker)) ? ['style' => 'background:#'.$blockinstance->tilescolourpicker.';'] : [];
       
                $this->content->text .= html_writer::div('', 'reporttile_highlighter', $style_colorpicker);
                $renderer = $this->page->get_renderer('block_reportdashboard');
                $reportclass->create_report($blockinstanceid);
                $data = $reportclass->finalreport->table;

                if(count($data->head) >3 || count($data->data) >1){
                    $this->content->text .= get_string('writingmultirecords','block_reporttiles');//'Report writering more than one record';
                } else{

                    $dataarray = $data->data[0];
                    $headarray = $data->head;
                    $width = 100/count($dataarray).'%';

                    $this->content->text .= html_writer::start_div('dashboard_tiles');
                    $this->content->text .= html_writer::span(
                                                    html_writer::span($this->config->title, 'reporttile_title').
                                                    html_writer::span($logo, 'dashboard_tiles_img')
                                                );
                    $this->content->text .= html_writer::start_div('tiles_information');

                    $this->content->text .= html_writer::start_tag('table', array('width' => '100%'));
                    $this->content->text .= html_writer::start_tag('tr');
                    
                    $i = 1;
                    foreach ($dataarray as $key => $value) {
                        
                        if(count($data->head) == 3){
                            $width = ($i == 3) ? '40%' : '30%';
                        }

                        $this->content->text .= html_writer::tag('td', ucwords($headarray[$key]).':<b>'.$value.'</b>', array('width' => $width));
                        $i++;
                    }

                    $this->content->text .= html_writer::end_tag('tr');
                    $this->content->text .= html_writer::end_tag('table');
                    $this->content->text .= html_writer::end_div();
                    $this->content->text .= html_writer::end_div();
                }
            }
            $this->content->text .= (isset($blockinstance->url) && !empty($blockinstance->url)) ? html_writer::end_tag('a') : '';
            $this->content->text .= html_writer::end_div();

        } else {
            $this->content->text .= '';
        }
        return $this->content;
    }

}
