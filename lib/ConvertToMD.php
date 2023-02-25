<?php

namespace Mail2Deck;

use League\HTMLToMarkdown\HtmlConverter;
use League\HTMLToMarkdown\Converter\TableConverter;

class ConvertToMD {
    protected $html;

    public function __construct($html) {
        $this->converter = new HtmlConverter([
            'strip_tags' => true,
            'remove_nodes' => 'title style'
        ]);
        $this->converter->getEnvironment()->addConverter(new TableConverter());
        $html = str_ireplace('<div ', ' 
<div ', $html);
        $this->html =  str_ireplace('<p>', '<br><p> ', $html);
    }

    public function execute()
    {
        return $this->converter->convert($this->html);
    }
}
