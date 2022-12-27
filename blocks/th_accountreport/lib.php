<?php
//lay ngay sau ngay ket thuc
function getDayTypeInt($endday, $range) {
	$day = (new DateTime())->setTimestamp(usergetmidnight($endday));
	$day->modify($range);
	$start = $day->getTimestamp();
	return $start;
}
//chuyen ngay kieu int sang kieu date
function getIntTypeDate($date) {
	return date('d/m/Y', $date);
}
/**
 * [getUser Lấy các Tài khoản được tạo trong khoản thời gian] 
 *
 * @param  [int] $to	Ngày bắt đầu (từ )giờ 0 phút 0 giây Ngày bắt đầu)
 * @param  [int] $from	Ngày kết thúc (đến 23 giờ 59 phút 59 giây Ngày kết thúc)
 * @return [array]		Các Tài khoản được tạo trong khoảng thời gian
 */
function getUser($to, $from) {
	global $DB;
	//echo date('d/m/Y H:i:s', $to) . ' - ' . date('d/m/Y H:i:s', $from) . '</br>';
	return $DB->get_records_sql(
		'SELECT id,timecreated FROM {user}
		WHERE deleted=0 AND suspended=0 AND timecreated>=? AND timecreated<? order by id DESC',
		[$to, $from]
	);
}