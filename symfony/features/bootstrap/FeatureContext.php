<?php
define('TESTING', true);
require_once("../config/ForumConfig.php");
require_once("../Classes/simplehtmldom/simple_html_dom.php");
require_once("Patch.php");

/**
 * Scrolling doesn't seem to always happen automatically so this is needed in order to 
 * force a scroll whenever an element is interacted with
 */
$objPatch = new Patch('vendor/instaclick/php-webdriver/lib/WebDriver/Session.php');
$objPatch->redefineFunction("
    public function moveto(\$parameters)
    {
        try {
            \$result = \$this->curl('POST', '/scrollto', \$parameters);
            \$result = \$this->curl('POST', '/moveto', \$parameters);
        } catch (WebDriverException\ScriptTimeout \$e) {
            throw WebDriverException::factory(WebDriverException::UNKNOWN_ERROR);
        }

        return \$result['value'];
    }");
eval($objPatch->getCode());

putenv("OPENSSL_CONF=/dev/null");

function getSeleniumPid(){
    exec('netstat -nlap 2>&1 /dev/null | grep ":::4444"', $output);
    $pid = @trim(explode("LISTEN", str_replace("/java", "", $output[0]))[1]);
    return $pid;
}

$pid = getSeleniumPid();
if($pid != ""){
    exec("kill $pid");
    sleep(1);
}
exec(sprintf("%s > %s 2>&1 &", 
             "PATH=bin/firefox/:bin/:\$PATH xvfb-run java -jar bin/selenium.jar", 
             "selenium.log"));

use Behat\Behat\Context\ClosuredContextInterface,
    Behat\Behat\Context\TranslatedContextInterface,
    Behat\Behat\Context\BehatContext,
    Behat\Behat\Exception\PendingException,
    Behat\Gherkin\Node\PyStringNode,
    Behat\Gherkin\Node\TableNode,
    PHPUnit\Framework\Assert;

//
// Require 3rd-party libraries here:
//
//require_once 'vendor/phpunit/phpunit/src/Framework/Assert/Functions.php';

/**
 * Features context.
 */
class FeatureContext extends Behat\MinkExtension\Context\MinkContext {
    
    /**
     * Initializes context.
     * Every scenario gets it's own context object.
     *
     * @param array $parameters context parameters (set them up through behat.yml)
     */
    public function __construct(){
        global $currentSession;
        $currentSession = $this;
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
     * @BeforeScenario
     */
    public function beforeScenario($event){
        global $currentSession;
        if(!$currentSession->getSession()->getDriver()->isStarted()){
            $currentSession->getSession()->getDriver()->start();
            $currentSession->getSession()->getDriver()->resizeWindow(1366, 1080,'current');
        }
        $currentSession->getSession()->getDriver()->reset();
        $this->currentScenario = $event->getScenario();
    }
    
    /**
     * @BeforeSuite
     */
    public static function prepare($event){
        global $currentSession;
        // Create test database
        system("php ../maintenance/seed.php &> /dev/null");
        $fp = fopen("../test.tmp", 'w');
        fwrite($fp, "This file should delete it's self once the test suite is done running.\nDo not delete this file until then.");
        fclose($fp);
        system("rm -fr output/assets/screenshots");
    }
    
    /**
     * @AfterSuite
     */
    public static function clean($event){
        global $currentSession;
        $currentSession->getSession()->stop();
        if(file_exists("/usr/lib/mailman/bin/list_members")){
            system("php ../maintenance/cleanAllLists.php &> /dev/null");
        }
        unlink("../test.tmp");
        $pid = getSeleniumPid();
        if($pid != ""){
            exec("kill $pid");
            sleep(1);
        }
    }
    
    /**
     * @BeforeStep
     */
    public static function beforeStep($event){
        global $currentSession;
        // Delay the session so that it doesn't process futher while the page is still loading
        try{
            $currentSession->getSession()->wait(5);
        }
        catch(Exception $e){
            
        }
    }
    
    /**
     * @AfterStep
     */
    public function afterStep($event){
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
    }

    /**
     * @Given /^I am logged in as "([^"]*)" using password "([^"]*)"$/
     */
    public function iAmLoggedInAsUsingPassword($username, $password){
        $this->visit('index.php');
        $this->fillField('wpName', $username);
        $this->fillField('wpPassword', $password);
        $this->pressButton('wpLoginAttempt');
    }
    
    /**
     * @Then /^I take a screenshot$/
     */
    public function iTakeAScreenshot(){
        // Do Nothing
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
        if(file_exists("/usr/lib/mailman/bin/list_members")){
            $command = "/usr/lib/mailman/bin/list_members $list";
            exec($command, $output);
            $found = false;
            foreach($output as $line){
                if($line == $email){
                    $found = true;
                }
            }
            Assert::assertTrue($found);
        }
    }
    
    /**
     * @Then /^"([^"]*)" should not be subscribed to "([^"]*)"$/
     */
    public function shouldNotBeSubscribedTo($email, $list){
        if(file_exists("/usr/lib/mailman/bin/list_members")){
            $command = "/usr/lib/mailman/bin/list_members $list";
            exec($command, $output);
            $found = false;
            foreach($output as $line){
                if($line == $email){
                    $found = true;
                }
            }
            Assert::assertFalse($found);
        }
    }
    
    /**
     * @Then /^unsubscribe "([^"]*)" from "([^"]*)"$/
     */
    public function unsubscribeFrom($email, $list){
        if(file_exists("/usr/lib/mailman/bin/remove_members")){
            $command =  "/usr/lib/mailman/bin/remove_members -n -N $list $email";
		    exec($command, $output);
		    Assert::assertTrue(count($output) == 0 || (count($output) > 0 && $output[0] == ""));
		}
    }
    
    /**
     * @Then /^The load time should be no greater than "([^"]*)"$/
     */
    public function theLoadTimeShouldBeNoGreaterThan($maxTime)
    {
        $time = $this->getSession()->evaluateScript('return window.performance.timing.domContentLoadedEventEnd- window.performance.timing.navigationStart;');
        Assert::assertFalse($time > $maxTime);
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
        Assert::assertTrue($strpos1 < $strpos2);
    }
    
    /**
     * @Then /^I should not see "(?P<text1>(?:[^"]|\\")*)" before "(?P<text2>(?:[^"]|\\")*)"$/
     */
    public function iShouldNotSeeBefore($string1, $string2){
        $text = str_replace("&#39;", "'", $this->getSession()->getPage()->getText());
        $strpos1 = strpos($text, stripslashes($string1));
        $strpos2 = strpos($text, stripslashes($string2));
        Assert::assertFalse($strpos1 < $strpos2);
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
        $script = "$('textarea[name=$id]').tinymce().setContent('$text'); " .
                  "_.defer(function(){ $('textarea[name=$id]').tinymce().fire('keyup'); });";
        $this->getSession()->getDriver()->executeScript($script);
    }
    
    /**
     * @Given /^I fill in TagIt "(?P<id>(?:[^"]|\\")*)" with "(?P<text>(?:[^"]|\\")*)"$/
     */
    public function fillInTagItWith($id, $text){
        $text = addslashes($text);
        $script = "$('[name=$id]').tagit('createTag', '$text');";
        $this->getSession()->getDriver()->executeScript($script);
    }
    
    /**
     * @Given /^I select from Chosen "(?P<id>(?:[^"]|\\")*)" with "(?P<text>(?:[^"]|\\")*)"$/
     */
    public function selectFromChosenWith($id, $text){
        $text = addslashes($text);
        $script = "chosenText = '$text'; " .
                  "$('select[name=$id] option').each(function(i, el){
                                                if($(el).val() == chosenText ||
                                                   $(el).text() == chosenText){
                                                    chosenText = $(el).val();
                                                }
                                            }); " .
                  "$('select[name=$id]').val(chosenText).trigger('chosen:updated').change();";
        $this->getSession()->getDriver()->executeScript($script);
    }
    
    /**
     * @Given /^I validate report xml$/
     */
    public function validateReportXML(){
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
        $script = '$("#'.$parent.' #'.$destination.'").append($("#'.$parent.' #'.$source.' li[data-id='.$id.']").detach()); ' .
                  '$("#'.$parent.' #'.$source.'")[0].Sortable.option("onSort")({target: $("#'.$parent.' #'.$source.'")}); ' .
                  '$("#'.$parent.' #'.$destination.'")[0].Sortable.option("onSort")({target: $("#'.$parent.' #'.$destination.'")});';
        $this->getSession()->getDriver()->executeScript($script);
    }
    
    /**
    * @When /^I log "([^"]*)" I should see "([^"]*)"$/
    */
    public function iLogIShouldSee($js, $text)
    {
        $value = $this->getSession()->evaluateScript('return '.$js);
        $json = json_encode($value);
        Assert::assertTrue((strpos($json, $text) !== false));
    }
    
    /**
    * @When /^I log "([^"]*)" I should not see "([^"]*)"$/
    */
    public function iLogIShouldNotSee($js, $text)
    {
        $value = $this->getSession()->evaluateScript('return '.$js);
        $json = json_encode($value);
        Assert::assertTrue((strpos($json, $text) === false));
    }
    
    /**
    * @When /^I blur$/
    */
    public function iBlur()
    {
        $this->getSession()->getDriver()->executeScript("$(':focus').blur();");
        $this->getSession()->wait(100);
    }
    
    static function listFiles($dir){
        global $config;
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
