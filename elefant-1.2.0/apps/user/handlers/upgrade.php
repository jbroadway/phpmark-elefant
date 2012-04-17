<?php

$page->layout = 'admin';

$this->require_admin ();

if ($this->installed ('user', $appconf['Admin']['version']) === true) {
	$page->title = i18n_get ('Upgrade completed');
	echo '<p><a href="/user/admin">' . i18n_get ('Continue') . '</a></p>';
	return;
}

$page->title = i18n_get ('Upgrading User App');

$db = Database::get_connection (1);
$dbtype = $db->getAttribute (PDO::ATTR_DRIVER_NAME);
switch ($dbtype) {
	case 'pgsql':
		db_execute ('alter table "user" alter column "password" type varchar(128)');
		break;
	case 'mysql':
		db_execute ('alter table `user` change column `password` `password` varchar(128) not null');
		break;
	case 'sqlite':
		db_execute ('begin transaction');
		db_execute ('alter table `user` rename to `tmp_user`');
		db_execute ('create table user (
			id integer primary key,
			email char(72) unique not null,
			password char(128) not null,
			session_id char(32) unique,
			expires datetime not null,
			name char(72) not null,
			type char(32) not null,
			signed_up datetime not null,
			updated datetime not null,
			userdata text not null
		)');
		db_execute ('create index user_email_password on user (email, password)');
		db_execute ('create index user_session_id on user (session_id)');
		db_execute ('insert into `user` (id, email, password, session_id, expires, name, type, signed_up, updated, userdata)
			select id, email, password, session_id, expires, name, type, signed_up, updated, userdata from `tmp_user`');
		db_execute ('drop table `tmp_user`');
		db_execute ('commit');
		break;
}

echo '<p>' . i18n_get ('Done.') . '</p>';

$this->mark_installed ('user', $appconf['Admin']['version']);

?>