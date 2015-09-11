<?php
define('TESTING', true);
require_once("../config/Config.php");
require_once("../Classes/simplehtmldom/simple_html_dom.php");

exec(sprintf("%s > %s 2>&1 & echo $! >> %s", 
             "phantomjs --webdriver=8643", 
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
        $interval = 500*1000;
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
        system("killall phantomjs");
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
        try{
            $currentSession->iTakeAScreenshot();
        }
        catch(Exception $e){
            
        }
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
        $command =  "/usr/lib/mailman/bin/remove_members -n -N $list $email";
		exec($command, $output);
		assertTrue(count($output) == 0 || (count($output) > 0 && $output[0] == ""));
    }
    
    /**
     * @Given /^I wait "([^"]*)"$/
     */
    public function iWait($ms){
        $this->getSession()->wait($ms);
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
    }

}
