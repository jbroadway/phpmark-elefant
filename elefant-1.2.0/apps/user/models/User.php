<?php

/**
 * Elefant CMS - http://www.elefantcms.com/
 *
 * Copyright (c) 2011 Johnny Broadway
 * 
 * Permission is hereby granted, free of charge, to any person obtaining a copy
 * of this software and associated documentation files (the "Software"), to deal
 * in the Software without restriction, including without limitation the rights
 * to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
 * copies of the Software, and to permit persons to whom the Software is
 * furnished to do so, subject to the following conditions:
 * 
 * The above copyright notice and this permission notice shall be included in
 * all copies or substantial portions of the Software.
 * 
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
 * IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
 * FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
 * AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
 * LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
 * OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN
 * THE SOFTWARE.
 */

/**
 * This is the default user authentication source for Elefant. Provides the
 * basic `User::require_login()` and `User::require_admin()` methods, as
 * well as `User::is_valid()` and `User::logout()`. If a user is logged in,
 * the first call to any validation method will initialize the `$user`
 * property to contain the static User object.
 *
 * Note that this class extends Model, so all of the Model methods are
 * available for querying the user list, and for user management, as well.
 *
 * Fields:
 *
 * - id
 * - email
 * - password
 * - session_id
 * - expires
 * - name
 * - type
 * - signed_up
 * - updated
 * - userdata
 *
 * Basic usage of additional methods:
 *
 *   // Send unauth users to myapp/login view
 *   if (! User::require_login ()) {
 *     $page->title = i18n_get ('Members');
 *     echo $this->run ('user/login');
 *     return;
 *   }
 *
 *   // Check if a user is valid at any point
 *   if (! User::is_valid ()) {
 *     // Not allowed
 *   }
 *
 *   // Check the user's type
 *   if (User::is ('member')) {
 *     // Access granted
 *   }
 *
 *   // Get the name value
 *   $name = User::val ('name');
 *
 *   // Get the actual user object
 *   info (User::$user);
 *
 *   // Update and save a user's name
 *   User::val ('name', 'Bob Diggity');
 *   User::save ();
 *
 *   // Encrypt a password
 *   $encrypted = User::encrypt_pass ($password);
 *
 *   // Log out and send them home
 *   User::logout ('/');
 */
class User extends ExtendedModel {
	/**
	 * Tell the ExtendedModel which field should contain the extended properties.
	 */
	public $_extended_field = 'userdata';

	/**
	 * This is the static User object for the current user.
	 */
	public static $user = false;

	/**
	 * Generates a random salt and encrypts a password using MD5.
	 */
	public static function encrypt_pass ($plain) {
		$base = 'ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789';
		$salt = '$2a$07$';
		for ($i = 0; $i < 22; $i++) {
			$salt .= $base[rand (0, 61)];
		}
		return crypt ($plain, $salt . '$');
	}

	/**
	 * Verifies a username/password combo against the database.
	 * Username is matched to the email field. If things check out,
	 * a session_id is generated and initialized in the database
	 * and for the user. Also creates the global $user object
	 * as well, since we have the data (no sense requesting it
	 * twice).
	 */
	public static function verifier ($user, $pass) {
		$u = db_single (
			'select * from `user` where email = ?',
			$user
		);
		if ($u && crypt ($pass, $u->password) == $u->password) {
			self::$user = new User ((array) $u, false);
			self::$user->session_id = md5 (uniqid (mt_rand (), 1));
			self::$user->expires = gmdate ('Y-m-d H:i:s', time () + 2592000); // 1 month
			$try = 0;
			while (! self::$user->put ()) {
				self::$user->session_id = md5 (uniqid (mt_rand (), 1));
				$try++;
				if ($try == 5) {
					return false;
				}
			}
			$_SESSION['session_id'] = self::$user->session_id;
			return true;
		}
		return false;
	}

	/**
	 * A custom handler for simple_auth(). Note: Calls session_start()
	 * for you, and creates the global $user object if a session is
	 * valid, since we have the data already.
	 */
	public static function method ($callback) {
		@session_set_cookie_params (time () + 2592000);
		@session_start ();
		if (isset ($_POST['username']) && isset ($_POST['password'])) {
			return call_user_func ($callback, $_POST['username'], $_POST['password']);
		} elseif (isset ($_SESSION['session_id'])) {
			$u = db_single (
				'select * from `user` where session_id = ? and expires > ?',
				$_SESSION['session_id'],
				gmdate ('Y-m-d H:i:s')
			);
			if ($u) {
				self::$user = new User ((array) $u, false);
				return true;
			}
		}
		return false;
	}

	/**
	 * Simplifies authorization down to:
	 *
	 *   if (! User::require_login ()) {
	 *     // unauthorized
	 *   }
	 */
	public static function require_login () {
		return simple_auth (array ('User', 'verifier'), array ('User', 'method'));
	}

	/**
	 * Simplifies authorization for admins down to:
	 *
	 *   if (! User::require_admin ()) {
	 *     // unauthorized
	 *   }
	 */
	public static function require_admin () {
		if (is_object (self::$user)) {
			if (self::$user->session_id == $_SESSION['session_id']) {
				if (self::$user->type == 'admin') {
					return true;
				}
				return false;
			}
		} else {
			$res = simple_auth (array ('User', 'verifier'), array ('User', 'method'));
			if ($res && self::$user->type == 'admin') {
				return true;
			}
		}
		return false;
	}

	/**
	 * Check if a user is valid.
	 */
	public static function is_valid () {
		if (is_object (self::$user) && self::$user->session_id == $_SESSION['session_id']) {
			return true;
		}
		return User::require_login ();
	}

	/**
	 * Check if a user is of a certain type.
	 */
	public static function is ($type) {
		return (self::$user->type === $type);
	}

	/**
	 * Get or set a specific field's value.
	 */
	public static function val ($key, $val = null) {
		if ($val !== null) {
			self::$user->{$key} = $val;
		}
		return self::$user->{$key};
	}

	/**
	 * Save the user's data to the database.
	 */
	public static function save () {
		return self::$user->put ();
	}

	/**
	 * Log out and optionally redirect to the specified URL.
	 */
	public static function logout ($redirect_to = false) {
		if (self::$user === false) {
			User::require_login ();
		}
		if (! empty (self::$user->session_id)) {
			self::$user->expires = gmdate ('Y-m-d H:i:s', time () - 100000);
			self::$user->put ();
		}
		$_SESSION['session_id'] = null;
		if ($redirect_to) {
			global $controller;
			$controller->redirect ($redirect_to);
		}
	}
}

?>