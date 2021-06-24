<?php
define('TESTING', true);
require_once("../config/Config.php");
require_once("../Classes/simplehtmldom/simple_html_dom.php");

exec(sprintf("%s > %s 2>&1 & echo $! >> %s", 
             "phantomjs --webdriver=8643 --ignore-ssl-errors=true", 
             "phantomjs.log", 
             "phantomjs.pid"));

use Behat\Behat\Context\ClosuredContextInterface,
    Behat\Behat\Context\TranslatedContextInterface,
    Behat\Behat\Context\BehatContext,
    Behat\Behat\Exception\PendingException;
use Behat\Gherkin\Node\PyStringNode,
    Behat\Gherkin\Node\TableNode;

//
// Require 3rd-party libraries here:
//
require_once 'vendor/phpunit/phpunit/src/Framework/Assert/Functions.php';

/**
 * Features context.
 */
class FeatureContext extends Behat\MinkExtension\Context\MinkContext {

    static $scenarioId = 0;
    static $stepId;
    static $skipScreenshot = false;
    static $screenshotTaken = false;
    
    /**
     * Initializes context.
     * Every scenario gets it's own context object.
     *
     * @param array $parameters context parameters (set them up through behat.yml)
     */
    public function __construct(array $parameters){
        global $currentSession;
        $currentSession = $this;
        self::$stepId = 1;
        self::$scenarioId++;
    }
    
    public function spin($lambda, $wait = 5000, $args=array()){
        $interval = 50*1000;
        $timeElapsed = 0;
        $start = microtime(true);
        $time = microtime(true);
        while($timeElapsed*1000 < $wait){
            try{
                if($lambda($this, $args)){
                    return true;
                }
            }catch(Exception $e){
                // do nothing
            }
            usleep($interval);
            $time = microtime(true);
            $timeElapsed = ($time - $start);
        }
        throw new Exception("Timeout thrown");
    }
    
    /**
     * @BeforeSuite
     */
    public static function prepare($event){
        global $currentSession;
        // Create test database
        system("php ../maintenance/seed.php &> /dev/null");
        system("rm -f screenshots/*");
        $fp = fopen("../test.tmp", 'w');
        fwrite($fp, "This file should delete it's self once the test suite is done running.\nDo not delete this file until then.");
        fclose($fp);
        $currentSession->getSession()->getDriver()->resizeWindow(1280, 1024,'current');
    }
    
    /**
     * @AfterSuite
     */
    public static function clean($event){
        global $currentSession;
        $currentSession->getSession()->stop();
        system("php ../maintenance/cleanAllLists.php &> /dev/null");
        unlink("../test.tmp");
    }
    
    /**
     * @BeforeStep
     */
    public static function beforeStep($event){
        global $currentSession;
        // Delay the session so that it doesn't process futher while the page is still loading
        try{
            $currentSession->getSession()->wait(25);
        }
        catch(Exception $e){
            
        }
    }
    
    /**
     * @AfterStep
     */
    public static function afterStep($event){
        global $currentSession;
        /*try {
            // Check for PHP errors on each step
            $currentSession->assertSession()->pageTextNotContains("Warning:");
            $currentSession->assertSession()->pageTextNotContains("Fatal error:");
            $currentSession->assertSession()->pageTextNotContains("Notice:");
            $currentSession->assertSession()->pageTextNotContains("Parse error:");
        }
        catch(Exception $e){
            
        }*/
        if((strstr(strtolower($event->getStep()->getText()), "i should see") !== false ||
            strstr(strtolower($event->getStep()->getText()), "i should not see") !== false)){
            self::$skipScreenshot = true;
        }
        self::$screenshotTaken = false;
        try{
            if(!self::$skipScreenshot){
                $currentSession->iTakeAScreenshot();
                self::$screenshotTaken = true;
            }
        }
        catch(Exception $e){
            
        }
        self::$skipScreenshot = false;
        self::$stepId++;
    }

    /**
     * @Given /^I am logged in as "([^"]*)" using password "([^"]*)"$/
     */
    public function iAmLoggedInAsUsingPassword($username, $password){
        $this->visit('index.php');
        $this->fillField('wpName', $username);
        $this->fillField('wpPassword', $password);
        $this->pressButton('wpLoginattempt');
    }
    
    /**
     * @Then /^I take a screenshot$/
     */
    public function iTakeAScreenshot(){
        file_put_contents("screenshots/".self::$scenarioId."_".self::$stepId.".png", $this->getSession()->getDriver()->getScreenshot());
        file_put_contents("screenshots/".self::$scenarioId."_".self::$stepId.".html", $this->getSession()->getPage()->getContent());
    }
    
    /**
     * @When I accept confirmation dialogs
     */
    public function acceptConfirmation() {
      $this->getSession()->getDriver()->executeScript('window.confirm = function(){return true;}');
    }
    
    /**
     * @When I do not accept confirmation dialogs
     */
    public function acceptNotConfirmation() {
      $this->getSession()->getDriver()->executeScript('window.confirm = function(){return false;}');
    }

    /**
     * @Given /^I check "([^"]*)" from "([^"]*)"$/
     */
    public function iCheckFrom($value, $name) {
        $this->iClickByCss("input[name='{$name}'][value='{$value}']");
    }
    
    /**
     * @When /^I click "([^"]*)"$/
     */
    public function iClick($sel){
        $el = $this->getSession()->getPage()->find('xpath', '//*[text()="'.$sel.'"]');
        if (null === $el) {
            throw new \InvalidArgumentException(sprintf('Could not find XPath selector: "%s"', $sel));
        }
        $el->click();
    }
    
    /**
     * @When /^I click by css "([^"]*)"$/
     */
    public function iClickByCss($sel){
        $el = $this->getSession()->getPage()->find('css', $sel);
        if (null === $el) {
            throw new \InvalidArgumentException(sprintf('Could not find CSS selector: "%s"', $sel));
        }
        $el->click();
    }
    
    /**
     * @When /^I click by css "([^"]*)" if exists$/
     */
    public function iClickByCssIfExists($sel){
        $el = $this->getSession()->getPage()->find('css', $sel);
        if (null === $el) {
            return true;
        }
        $el->click();
    }
    
    /**
     * @When /^(?:|I )check "(?P<option>(?:[^"]|\\")*)" if exists$/
     */
    public function iCheckIfExists($option){
        try {
            $this->checkOption($option);
        }
        catch(Exception $e){
            
        }
        // Always pass
        return true;
    }
    
    /**
     * @When /^(?:|I )uncheck "(?P<option>(?:[^"]|\\")*)" if exists$/
     */
    public function iUnCheckIfExists($option){
        try {
            $this->unCheckOption($option);
        }
        catch(Exception $e){
            
        }
        // Always pass
        return true;
    }
    
    /**
     * @When /^I switch to iframe "([^"]*)"$/
     */
    public function iSwitchToIframe($name){
        $this->getSession()->switchToIFrame($name);
    }
    
    /**
     * @Then /^"([^"]*)" should be subscribed to "([^"]*)"$/
     */
    public function shouldBeSubscribedTo($email, $list){
        self::$skipScreenshot = true;
        $command = "/usr/lib/mailman/bin/list_members $list";
        exec($command, $output);
        $found = false;
        foreach($output as $line){
            if($line == $email){
                $found = true;
            }
        }
        assertTrue($found);
    }
    
    /**
     * @Then /^"([^"]*)" should not be subscribed to "([^"]*)"$/
     */
    public function shouldNotBeSubscribedTo($email, $list){
        self::$skipScreenshot = true;
        $command = "/usr/lib/mailman/bin/list_members $list";
        exec($command, $output);
        $found = false;
        foreach($output as $line){
            if($line == $email){
                $found = true;
            }
        }
        assertFalse($found);
    }
    
    /**
     * @Then /^unsubscribe "([^"]*)" from "([^"]*)"$/
     */
    public function unsubscribeFrom($email, $list){
        self::$skipScreenshot = true;
        $command =  "/usr/lib/mailman/bin/remove_members -n -N $list $email";
		exec($command, $output);
		assertTrue(count($output) == 0 || (count($output) > 0 && $output[0] == ""));
    }
    
    /**
     * @Then /^The load time should be no greater than "([^"]*)"$/
     */
    public function theLoadTimeShouldBeNoGreaterThan($maxTime)
    {
        $time = $this->getSession()->evaluateScript('return window.performance.timing.domContentLoadedEventEnd- window.performance.timing.navigationStart;');
        assertFalse($time > $maxTime);
    }
    
    /**
     * @Given /^I wait "([^"]*)"$/
     */
    public function iWait($ms){
        $this->getSession()->wait($ms);
    }
    

    
    /**
     * @Given /^I wait until I see "([^"]*)" in "([^"]*)" up to "([^"]*)"$/
     */
    public function iWaitOrUntilISeeIn($text, $sel, $ms){
        $this->spin(function($context, $args){
            if(strstr($context->getSession()->getPage()->find('css', $args['sel'])->getText(), $args['text']) !== false){;
                return true;
            }
            return false;
        }, $ms, array('sel' => $sel, 'text' => $text));
    }
    
    /**
     * @Given /^I wait until I no longer see "([^"]*)" in "([^"]*)" up to "([^"]*)"$/
     */
    public function iWaitOrUntilINoLongerSeeIn($text, $sel, $ms){
        $this->spin(function($context, $args){
            if(strstr($context->getSession()->getPage()->find('css', $args['sel'])->getText(), $args['text']) === false){;
                return true;
            }
            return false;
        }, $ms, array('sel' => $sel, 'text' => $text));
    }
    
    /**
     * @Given /^I wait until I see "([^"]*)" up to "([^"]*)"$/
     */
    public function iWaitOrUntilISee($text, $ms){
        $this->spin(function($context, $args){
            $context->assertPageContainsText($args['text']);
            return true;
        }, $ms, array('text' => $text));
    }
    
    /**
     * @Given /^I wait until I no longer see "([^"]*)" up to "([^"]*)"$/
     */
    public function iWaitOrUntilINoLongerSee($text, $ms){
        $this->spin(function($context, $args){
            $context->assertPageNotContainsText($args['text']);
            return true;
        }, $ms, array('text' => $text));
    }
    
    /**
     * @Then /^I should see "(?P<text1>(?:[^"]|\\")*)" before "(?P<text2>(?:[^"]|\\")*)"$/
     */
    public function iShouldSeeBefore($string1, $string2){
        $text = str_replace("&#39;", "'", $this->getSession()->getPage()->getText());
        $strpos1 = strpos($text, stripslashes($string1));
        $strpos2 = strpos($text, stripslashes($string2));
        assertTrue($strpos1 < $strpos2);
    }
    
    /**
     * @Then /^I should not see "(?P<text1>(?:[^"]|\\")*)" before "(?P<text2>(?:[^"]|\\")*)"$/
     */
    public function iShouldNotSeeBefore($string1, $string2){
        $text = str_replace("&#39;", "'", $this->getSession()->getPage()->getText());
        $strpos1 = strpos($text, stripslashes($string1));
        $strpos2 = strpos($text, stripslashes($string2));
        assertFalse($strpos1 < $strpos2);
    }
    
    /**
     * @Given /^I press "([^"]*)" by css$/
     */
    public function pressButtonByCss($css){
        $button = $this->getSession()->getPage()->find('css', $css);
        $button->press();
    }
    
    /**
     * @Given /^I fill in TinyMCE "(?P<id>(?:[^"]|\\")*)" with "(?P<text>(?:[^"]|\\")*)"$/
     */
    public function fillInTinyMCEWith($id, $text){
        $text = addslashes($text);
        $this->getSession()->evaluateScript("$('textarea[name=$id]').tinymce().setContent('$text');");
        $this->getSession()->evaluateScript("$('textarea[name=$id]').tinymce().fire('keyup');");
    }
    
    /**
     * @Given /^I fill in TagIt "(?P<id>(?:[^"]|\\")*)" with "(?P<text>(?:[^"]|\\")*)"$/
     */
    public function fillInTagItWith($id, $text){
        $text = addslashes($text);
        $this->getSession()->evaluateScript("$('[name=$id]').tagit('createTag', '$text');");
    }
    
    /**
     * @Given /^I select from Chosen "(?P<id>(?:[^"]|\\")*)" with "(?P<text>(?:[^"]|\\")*)"$/
     */
    public function selectFromChosenWith($id, $text){
        $text = addslashes($text);
        $this->getSession()->evaluateScript("
            var text = '$text';
            $('select[name=$id] option').each(function(i, el){
                if($(el).val() == text ||
                   $(el).text() == text){
                    text = $(el).val();
                }
            });
            $('select[name=$id]').val(text).trigger('chosen:updated').change()");
    }
    
    /**
     * @Given /^I validate report xml$/
     */
    public function validateReportXML(){
        self::$skipScreenshot = true;
        $files = self::listFiles("../extensions/Reporting/Report/ReportXML");
        foreach($files as $file){
            $content = file_get_contents($file);
            $xml = @simplexml_load_string($content);
            if($xml === false){
                throw new Exception("$file is not a valid xml file.");
            }
            if($xml->getName() == "Report"){
                $children = $xml->children();
                foreach($children as $section){
                    if($section->getName() == "ReportSection"){
                        $ids = array();
                        $blobItems = array();
                        
                        $items = $section->xpath("descendant-or-self::*");
                        foreach($items as $item){
                            if($item->getName() == "ReportItem"){
                                $node = dom_import_simplexml($item);
                                $path = $node->parentNode->getNodePath();
                                
                                $id = @$item->attributes()->id;
                                $blobItem = @$item->attributes()->blobItem."/".$item->attributes()->blobSubItem;
                                
                                if($blobItem != "/"){
                                    if(isset($ids["$id"])){
                                        foreach($ids["$id"] as $p){
                                            if(strstr("$path", "$p") !== false || strstr("$p", "$path") !== false){
                                                throw new Exception("$file containts duplicate id \"$id\" in section \"{$section->attributes()->id}\"");
                                            }
                                        }
                                    }
                                    if(isset($blobItems["$blobItem"])){
                                        foreach($blobItems["$blobItem"] as $p){
                                            if(strstr("$path", "$p") !== false || strstr("$p", "$path") !== false){
                                                if(@$item->attributes()->blobType != "BLOB_ARRAY" || isset($ids["$id"])){
                                                    throw new Exception("$file containts duplicate blobItem/blobSubItem \"$blobItem\" in section \"{$section->attributes()->id}\"");
                                                }
                                            }
                                        }
                                    }
                                    $blobItems["$blobItem"][] = $path;
                                    $ids["$id"][] = $path;
                                }
                            }
                        }
                    }
                }
            }
        }
    }

    /**
    * @When /^I drag an element from "([^"]*)" with id "([^"]*)" from "([^"]*)" to "([^"]*)"$/
    */
    public function dragLiLeftOrRight($parent, $id, $source, $destination)
    {
        $this->getSession()->evaluateScript('$("#'.$parent.' #'.$destination.'").append($("#'.$parent.' #'.$source.' li[data-id='.$id.']").detach());');
        $this->getSession()->evaluateScript('$("#'.$parent.' #'.$source.'")[0].Sortable.option("onSort")({target: $("#'.$parent.' #'.$source.'")});');
        $this->getSession()->evaluateScript('$("#'.$parent.' #'.$destination.'")[0].Sortable.option("onSort")({target: $("#'.$parent.' #'.$destination.'")});');
    }
    
    /**
    * @When /^I log "([^"]*)" I should see "([^"]*)"$/
    */
    public function iLogIShouldSee($js, $text)
    {
        self::$skipScreenshot = true;
        $value = $this->getSession()->evaluateScript('return '.$js);
        $json = json_encode($value);
        assertTrue((strpos($json, $text) !== false));
    }
    
    /**
    * @When /^I log "([^"]*)" I should not see "([^"]*)"$/
    */
    public function iLogIShouldNotSee($js, $text)
    {
        self::$skipScreenshot = true;
        $value = $this->getSession()->evaluateScript('return '.$js);
        $json = json_encode($value);
        assertTrue((strpos($json, $text) === false));
    }
    
    static function listFiles($dir){
        global $config;
        self::$skipScreenshot = true;
        $return = array();
        $files = array_diff(scandir($dir), array('.', '..'));
        foreach($files as $file){
            if(is_dir("{$dir}/{$file}/")){
                $return = array_merge(self::listFiles("{$dir}/{$file}/"), $return);
            }
            else{
                $return[] = "{$dir}{$file}";
            }
        }
        return $return;
    }

}
