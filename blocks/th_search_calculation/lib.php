<?php

function local_th_update_calculation_controls(context $context, moodle_url $currenturl) {
    $tabs = array();
    $currenttab = 'view';
    $view = new moodle_url('/blocks/th_search_calculation/view.php');

    if (has_capability('block/th_search_calculation:view', $context)) {
        $addurl = new moodle_url('/blocks/th_search_calculation/view.php');
        $tabs[] = new tabobject('view', $addurl, "Danh sách môn học chứa công thức tổng");
        if ($currenturl->get_path() === $addurl->get_path()) {
            $currenttab = 'view';
        }
    }

    if (has_capability('block/th_search_calculation:view', $context)) {
        $addurl = new moodle_url('/blocks/th_search_calculation/view2.php');
        $tabs[] = new tabobject('view2', $addurl, "Danh sách môn học chứa công thức bài kiểm tra");
        if ($currenturl->get_path() === $addurl->get_path()) {
            $currenttab = 'view2';
        }
    }

    if (has_capability('block/th_search_calculation:view', $context)) {
        $addurl = new moodle_url('/blocks/th_search_calculation/view3.php');
        $tabs[] = new tabobject('view3', $addurl, "Danh sách môn học chứa công thức điểm chuyên cần");
        if ($currenturl->get_path() === $addurl->get_path()) {
            $currenttab = 'view3';
        }
    }

    if (has_capability('block/th_search_calculation:view', $context)) {
        $addurl = new moodle_url('/blocks/th_search_calculation/th_update_calculation.php');
        $tabs[] = new tabobject('update_calculation', $addurl, "Cập nhật công thức hàng loạt");
        if ($currenturl->get_path() === $addurl->get_path()) {
            $currenttab = 'update_calculation';
        }
    }
    
    if (count($tabs) > 1) {
        return new tabtree($tabs, $currenttab);
    }
    return null;
}

function th_update_calculation_display_table_error($errors) {
    $table1 = new html_table($errors);
    $table1->head = array('STT', 'Hints');
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

function th_display_table_update_calculation($data_calculation) {
    global $DB, $USER;
    $table = new html_table();
    $table->head = array('STT','Khóa học', 'Tên điểm', 'Mã điểm', 'Công thức hiện tại', 'Công thức mới', 'Trạng thái');
    $stt = 0;

    foreach ($data_calculation as $k => $calculation) {

        $stt = $stt + 1;
        $row = new html_table_row();
        $cell = new html_table_cell($stt);
        $row->cells[] = $cell;
        $cell = new html_table_cell($calculation->courses->fullname);
        $row->cells[] = $cell;
        $cell = new html_table_cell($calculation->ten_diem);
        $row->cells[] = $cell;
        $cell = new html_table_cell($calculation->ma_diem);
        $row->cells[] = $cell;
        $cell = new html_table_cell($calculation->calculation);
        $row->cells[] = $cell;
        $cell = new html_table_cell($calculation->calculation_new);
        $row->cells[] = $cell;
        $cell = new html_table_cell();
        $cell->text = html_writer::tag('span', 'Công thức sẽ được cập nhật',
            array('class' => 'badge badge-success'));
        $row->cells[] = $cell;
        $table->data[] = $row;
    }

    $table->attributes = array('class' => 'th_update_calculation_table', 'border' => '1');
    $table->attributes['style'] = "width: 100%; text-align:center;";
    $html = html_writer::table($table);
    return $html;
}


function th_replace_calculation($calculation){
    $pos = strpos($calculation, ',');
    $pos1 = strpos($calculation, ';');
    if ($pos !== false && $pos1 !== false) {
        $calculation1 = $calculation;
    } else {
        $calculation1 = str_replace(",", ";", $calculation);
        $calculation1 = str_replace(".", ",", $calculation1);
    }

    return $calculation1;
}

function th_replace_calculation1($calculation){
    $pos = strpos($calculation, '.');
    $pos1 = strpos($calculation, ',');
    if ($pos !== false && $pos1 !== false) {
        $calculation1 = $calculation;
    } else {
        $calculation1 = str_replace(",", ".", $calculation);
        $calculation1 = str_replace(";", ",", $calculation1);
    }

    return $calculation1;
}

?>