<?php

require_once __DIR__ . '/../../config.php';

$query = $_GET['key'];
// global $PAGE;
$PAGE->set_url(new moodle_url('/local/th_dashboard/view.php', array('key' => $query)));
$PAGE->set_context(\context_system::instance());
$PAGE->set_title('TH dashboard');
$PAGE->set_pagelayout('admin');

$heading_content = 'Category: TH Dashboard';
$outputhtml      = '';

// mainnode = $PAGE->navigation->find('thdashboard', navigation_node::TYPE_CONTAINER);
$node = $PAGE->navigation->find($query, null);
// print_object($node);
if ($node) {
    // print_object($node->get_content());
}

function get_node_content(navigation_node $node)
{
    $level        = 0;
    $node_content = [];
    while (strcmp($node->key, get_string('thkey', 'local_th_dashboard')) !== 0) {
        $node_content[$level] = $node->get_content();
        $level++;
        $node = $node->parent;
    }
    return $node_content;
}

$node_content = get_node_content($node);
// print_object($node_content);

for ($i = count($node_content) - 1; $i >= 0; $i--) {
    $heading_content .= ' / ' . $node_content[$i];
}

echo $OUTPUT->header();

if ($node->has_children()) {
    foreach ($node->children as $item) {
        $url = $item->action;
        // print_object($url);
        $outputhtml .= $OUTPUT->heading(html_writer::link(new moodle_url($url), $item->get_content()), 3);
    }
}
// print_object($outputhtml);
echo $OUTPUT->heading($heading_content, 2);

echo html_writer::start_tag('form', array('action' => '', 'method' => 'post', 'id' => 'adminsettings'));
echo html_writer::start_tag('div');
echo $outputhtml;
echo html_writer::end_tag('div');
echo html_writer::end_tag('form');

echo $OUTPUT->footer();
