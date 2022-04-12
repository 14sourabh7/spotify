<?php
// helper class for escaper

namespace App\Components;

use Phalcon\Escaper;

class MyEscaper
{
    public function sanitize($input)
    {
        $escaper = new Escaper();
        $arr =  $input;
        $input = $escaper->escapeHtml($arr);
        return $input;
    }
}
