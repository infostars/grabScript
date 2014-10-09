<?php

class grab
{
    protected $config = '{
        start_url: "http://ya.ru/",
        userAgent: "Curl 0.7",
        parserDriver: "grabtemplate"
    }';

    protected $queue;

    protected function onForeachCycleItem($inputItem)
    {
        $a = $inputItem;
        $a['value'] = 123;

        return $a;
    }
}