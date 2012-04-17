<?php

if (! User::require_admin ()) {
	$this->redirect ('/admin');
}

$page->title = 'Hello admin';
$page->layout = 'admin';

?>