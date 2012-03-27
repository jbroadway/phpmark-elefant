<?php

/**
 * List of languages.
 */

$this->require_admin ();

if (! file_exists ('lang/_index.php')) {
	$this->redirect ('/translator/build');
}

require_once ('apps/translator/lib/Functions.php');

$page->layout = 'admin';

$page->title = i18n_get ('Languages');

global $i18n;

echo $tpl->render ('translator/index', $i18n);

?>