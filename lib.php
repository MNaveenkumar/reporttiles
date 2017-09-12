<?php

class reporttiles {
    /**
     * [reporttiles_log description]
     * @param  [int] $itemid [Item ID]
     * @return [String]      [url]
     */
	public function reporttiles_log($itemid,$blockinstanceid){
		global $DB, $CFG, $USER;
	 	$file =$DB->get_record('files', array('itemid' => $itemid));
        if(empty($file)){
            $logo ='';
        } else{
            $context = context_block::instance($blockinstanceid);
            $fs = get_file_storage();
            $files = $fs->get_area_files($context->id, 'block_reporttiles', 'reporttiles', $file->itemid, 'filename', false);
            $url = array();
            foreach ($files as $file) {
                $isimage=$file->is_valid_image();
                $filename = $file->get_filename();
                $ctxid = $file->get_contextid();
                $component = $file->get_component();
                $itemid = $file->get_itemid();
                if($isimage){
                   $url[] = $CFG->wwwroot."/pluginfile.php/$ctxid/block_reporttiles/reporttiles/$itemid/$filename";
                }
            }
            $logo = html_writer::empty_tag('img', array('src' => $url[0], 'style' => 'width:50px;height:50px;'));
        }
        return  $logo;
	}
}
/**
 * [block_cobalt_reports_pluginfile description]
 * @param  [type] $course        [description]
 * @param  [type] $cm            [description]
 * @param  [type] $context       [description]
 * @param  [type] $filearea      [description]
 * @param  [type] $args          [description]
 * @param  [type] $forcedownload [description]
 * @param  array  $options       [description]
 * @return [type]                [description]
 */
function block_reporttiles_pluginfile($course, $cm, $context, $filearea, $args, $forcedownload, array $options = array()) {
    global $CFG;

    if ($filearea == 'reporttiles') {
        $itemid = (int) array_shift($args);

        // if ($itemid > 0) {
        //     return false;
        // }
        $fs = get_file_storage();
        $filename = array_pop($args);
        if (empty($args)) {
            $filepath = '/';
        } else {
            $filepath = '/' . implode('/', $args) . '/';
        }

        $file = $fs->get_file($context->id, 'block_reporttiles', $filearea, $itemid, $filepath, $filename);

        if (!$file) {
            return false;
        }
        $filedata = $file->resize_image(200, 200);
        \core\session\manager::write_close();
        send_stored_file($file, null, 0, 1);
    }

    send_file_not_found();
}