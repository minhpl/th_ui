<?php

function th_export_parse($list_data)
{
    if (empty($list_data)) {
        return array();
    } else {
        $rawlines = explode(PHP_EOL, $list_data);
        $result   = array();
        foreach ($rawlines as $rawline) {
            $result[] = trim($rawline);
        }
        return $result;
    }
}

function th_export_get_content($contents)
{
    $max = count($contents) - 1;
    for ($i = 1; $i < $max; ++$i) {
        $listcourses_new[] = $contents[$i];
    }
    return $listcourses_new;
}

function local_th_export_support_dcct_controls(context $context, moodle_url $currenturl)
{
    $tabs       = array();
    $currenttab = 'view';
    $view       = new moodle_url('/blocks/th_export_support_dcct/index.php');

    if (has_capability('block/th_export_support_dcct:view', $context)) {
        $addurl = new moodle_url('/blocks/th_export_support_dcct/index.php');
        $tabs[] = new tabobject('view', $addurl, "Tất cả");
        if ($currenturl->get_path() === $addurl->get_path()) {
            $currenttab = 'view';
        }
    }

    if (has_capability('block/th_export_support_dcct:view', $context)) {
        $addurl = new moodle_url('/blocks/th_export_support_dcct/view2.php');
        $tabs[] = new tabobject('import', $addurl, "Upload GVCN/QLHT");
        if ($currenturl->get_path() === $addurl->get_path()) {
            $currenttab = 'import';
        }
    }
    
    if (has_capability('block/th_export_support_dcct:view', $context)) {
        $addurl = new moodle_url('/blocks/th_export_support_dcct/edit.php');
        $tabs[] = new tabobject('edit', $addurl, "Thêm");
        if ($currenturl->get_path() === $addurl->get_path()) {
            $currenttab = 'edit';
        }
    }

    if (has_capability('block/th_export_support_dcct:view', $context)) {
        $addurl = new moodle_url('/blocks/th_export_support_dcct/view.php');
        $tabs[] = new tabobject('export', $addurl, "Xuất file");
        if ($currenturl->get_path() === $addurl->get_path()) {
            $currenttab = 'export';
        }
    }

    if (count($tabs) > 1) {
        return new tabtree($tabs, $currenttab);
    }
    return null;
}

function th_display_table_export_dcct($data_export_dcct)
{
    global $DB;
    $table = new html_table();
    $table->head = array('STT', 'Tên môn', 'Mã lớp', 'Mã lớp 1', 'Tên GVCN', 'Tên QLHT', 'Tên GVCM');
    $stt = 0;
    foreach ($data_export_dcct as $k => $data) {

        $ds_support = $data->ds_support;

        foreach ($ds_support as $support) {
            $stt = $stt + 1;
            $row = new html_table_row();
            $cell = new html_table_cell($stt);
            $row->cells[] = $cell;

            $cell = new html_table_cell($data->ten_mon);
            $row->cells[] = $cell;
            $cell = new html_table_cell($support->ma_lop);
            $row->cells[] = $cell;
            $cell = new html_table_cell($support->ma_lop1);
            $row->cells[] = $cell;
            $cell = new html_table_cell($support->gvcn->ho_ten);
            $row->cells[] = $cell;
            $cell = new html_table_cell($support->qlht->ho_ten);
            $row->cells[] = $cell;
            $cell = new html_table_cell($data->gvcm);
            $row->cells[] = $cell;
            $table->data[] = $row;
        }
    }

    $html = html_writer::table($table);
    return $html;
}


function th_display_table_import_dcct($data_import_dcct)
{
    global $DB;
    $table = new html_table();
    $table->head = array('STT', 'Mã lớp', 'Họ tên', 'Sđt', 'Email', 'Chức vụ', 'Giới tính');
    $stt = 0;
    foreach ($data_import_dcct as $k => $data) {

        $stt = $stt + 1;
        $row = new html_table_row();
        $cell = new html_table_cell($stt);
        $row->cells[] = $cell;

        $cell = new html_table_cell($data->ma_lop);
        $row->cells[] = $cell;
        $cell = new html_table_cell($data->ho_ten);
        $row->cells[] = $cell;
        $cell = new html_table_cell($data->sdt);
        $row->cells[] = $cell;
        $cell = new html_table_cell($data->email);
        $row->cells[] = $cell;
        
        if ($data->chuc_vu == 1) {
            $chuc_vu = 'GVCN';
        } else {
            $chuc_vu = 'QLHT';
        }

        $cell = new html_table_cell($chuc_vu);
        $row->cells[] = $cell;

        if ($data->gioi_tinh == 1) {
            $gioi_tinh = 'Nam';
        } else {
            $gioi_tinh = 'Nữ';
        }

        $cell = new html_table_cell($gioi_tinh);
        $row->cells[] = $cell;

        $table->data[] = $row;
        
    }

    $html = html_writer::table($table);
    return $html;
}

function th_display_table_export_dcct_error($errors)
{
    $table1 = new html_table($errors);
    $table1->head = array(get_string('STT', 'block_th_bulk_override'), get_string('Hints', 'block_th_bulk_override'));
    $stt = 0;
    foreach ($errors as $k => $error) {
        $stt = $stt + 1;
        $row = new html_table_row();
        $cell = new html_table_cell($stt);
        $row->cells[] = $cell;
        $cell = new html_table_cell($error);
        $row->cells[] = $cell;
        $table1->data[] = $row;
    }
    $html1 = html_writer::table($table1);
    return $html1;
}

?>