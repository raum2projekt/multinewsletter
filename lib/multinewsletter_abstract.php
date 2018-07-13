<?php

/**
 * This file is part of the Kreatif\Project package.
 *
 * @author Kreatif GmbH
 * @author a.platter@kreatif.it
 * Date: 27.09.17
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
abstract class MultinewsletterAbstract {
    protected $data = [];

    public function __get($key) {
        $key   = $this->getDBKey($key);
        $value = $this->getValue($key);

        if ($key == 'group_ids') {
            $value = explode('|', trim($value, '|'));
        }
        else if ($key == 'recipients') {
            if (strpos($value, '|') !== FALSE) {
                $value = explode('|', trim($value, '|'));
            }
            else {
                $value = explode(',', trim($value, ','));
            }
        }
        return $value;
    }

	/**
	 * Get ID
	 * @return int ID or null if no ID is available
	 */
	public function getId() {
        return $this->getValue('id', null);
    }

    public function getValue($key, $default = '')
    {
        return isset($this->data[$key]) && strlen($this->data[$key]) ? $this->prepareValue($key, $this->data[$key], $default) : $default;
    }

    public function getArrayValue($key, $default = []) {
        $value = $this->getValue($key, '');
        if (strpos($value, '|') !== FALSE) {
            $value = preg_grep('/^\s*$/s', explode("|", $value), PREG_GREP_INVERT);
        }
        else if (strpos($value, ',') !== FALSE) {
            $value = preg_grep('/^\s*$/s', explode(",", $value), PREG_GREP_INVERT);
        }
        else if (strlen($value)) {
            $value = [$value];
        }
        else {
            $value = $default;
        }
        return $value;
    }

    public function getData()
    {
        return $this->data;
    }

    public function __set($key, $value)
    {
        switch ($key) {
            case 'createdate':
            case 'updatedate':
            case 'activationdate':
                $value = date('Y-m-d H:i:s', $value);
                break;
        }
        $this->setValue($key, $value);
    }

    public function setValue($key, $value)
    {
        $this->data[$key] = trim($this->filterValue($key, $value));
        return $this;
    }

    protected function getDBKey($key)
    {
        if (in_array($key, ['user_id', 'group_id', 'archive_id'])) {
            $key = 'id';
        }
        else if ($key == 'createip') {
            $key = 'createip';
        }
        else if ($key == 'activationip') {
            $key = 'activationip';
        }
        else if ($key == 'updateIP') {
            $key = 'updateip';
        }
        return $key;
    }

    protected function filterValue($key, $value)
    {
        switch ($key) {
            case 'email':
                $value = strtolower($value);
            default:
                $value = addslashes($value);
                break;

            case 'activationkey':
            case 'subject':
            case 'sender_name':
                $value = htmlspecialchars($value);
                break;

            case 'htmlbody':
                $value = base64_encode($value);
                break;

            case 'group_ids':
            case 'recipients':
                $value = str_replace(' ', '', is_array($value) ? implode('|', array_filter($value)) : $value);
                break;

            case 'createip':
            case 'activationip':
            case 'updateip':
                $value = filter_var($value, FILTER_VALIDATE_IP);
                break;
        }
        return trim($value);
    }

    protected function prepareValue($key, $value, $default)
    {
        switch ($key) {
            default:
                $value = stripslashes($value);
                break;

            case 'activationkey':
            case 'subject':
                $value = htmlspecialchars_decode($value);
                break;

            case 'htmlbody':
                $value = base64_decode($value);
                break;

            case 'createdate':
            case 'updatedate':
            case 'activationdate':
                $value = $value == '0000-00-00 00:00:00' ? $default : $value;
                break;
        }
        return $value;
    }
}