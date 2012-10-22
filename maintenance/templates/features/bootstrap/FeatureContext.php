# features/bootstrap/FeatureContext.php
<?php
ini_set("memory_limit","1024M");
use Behat\Behat\Context\ClosuredContextInterface,
    Behat\Behat\Context\TranslatedContextInterface,
    Behat\Behat\Context\BehatContext,
    Behat\Behat\Exception\PendingException,
    Behat\Behat\Context\Step;
    
use Behat\Gherkin\Node\PyStringNode,
    Behat\Gherkin\Node\TableNode;
    
date_default_timezone_set('UTC');
    
require_once 'mink/autoload.php';

$currentSession = null;

class FeatureContext extends Behat\Mink\Behat\Context\MinkContext {

    function FeatureContext(){
        global $currentSession;
        $currentSession = $this;
    }
    
    /** @AfterScenario */
    public function after($event){
        exec("rm -f /tmp/upload*");
    }
    
    /**
     * @AfterSuite
     */
    public static function clean($event){
        exec("rm -f /tmp/upload*");
    }

    /**
     * @Given /^I am logged in as "([^"]*)" using password "([^"]*)"$/
     */
    public function iAmLoggedInAsUsingPassword($username, $password){
        $this->visit('/');
        $this->fillField('wpName', $username);
        $this->fillField('wpPassword', $password);
        $this->fillField('wpRemember', '1');
        $this->pressButton('Log in');
    }
    
    /**
     * @Given /^I wait "([^"]*)"$/
     */
    public function iWait($time){
        $this->getSession()->wait($time);
    }
    
    /**
     * @Then /^cookie "([^"]*)" is set$/
     */
    public function cookieIsSet($cookie){
        assertNotNull($this->getSession()->getCookie($cookie));
    }
    
    /**
     * @Then /^I should see image "([^"]*)"$/
     */
    public function iShouldSeeImage($altText){
        assertTrue($this->getSession()->getPage()->has("css", "img[alt=$altText]"));
    }
    
    /**
     * @Then /^I should not see image "([^"]*)"$/
     */
    public function iShouldNotSeeImage($altText){
        assertFalse($this->getSession()->getPage()->has("css", "img[alt=$altText]"));
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
     * @Given /^I fill in "([^"]*)" with multiline "([^"]*)"$/
     */
    public function iFillInWithMultiline($field, $text){
        $text = str_replace("\\n", "\n", $text);
        $this->fillField($field, $text);
    }

    /**
     * @Given /^I append "([^"]*)" to "([^"]*)"$/
     */
    public function iAppendTo($text, $field){
        $text = str_replace("\\n", "\n", $text);
        $text = str_replace("&amp;", "&", $text);
        $f = str_replace("\\n", "\n", $this->getSession()->getPage()->findField($field)->getValue());
        $f = str_replace("&amp;", "&", $f);
        $this->fillField($field, $f.$text);
    }

}
