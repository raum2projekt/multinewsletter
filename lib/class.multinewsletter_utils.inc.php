<?php
/**
 * Nützliche Werkzeuge rund um MultiNewsletter.
 */
class multinewsletter_utils {
	public static function createDynFile($file) {
		$fileHandle = fopen($file, 'w');

		fwrite($fileHandle, "<?php\r\n");
		fwrite($fileHandle, "// --- DYN\r\n");
		fwrite($fileHandle, "// --- /DYN\r\n");

		fclose($fileHandle);
	}

	public static function getSettingsFile() {
		return MULTINEWSLETTER_DATA_DIR . 'settings.inc.php';
	}

	public static function includeSettingsFile() {
		$settingsFile = self::getSettingsFile();

		if (!file_exists($settingsFile)) {
			self::updateSettingsFile(false);
		}

		require_once($settingsFile);
	}

	public static function updateSettingsFile($showSuccessMsg = true) {
		global $I18N;

		$settingsFile = self::getSettingsFile();
		$msg = self::checkDirForFile($settingsFile);

		if ($msg != '') {
			if(rex::isBackend()) {
				echo rex_warning($msg);
			}
		} else {
			if (!file_exists($settingsFile)) {
				self::createDynFile($settingsFile);
			}

			$content = "<?php\n\n";
		
			foreach ((array) $REX['ADDON']['multinewsletter']['settings'] as $key => $value) {
				$content .= "\$REX['ADDON']['multinewsletter']['settings']['$key'] = " . var_export($value, true) . ";\n";
			}

			if (rex_put_file_contents($settingsFile, $content)) {
				if (rex::isBackend() && $showSuccessMsg) {
					echo rex_info($I18N->msg('multinewsletter_config_ok'));
				}
			} else {
				if (rex::isBackend()) {
					echo rex_warning($I18N->msg('multinewsletter_config_error'));
				}
			}
		}
	}

	public static function getLangSettingsMsg() {

		if (!isset($REX['ADDON']['multinewsletter']['settings']['lang']) || multinewsletter::getLangCount() != count($REX['ADDON']['multinewsletter']['settings']['lang'])) {
			$icon = '<span title="' . $I18N->msg('multinewsletter_setup_langcount_error') . '" class="multinewsletter-tooltip status exclamation">&nbsp;</span>';
		} else {
			$icon = '';
		}

		return '<span class="rex-form-read" id="lang_hint">' . $I18N->msg('multinewsletter_setup_lang_settings_hint') . '</span>' . $icon;
	}

	public static function checkDir($dir) {
		global $I18N;

		$path = $dir;

		if (!@is_dir($path)) {
			@mkdir($path, rex::getProperty('DIRPERM'), true);
		}

		if (!@is_dir($path)) {
			if (rex::isBackend()) {
				return $I18N->msg('multinewsletter_install_make_dir', $dir);
			}
		} elseif (!@is_writable($path . '/.')) {
			if (rex::isBackend()) {
				return $I18N->msg('multinewsletter_install_perm_dir', $dir);
			}
		}
		
		return '';
	}

	public static function checkDirForFile($fileWithPath) {
		$pathInfo = pathinfo($fileWithPath);

		return self::checkDir($pathInfo['dirname']);
	}
	
	public static function convertVarType($originalValue, $newValue) {
		switch (gettype($originalValue)) {
			case 'string':
				return trim($newValue);
				break;
			case 'integer':
				return intval($newValue);
				break;
			case 'boolean':
				return (bool) $newValue;
				break;
			case 'array':
				if ($newValue == '') {
					return array();
				} else {
					return explode('|', $newValue);
				}
				break;
			default:
				return $newValue;
				
		}
	}
}