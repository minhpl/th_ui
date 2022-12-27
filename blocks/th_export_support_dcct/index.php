<?php

require_once '../../config.php';
require_once $CFG->libdir . '/adminlib.php';
require_once $CFG->dirroot.'/grade/lib.php';
require_once $CFG->libdir.'/mathslib.php';
require_once $CFG->dirroot . '/blocks/th_export_support_dcct/edit_form.php';
require_once $CFG->dirroot . '/blocks/th_export_support_dcct/lib.php';

global $DB, $OUTPUT, $PAGE, $COURSE, $USER;

// Check for all required variables.
$courseid = $COURSE->id;
$returnto = optional_param('returnto', 'course', PARAM_ALPHANUM); // Generic navigation return page switch.
$returnurl = optional_param('returnurl', '', PARAM_LOCALURL);

if (!$course = $DB->get_record('course', array('id' => $courseid))) {
	print_error('invalidcourse', 'block_th_export_support_dcct', $courseid);
}

require_login($courseid);
require_capability('block/th_export_support_dcct:view', context_course::instance($COURSE->id));

$pageurl = '/blocks/th_export_support_dcct/index.php';
$title = get_string('title', 'block_th_export_support_dcct');
$context = context_system::instance();
$PAGE->set_url('/blocks/th_export_support_dcct/index.php');
$PAGE->set_pagelayout('standard');
$PAGE->set_heading(get_string('th_export_support_dcct', 'block_th_export_support_dcct'));
$PAGE->set_title($SITE->fullname . ': ' . get_string('title', 'block_th_export_support_dcct'));

$editurl = new moodle_url('/blocks/th_export_support_dcct/index.php');
$settingsnode = $PAGE->navbar->add(get_string('breadcrumb', 'block_th_export_support_dcct'), $editurl);
$settingsnode->make_active();
	
echo $OUTPUT->header();
echo $OUTPUT->heading('<center>DANH SÁCH GVCN/QLHT</center></br>');
$list_data = $DB->get_records_sql("SELECT * FROM {th_export_support_dcct} ORDER BY role");

// print_object($list_data);
$baseurl = new moodle_url('/blocks/th_export_support_dcct/index.php');

$table       = new html_table();
$table->head = array('STT', 'Mã lớp học', 'Họ tên', 'SDT', 'Email', 'Chức vụ', 'Giới tính', 'Action');
$stt         = 0;

foreach ($list_data as $k => $data) {
    $stt = $stt + 1;
    $urlparams = array('id' => $data->id, 'returnurl' => $baseurl->out_as_local_url(false));
    $link_edit = new moodle_url('/blocks/th_export_support_dcct/edit.php', $urlparams,);
    $edit      = html_writer::link($link_edit, $OUTPUT->pix_icon('t/edit', get_string('edit')), array('title' => get_string('edit')));
    $link_delete = new moodle_url('/blocks/th_export_support_dcct/edit.php', $urlparams + array('delete' => 1));
    $delete      = html_writer::link(
        $link_delete,
        $OUTPUT->pix_icon('t/delete', get_string('delete')),
        array('title' => get_string('delete'))
    );

    $row          = new html_table_row();
    $cell         = new html_table_cell($stt);
    $row->cells[] = $cell;
    $cell         = new html_table_cell($data->ma_lop);
    $row->cells[] = $cell;
    $cell         = new html_table_cell($data->ho_ten);
    $row->cells[] = $cell;
    $cell         = new html_table_cell('0' . $data->sdt);
    $row->cells[] = $cell;
    $cell         = new html_table_cell($data->email);
    $row->cells[] = $cell;
    if ($data->role == 1) {
        $cell         = new html_table_cell('GVCN');
        $row->cells[] = $cell;
    } else {
        $cell         = new html_table_cell('QLHT');
        $row->cells[] = $cell;
    }

    if ($data->gioi_tinh == 1) {
        $cell         = new html_table_cell('Nam');
        $row->cells[] = $cell;
    } else {
        $cell         = new html_table_cell('Nữ');
        $row->cells[] = $cell;
    }

    $cell          = new html_table_cell($edit . $delete);
    $row->cells[]  = $cell;
    $table->data[] = $row;
}

$table->attributes          = array('class' => 'th_export_support_dcct', 'border' => '1');
$table->attributes['style'] = "width: 100%; text-align:center;";
$html = html_writer::table($table);

$baseurl = new moodle_url('/blocks/th_export_support_dcct/index.php');
if ($editcontrols = local_th_export_support_dcct_controls($context, $baseurl)) {
	echo $OUTPUT->render($editcontrols);
}

echo '<style type="text/css">
        #khung{
            width:100%;
            overflow:auto;
        }
    </style>';
echo '<div id="khung">';
echo $html;
echo '</div>';
$lang = current_language();
$PAGE->requires->js_call_amd('local_thlib/main', 'init', array('.th_export_support_dcct', 'LIST GVCN/QLHT', $lang));
echo $OUTPUT->footer();

?>

