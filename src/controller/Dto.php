<?php

namespace Controller;

class Dto
{
    const STATUS_OK = 'ok';
    const STATUS_WARNING = 'warning';
    const STATUS_ERROR = 'error';

    protected $_data;

    public function setAnswer($answer)
    {
        $this->_data['answer'] = $answer;
        return $this;
    }

    public function getAnswer()
    {
        return $this->_data['answer'];
    }

    public function addMessage($message)
    {
        $this->_data['messages'][] = $message;
        return $this;
    }

    public function addWarning($message)
    {
        $this->setAnswer(self::STATUS_WARNING);
        $this->addMessage($message);
        return $this;
    }

    public function addError($message)
    {
        $this->setAnswer(self::STATUS_ERROR);
        $this->addMessage($message);
        return $this;
    }

    public function addData($key, $value)
    {
        $this->_data[$key] = $value;
        return $this;
    }

    public function getData()
    {
        return $this->_data;
    }

    public function getJsonData()
    {
        return json_encode($this->_data);
    }

    public function addBatchData(array $data)
    {
        foreach ($data as $k => $v) {
            $this->addData($k, $v);
        }
        return $this;
    }
}