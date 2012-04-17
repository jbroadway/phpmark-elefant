<?php

/**
 * Creates a preview of a web page based on POST data sent to it.
 * POST data must match values available to the Page object.
 */

$this->require_admin ();

$wp = new Webpage ($_POST);

$page->id = $_POST['id'];
$page->title = $_POST['title'];
$page->menu_title = (! empty ($_POST['menu_title'])) ? $_POST['menu_title'] : $_POST['title'];
$page->window_title = (! empty ($_POST['window_title'])) ? $_POST['window_title'] : $_POST['title'];
$page->description = $_POST['description'];
$page->keywords = $_POST['keywords'];
$page->layout = $_POST['layout'];
$page->head = '';

echo $tpl->run_includes ($_POST['body']);

?>