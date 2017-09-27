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
abstract class MultinewsletterAbstract
{
    protected $data = [];

    public function __get($key)
    {
        $key   = $this->getDBKey($key);
        $value = $this->getValue($key);

        if ($key == 'group_ids') {
            $value = explode('|', $value);
        }
        else if ($key == 'recipients') {
            $value = explode(',', $value);
        }
        return $value;
    }

    public function getId()
    {
        return $this->getValue('id', null);
    }

    public function getValue($key, $default = '')
    {
        return isset($this->data[$key]) ? $this->prepareValue($key, $this->data[$key]) : $default;
    }

    public function getArrayValue($key, $default = [])
    {
        $value = $this->getValue($key, '');

        if (strpos($value, '|')) {
            $value = explode('|', $value);
        }
        else if (strpos($value, ',')) {
            $value = explode(',', $value);
        }
        else if (strlen($value)) {
            $value = [$value];
        }
        else {
            $value = $default;
        }
        return $value;
    }

    public function getName()
    {
        return trim($this->getValue('firstname') . ' ' . $this->getValue('lastname'));
    }

    public function getData()
    {
        return $this->data;
    }

    public function __set($key, $value)
    {
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
        else if ($key == 'createIP') {
            $key = 'createip';
        }
        else if ($key == 'activationIP') {
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
                $value = str_replace(' ', '', is_array($value) ? implode('|', $value) : $value);
                break;

            case 'createip':
            case 'activationip':
            case 'updateip':
                $value = filter_var($value, FILTER_VALIDATE_IP);
                break;
        }
        return trim($value);
    }

    protected function prepareValue($key, $value)
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
        }
        return $value;
    }
}