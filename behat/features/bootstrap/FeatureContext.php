<?php

require_once("../Classes/simplehtmldom/simple_html_dom.php");
$pid = pcntl_fork();
if ($pid == -1) {
     die('could not fork');
} else if ($pid) {
    // we are the parent
    $output = exec("netstat -vatn | grep ':::4444'");
    while(!strstr($output, "4444")){
        // Not the best way to check if Selenium is running, 
        // but it is the best I could come up with
        sleep(1);
        $output = exec("netstat -vatn | grep ':::4444'");
    }
} else {
     // we are the child
     $output = exec("netstat -vatn | grep ':::4444'");
     if(!strstr($output, "4444")){
        pcntl_exec("bin/selenium");
     }
     else{
        exit();
     }
}

use Behat\Behat\Context\ClosuredContextInterface,
    Behat\Behat\Context\TranslatedContextInterface,
    Behat\Behat\Context\BehatContext,
    Behat\Behat\Exception\PendingException;
use Behat\Gherkin\Node\PyStringNode,
    Behat\Gherkin\Node\TableNode;

//
// Require 3rd-party libraries here:
//
   require_once 'PHPUnit/Autoload.php';
   require_once 'PHPUnit/Framework/Assert/Functions.php';
//

/**
 * Features context.
 */
class FeatureContext extends Behat\MinkExtension\Context\MinkContext {

    static $dbJSON = array();
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
    
    /**
     * @BeforeSuite
     */
    public static function prepare($event){
        // Create test database
        system("php ../maintenance/seed.php &> /dev/null");
        system("rm -f screenshots/*");
        $fp = fopen("../test.tmp", 'w');
        fwrite($fp, "This file should delete it's self once the test suite is done running.\nDo not delete this file until then.");
        fclose($fp);
    }
    
    /**
     * @AfterSuite
     */
    public static function clean($event){
        global $currentSession;
        $currentSession->getSession()->stop();
        //system("killall java");
        //system("killall Xvfb");
        unlink("../test.tmp");
    }
    
    /**
     * @BeforeStep
     */
    public static function beforeStep($event){
        global $currentSession;
        // Delay the session so that it doesn't process futher while the page is still loading
        try{
            $currentSession->getSession()->wait(10);
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
        $interval = 100;
        $timeElapsed = 0;
        while($timeElapsed < $ms && !$this->getSession()->getPage()->hasContent($text)){
            usleep($interval*1000);
            $timeElapsed += $interval;
        }
        $this->assertSession()->pageTextContains($text);
    }
    
    /**
     * @Given /^I wait until I no longer see "([^"]*)" up to "([^"]*)"$/
     */
    public function iWaitOrUntilINoLongerSee($text, $ms){
        $interval = 100;
        $timeElapsed = 0;
        while($timeElapsed < $ms && $this->getSession()->getPage()->hasContent($text)){
            usleep($interval*1000);
            $timeElapsed += $interval;
        }
        $this->assertSession()->pageTextNotContains($text);
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
     * @Given /^I visit profile for "([^"]*)"$/
     */
    public function visitProfile($name){
        $html = str_get_html($this->getSession()->getPage()->getContent());
        $a = $html->find('a.Bold', 0);
        $affiliation = $html->find('li.dataCol5', 0);
        if($affiliation != null){
            self::$dbJSON['authors'][$name]['affiliation'] = $affiliation->plaintext;
        }
        if($a != null){
            $this->getSession()->visit("http://www.scopus.com".$a->href);
        }
        $html->clear();
        unset($html);
    }
    
    /**
     * @Given /^I scrape subjects for "([^"]*)"$/
     */
    public function scrapeSubjects($name){
        $html = str_get_html($this->getSession()->getPage()->getContent());
        $sub = @$html->find(".tableMbWh-T", 18);
        if($sub != null){
            $row = $sub->plaintext;
            $row = str_replace("Less...", "", $row);
            $row = str_replace("More...", "", $row);
            $row = str_replace("<br>", "\n", $row);
            if(!isset(self::$dbJSON['authors'][$name]['subjects'])){
                self::$dbJSON['authors'][$name]['subjects'] = $row;
            }
            else{
                self::$dbJSON['authors'][$name]['subjects'] .= "<br />".$row;
            }
            $sub->clear();
            unset($sub);
        }
        self::$dbJSON['authors'][$name]['url'] = $this->getSession()->getCurrentUrl();
        file_put_contents("db.json", json_encode(self::$dbJSON));
        $html->clear();
        unset($html);
    }
    
    /**
     * @Given /^I scrape document years for "([^"]*)"$/
     */
    public function scrapeDocumentYears($name){
        $html = str_get_html($this->getSession()->getPage()->getContent());
        $pubs = @$html->find("#clusterAttribute_PUBYEAR", 0);
        if($pubs != null){
            $rows = $pubs->find("li");
            foreach($rows as $row){
                $year = str_replace(" ", "", $row->find("label", 0)->innertext);
                $amount = str_replace(")", "", str_replace("(", "", $row->find("span.floatL", 0)->innertext));
                @self::$dbJSON['authors'][$name]['nPubs'][$year] += $amount;
            }
            $pubs->clear();
            unset($pubs);
        }
        file_put_contents("db.json", json_encode(self::$dbJSON));
        $html->clear();
        unset($html);
    }
    
    /**
     * @Given /^I scrape citation years for "([^"]*)"$/
     */
    public function scrapeCitationYears($name){
        $html = str_get_html($this->getSession()->getPage()->getContent());
        $years = array();
        $years[2007] = $html->find("#year_2007 a", 0);
        $years[2008] = $html->find("#year_2008 a", 0);
        $years[2009] = $html->find("#year_2009 a", 0);
        $years[2010] = $html->find("#year_2010 a", 0);
        $years[2011] = $html->find("#year_2011 a", 0);
        $years[2012] = $html->find("#year_2012 a", 0);
        $years[2013] = $html->find("#year_2013 a", 0);
        foreach($years as $year => $amount){
            if($amount != null){
                @self::$dbJSON['authors'][$name]['nCits'][$year] += $amount->innertext;
            }
        }
        file_put_contents("db.json", json_encode(self::$dbJSON));
        $html->clear();
        unset($html);
    }
}
