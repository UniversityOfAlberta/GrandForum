<?php

namespace Behat\Behat\Formatter;
use Behat\Behat\Formatter\HtmlFormatter;

class MyHtmlFormatter extends HtmlFormatter {
/*
    protected function printStep($step, $result, $definition = null, $snippet = null, $exception = null){
        $this->writeln('<li class="' . $this->getResultColorCode($result) . '">');
        parent::printStep($step, $result, $definition, $snippet, $exception);
        $this->writeln("<a class='colorbox' href='../screenshots/".FeatureContext::$scenarioId."_".FeatureContext::$stepId.".png'>Show Screenshot</a></li>");
    }
    */
    
}

?>
