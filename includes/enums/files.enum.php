<?php
namespace Enums\Files;

use MyClabs\Enum\Enum as Enum;

class Forms extends Enum {
	const LOGIN_URL = FORMS_URL . 'login.form.php';
	const LOGIN_PATH = FORMS_DIR . 'login.form.php';

	const REGISTER_URL = FORMS_URL . 'register.form.php';
	const REGISTER_PATH = FORMS_DIR . 'register.form.php';

	const LOGOUT_URL = FORMS_URL . 'logout.form.php';
	const LOGOUT_PATH = FORMS_DIR . 'logout.form.php';
}

class Userdata extends Enum {
	const UPLOAD_URL = INCLUDES_URL . 'files/file_upload.php';
	const UPLOAD_PATH = INCLUDES_DIR . 'files/file_upload.php';

	const VIEW_URL = INCLUDES_URL . 'files/file_view.php';
	const VIEW_PATH = INCLUDES_DIR . 'files/file_view.php';
}
