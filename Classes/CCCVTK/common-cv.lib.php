<?php
/*
    CCCVTK, the Canadian Common CV Toolkit
    Copyright (C) 2013-2014 Sylvain Hallé

    This program is free software: you can redistribute it and/or modify
    it under the terms of the GNU General Public License as published by
    the Free Software Foundation, either version 3 of the License, or
    (at your option) any later version.

    This program is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.

    You should have received a copy of the GNU General Public License
    along with this program.  If not, see <http://www.gnu.org/licenses/>.
*/

// We load the (huge) list of constants associated to the CV
require_once("constants.lib.php");

/**
 * Basic class to handle data from the Canadian Common CV (CCV). Basically,
 * this class loads the XML data from an instance of CCV, parses and
 * processes it, and offers information from the most common sections
 * (publications, funding, students supervised, etc.) as a set of
 * associative arrays that can be easily queried by a PHP script. Any
 * user-defined script can then use these arrays to build a document with
 * an arbitrary format (e.g. LaTeX, HTML, plain-text, etc.).
 * 
 * This script works in conjunction with a large array containing all the
 * predefined constants used in the documentation of the CCV.
 */
class CommonCV // {{{
{
  // The DOM object holding the CV's contents
  private $m_dom;
  
  // An object to perform XPath queries on the CV's content. It is made
  // public so that one can query the CV in custom ways if needed
  public $m_xpath;
  
  public static $constants;
  
  /**
   * Default constructor. Reads a CV from a given filename
   * @param filename The filename to read from
   */
  public function CommonCV($filename) // {{{
  {
    global $CCV_CONST;
    $this->constants = $CCV_CONST;
    $this->loadFromFile($filename);
  } // }}}
  
  /**
   * Loads a CV from an XML file. This wipes any CV that was already
   * loaded into the object.
   * @param filename The filename to read from
   */
  private function loadFromFile($filename) // {{{
  {
    $file_contents = file_get_contents($filename);
    $this->m_dom = new DOMDocument();
    $this->m_dom->formatOutput = true;
    $this->m_dom->loadXML($file_contents);
    $this->m_xpath = new DOMXpath($this->m_dom);
  } // }}}
  
  /**
   * Parses the list of conference papers and returns (some of its) data
   * as an associative array for convenience
   */
  public function getConferencePapers() // {{{
  {
    global $CCV_CONST;
    $records = array();
    $elements = $this->m_xpath->query("//section[@id='4b9f909503cd4c8aa8d826c87d6d874d']");
    for ($i = 0; !is_null($elements) && $i < $elements->length; $i++)
    {
      $record = array();
      $id = $this->get_xpath("@recordId", $elements->item($i));
      $record["title"] = $this->get_xpath("field[@id='8e6ee535c95e42ec866b777c7472bafb']/value", $elements->item($i));
      $record["publisher"] = $this->get_xpath("field[@id='0c357193a93f4137a87394401ac81958']/value", $elements->item($i));
      $record["authors"] = $this->get_xpath("field[@id='3cc54d9bb92d421da46548979048396f']/value", $elements->item($i));
      $record["editors"] = $this->get_xpath("field[@id='018e656a0f824b1f91a6a2cb33ac61dd']/value", $elements->item($i));
      $record["pages"] = $this->get_xpath("field[@id='684ccb1fcdd7421f89b304ff5c40579d']/value", $elements->item($i));
      $record["url"] = $this->get_xpath("field[@id='61690b466fb748d99ed29b340c0ee60b']/value", $elements->item($i));
      $record["status"] = $this->get_xpath("field[@id='080301b1f1c0464bba7fcfa1fa8fe182']/lov/@id", $elements->item($i));
      $pr = $this->get_xpath("field[@id='560a2ce08e14497ba575af760eb12ba9']/lov/@id", $elements->item($i));
      if ($pr === $CCV_CONST["Yes-No"]["Yes"])
        $record["peer_reviewed"] = true;
      elseif ($pr === $CCV_CONST["Yes-No"]["No"])
        $record["peer_reviewed"] = false;
      $record["conf_name"] = $this->get_xpath("field[@id='b3c8a60c053a405597b92899d95765a3']/value", $elements->item($i));
      $record["published_in"] = $this->get_xpath("field[@id='1a1b39e861054ee59d270e66271a4ead']/value", $elements->item($i));
      $record["city"] = $this->get_xpath("field[@id='c2efd9725588489b8df73467c5597c32']/value", $elements->item($i));
      $date = $this->get_xpath("field[@id='0318d139f3e0479083188ff8319a97b2']/value", $elements->item($i));
      @list($date_year, $date_month) = explode("/", $date);
      $record["date_year"] = $date_year;
      $record["date_month"] = $date_month;
      if($date_year == "" || $date_month == ""){
          // Check Conference Date field instead
          $confDate = $this->get_xpath("field[@id='99b57db653a841ccbd5f8e52079745c0']/value", $elements->item($i));
          @list($date_year, $date_month) = explode("/", $confDate);
          if($date_year != ""){
            $record["date_year"] = $date_year;
          }
          if($date_month != ""){
            $record["date_month"] = str_pad($date_month, 2, "0", STR_PAD_LEFT);
          }
      }
      $records[$id] = $record;
    }
    return $records;
  } // }}}
  
  /**
   * Parses the list of journal papers and returns (some of its) data
   * as an associative array for convenience
   */
  public function getJournalPapers() // {{{
  {
    global $CCV_CONST;
    $records = array();
    $elements = $this->m_xpath->query("//section[@id='9a34d6b273914f18b2273e8de7c48fd6']");
    for ($i = 0; !is_null($elements) && $i < $elements->length; $i++)
    {
      $id = $this->get_xpath("@recordId", $elements->item($i));
      $record = array();
      $record["title"] = $this->get_xpath("field[@id='f3fd4878d47c4e83aef6959620ba4870']/value", $elements->item($i));
      $record["authors"] = $this->get_xpath("field[@id='bc3b428d99384b04bb749311bb804e1d']/value", $elements->item($i));
      $record["editors"] = $this->get_xpath("field[@id='707a6e0ca58341a5a82fb923b2842530']/value", $elements->item($i));
      $record["pages"] = $this->get_xpath("field[@id='00ba1799ece344dc8d0779a3f05a4df8']/value", $elements->item($i));
      $record["url"] = $this->get_xpath("field[@id='478545acac5340c0a73b7e0d2a4bee06']/value", $elements->item($i));
      $record["publisher"] = $this->get_xpath("field[@id='4ad593960aba4a21bf154fa8daf37f9f']/value", $elements->item($i));
      $record["volume"] = $this->get_xpath("field[@id='0a826c656ff34e579dfcbfb373771260']/value", $elements->item($i));
      $record["number"] = $this->get_xpath("field[@id='cc1d9e14945b4e8496641dbe22b3448a']/value", $elements->item($i));
      $record["status"] = $this->get_xpath("field[@id='3b56e4362d6a495aa5d22a1de5914741']/lov/@id", $elements->item($i));
      $pr = $this->get_xpath("field[@id='2089ff1a86844b6c9a10fc63469f9a9d']/lov/@id", $elements->item($i));
      if ($pr === $CCV_CONST["Yes-No"]["Yes"])
        $record["peer_reviewed"] = true;
      elseif ($pr === $CCV_CONST["Yes-No"]["No"])
        $record["peer_reviewed"] = false;
      $record["journal"] = $this->get_xpath("field[@id='5c04ea4dae464499807d0b40b4cad049']/value", $elements->item($i));
      $date = $this->get_xpath("field[@id='6fafe258e19e49a7884428cb49d75424']/value", $elements->item($i));
      @list($date_year, $date_month) = explode("/", $date);
      $record["date_year"] = $date_year;
      $record["date_month"] = ($date_month != "") ? str_pad($date_month, 2, "0", STR_PAD_LEFT) : "01";
      $records[$id] = $record;
    }
    return $records;
  } // }}}

  /**
   * Parses the list of book chapters and returns (some of its) data
   * as an associative array for convenience
   */
  public function getBookChapters() // {{{
  {
    global $CCV_CONST;
    $records = array();
    $elements = $this->m_xpath->query("//section[@id='fd8f2ffe3f5c43db8b5c3f72d8ffd994']");
    for ($i = 0; !is_null($elements) && $i < $elements->length; $i++)
    {
      $id = $this->get_xpath("@recordId", $elements->item($i));
      $record = array();
      $record["booktitle"] = $this->get_xpath("field[@id='b864a9512e89482487f83ed22c454e9d']/value", $elements->item($i));
      $record["title"] = $this->get_xpath("field[@id='52a699a6b0fe42a4851a7d5ae355360b']/value", $elements->item($i));
      $record["authors"] = $this->get_xpath("field[@id='5bc7b0b361f843d296e0e035b4b87176']/value", $elements->item($i));
      $record["editors"] = $this->get_xpath("field[@id='dcb27492e1554fc9838992ba7c70f416']/value", $elements->item($i));
      $record["pages"] = $this->get_xpath("field[@id='45b9d02a4bb04ec782357741b53dc135']/value", $elements->item($i));
      $record["url"] = $this->get_xpath("field[@id='6f1c66fc402d4b0db3d9987e2c5d49e8']/value", $elements->item($i));
      $record["publisher"] = $this->get_xpath("field[@id='51a088349c1442238d5d0331d95f3205']/value", $elements->item($i));
      $record["status"] = $this->get_xpath("field[@id='cf36bbd2e16c45cba9768a84ac2d6729']/lov/@id", $elements->item($i));
      $pr = $this->get_xpath("field[@id='51bd72e89f85442a9ae199e75fe5e765']/lov/@id", $elements->item($i));
      if ($pr === $CCV_CONST["Yes-No"]["Yes"])
        $record["peer_reviewed"] = true;
      elseif ($pr === $CCV_CONST["Yes-No"]["No"])
        $record["peer_reviewed"] = false;
      $record["journal"] = $this->get_xpath("field[@id='5c04ea4dae464499807d0b40b4cad049']/value", $elements->item($i));
      $date = $this->get_xpath("field[@id='c114eabcd4674f3c9467b2bf6820cbd6']/value", $elements->item($i));
      @list($date_year, $date_month) = explode("/", $date);
      $record["date_year"] = $date_year;
      $record["date_month"] = ($date_month != "") ? str_pad($date_month, 2, "0", STR_PAD_LEFT) : "01";
      $records[$id] = $record;
    }
    return $records;
  } // }}}

  /**
   * Parses the list of supervised students and returns (some of its) data
   * as an associative array for convenience
   */
  public function getStudentsSupervised() // {{{
  {
    global $CCV_CONST;
    $records = array();
    $elements = $this->m_xpath->query("//section[@id='4b36fa1eef2549f6ab3a3df7c1c81e0b']");
    for ($i = 0; !is_null($elements) && $i < $elements->length; $i++)
    {
      $record = array();
      $id = $this->get_xpath("@recordId", $elements->item($i));
      // Supervision Dates
      $date = $this->get_xpath("field[@id='19964df0a8524f2bb44d5eb53729f9cc']/value", $elements->item($i));
      @list($record["start_year"], $record["start_month"]) = explode("/", $date);
      $date = $this->get_xpath("field[@id='bd3619f7970441dc83ada1d2fdbf0780']/value", $elements->item($i));
      @list($record["end_year"], $record["end_month"]) = explode("/", $date);
      // Degree Dates
      $date = $this->get_xpath("field[@id='3cf3d0de12f44222b941fdbf57ad51a6']/value", $elements->item($i));
      @list($record["degree_start_year"], $record["degree_start_month"]) = explode("/", $date);
      $date = $this->get_xpath("field[@id='ab1293e2fee8472481457d4f8493c7f1']/value", $elements->item($i));
      @list($record["degree_expected_year"], $record["degree_expected_month"]) = explode("/", $date);
      $date = $this->get_xpath("field[@id='8284dbdd03aa4277b7fca7662bd1758c']/value", $elements->item($i));
      @list($record["degree_end_year"], $record["degree_end_month"]) = explode("/", $date);
      $record["name"] = $this->get_xpath("field[@id='3c504aafda28418ea439d8f92c28aef0']/value", $elements->item($i));
      $record["institution"] = $this->get_xpath("field[@id='e36ccf9a00a241dc942e608df32c8c84']/value", $elements->item($i));
      $record["diploma"] = $this->get_xpath("field[@id='5b8638e8646448dcb8edef2c21e01c87']/lov/@id", $elements->item($i));
      $record["status"] = $this->get_xpath("field[@id='e5d331dca0fc4000992e43b695b2db21']/lov/@id", $elements->item($i));
      $record["title"] = $this->get_xpath("field[@id='420e5bbd57104c3c9823b5e6850ee6f8']/value", $elements->item($i));
      $records[$id] = $record;
    }
    return $records;
  } // }}}
  
  /**
   * Parses the list of funding sources and returns (some of its) data
   * as an associative array for convenience
   */
  public function getFunding() // {{{
  {
    global $CCV_CONST;
    $records = array();
    $elements = $this->m_xpath->query("//section[@id='aaedc5454412483d9131f7619d10279e']");
    for ($i = 0; !is_null($elements) && $i < $elements->length; $i++)
    {
      $record = array();
      $id = $this->get_xpath("@recordId", $elements->item($i));
      $date = $this->get_xpath("field[@id='9c1db4674334436ca891b7b8a9e114bd']/value", $elements->item($i));
      list($record["start_year"], $record["start_month"]) = explode("/", $date);
      $date = $this->get_xpath("field[@id='b63179ab0f0e4c9eaa7e9a8130d60ee3']/value", $elements->item($i));
      @list($record["end_year"], $record["end_month"]) = explode("/", $date);
      $record["funding_title"] = $this->get_xpath("field[@id='735545eb499e4cc6a949b4b375a804e8']/value", $elements->item($i));
      $record["funding_type"] = $this->get_xpath("field[@id='931b92a5ffed4e5aa9c7b3a0afd5f8ba']/lov/@id", $elements->item($i));
      $record["funding_program"] = $this->get_xpath("section[@id='376b8991609f46059a3d66028f005360']/field[@id='97231512141a452a82151cc162e9a59c']/value", $elements->item($i));
      $record["funder"] = $this->get_xpath("section[@id='376b8991609f46059a3d66028f005360']/field[@id='67e083b070954e91bcbb1cc70131145a']/lov/@id", $elements->item($i));
      $record["otherfunder"] = $this->get_xpath("section[@id='376b8991609f46059a3d66028f005360']/field[@id='1bdead14642545f3971a59997d82da67']/value", $elements->item($i));
      $record["total_amount"] = $this->get_xpath("section[@id='376b8991609f46059a3d66028f005360']/field[@id='dfe6a0b34347486aaa677f07306a141e']/value", $elements->item($i));
      $record["received_amount"] = $this->get_xpath("section[@id='376b8991609f46059a3d66028f005360']/field[@id='882a94c7548744ca992e2647346d2e14']/value", $elements->item($i));
      $record["role"] = $this->get_xpath("field[@id='13806a6772d248158619261afaab2fe0']/lov/@id", $elements->item($i));
      $co_holders = array();
      $co_els = $this->m_xpath->query("section[@id='c7c473d1237b432fb7f2abd831130fb7']", $elements->item($i));
      for ($j = 0; !is_null($co_els) && $j < $co_els->length; $j++)
      {
        $co_holder = array();
        $ch_id = $this->get_xpath("@recordId", $co_els->item($j));
        $co_holder["name"] = $this->get_xpath("field[@id='ddd551dfb26344fbb17f07afcffc94ed']/value", $co_els->item($j));
        $co_holders[$ch_id] = $co_holder;
      }
      $record["co_holders"] = $co_holders;
      $comp = $this->get_xpath("section[@id='376b8991609f46059a3d66028f005360']/field[@id='00efdc7e790a48ac8675696c66afc3ad']/lov/@id", $elements->item($i));
      if ($comp === $CCV_CONST["Yes-No"]["Yes"])
        $record["competitive"] = true;
      elseif ($comp === $CCV_CONST["Yes-No"]["No"])
        $record["competitive"] = false;
      $records[$id] = $record;
    }
    return $records;
  } // }}}
  
  /**
   * Parses the list of personal info and returns (some of its) data
   * as an associative array for convenience
   */
  public function getPersonalInfo() // {{{
  {
    global $CCV_CONST;
    $records = array();
    $elements = $this->m_xpath->query("//section[@id='2687e70e5d45487c93a8a02626543f64']");
    if ($elements->length > 0)
    {
      $i = 0;
      $records["greeting"] = $this->get_xpath("field[@id='ee8beaea41f049d8bcfadfbfa89ac09e']/lov/@id", $elements->item($i));
      $records["last_name"] = $this->get_xpath("field[@id='5c6f17e8a67241e19667815a9e95d9d0']/value", $elements->item($i));
      $records["first_name"] = $this->get_xpath("field[@id='98ad36fee26a4d6b8953ea764f4fed04']/value", $elements->item($i));
      $records["middle_name"] = $this->get_xpath("field[@id='4ca83c1aaa6a42a78eac0290368e70f3']/value", $elements->item($i));
      $records["prev_first_name"] = $this->get_xpath("field[@id='0fb359a7d809457d9392bb1ca577f1b3']/value", $elements->item($i));
      $records["prev_last_name"] = $this->get_xpath("field[@id='84e9fa08f7334db79ed5310e5f7a961b']/value", $elements->item($i));
      $records["sex"] = $this->get_xpath("field[@id='3d258d8ceb174d3eb2ae1258a780d91b']/lov/@id", $elements->item($i));
      $records["correspondence_language"] = $this->get_xpath("field[@id='2b72a344523c467da0c896656b5290c0']/lov/@id", $elements->item($i));
      // Get Address Info
      $co_holders = array();
      $co_els = $this->m_xpath->query("//section[@id='b92721f0510a4ef4b0d1cf7f5ea3f01e']");
      for ($j = 0; !is_null($co_els) && $j < $co_els->length; $j++)
      {
        $co_holder = array();
        $ch_id = $this->get_xpath("@recordId", $co_els->item($j));
        $co_holder["primary_indicator"] = ($this->get_xpath("@primaryIndicator", $co_els->item($j)) == "true");
        $co_holder["type"] = $this->get_xpath("field[@id='35c302c36fe9479287206171087fb185']/lov/@id", $co_els->item($j));
        $co_holder["line1"] = $this->get_xpath("field[@id='2de0fe4994f546c695a060d68e8e03ca']/value", $co_els->item($j));
        $co_holder["line2"] = $this->get_xpath("field[@id='dafdb980e181416abc5e26c0770df662']/value", $co_els->item($j));
        $co_holder["line3"] = $this->get_xpath("field[@id='fc390eae1fbc45c89789f2ecbb5bed8e']/value", $co_els->item($j));
        $co_holder["line4"] = $this->get_xpath("field[@id='d51e2de9122744489ac2231d85995617']/value", $co_els->item($j));
        $co_holder["line5"] = $this->get_xpath("field[@id='5365d87b9ff145d3a8d0d4fc21af57bb']/value", $co_els->item($j));
        $co_holder["city"] = $this->get_xpath("field[@id='499d69637b4148d0a49463a2881e9d09']/value", $co_els->item($j));
        $co_holder["postal_code"] = $this->get_xpath("field[@id='a41f1e118e61482eb3cdde4aaeb783e8']/value", $co_els->item($j));
        $co_holder["location_country"] = $this->get_xpath("field[@id='b1071063df03484ebec65cd1a3464438']/refTable/linkedWith[@label='Country']/@value", $co_els->item($j));
        $co_holder["location_subdivision"] = $this->get_xpath("field[@id='b1071063df03484ebec65cd1a3464438']/refTable/linkedWith[@label='Subdivision']/@value", $co_els->item($j));
        $date = $this->get_xpath("field[@id='b77ff4a2c49247e0af668be52704da91']/value", $co_els->item($j));
        @list($co_holder["start_year"], $co_holder["start_month"], $co_holder["start_day"]) = explode("-", $date);
        $date = $this->get_xpath("field[@id='4ab2497d7a0f471ebc6a50e32dd4f22d']/value", $co_els->item($j));
        @list($co_holder["end_year"], $co_holder["end_month"], $co_holder["end_day"]) = explode("-", $date);
        $co_holder["start_year"] = ($co_holder["start_year"] == null) ? "" : $co_holder["start_year"];
        $co_holder["start_month"] = ($co_holder["start_month"] == null) ? "" : $co_holder["start_month"];
        $co_holder["start_day"] = ($co_holder["start_day"] == null) ? "" : $co_holder["start_day"];
        $co_holder["end_year"] = ($co_holder["end_year"] == null) ? "" : $co_holder["end_year"];
        $co_holder["end_month"] = ($co_holder["end_month"] == null) ? "" : $co_holder["end_month"];
        $co_holder["end_day"] = ($co_holder["end_day"] == null) ? "" : $co_holder["end_day"];
        $co_holders[$ch_id] = $co_holder;
      }
      $records["addresses"] = $co_holders;
      // Get Language Skills Info
      $languages = array();
      $lang_els = $this->m_xpath->query("//section[@id='c1f614961342429c86397e81cd6f50f5']");
      for ($j = 0; !is_null($lang_els) && $j < $lang_els->length; $j++)
      {
        $language = array();
        $l_id = $this->get_xpath("@recordId", $lang_els->item($j));
        $language["language"] = $this->get_xpath("field[@id='ee161805b4f941e48f05e050e364e585']/lov/@id", $lang_els->item($j));
        $read = $this->get_xpath("field[@id='a9d0f0666e5b47dcb9acb30bd5cab407']/lov/@id", $lang_els->item($j));
        $write = $this->get_xpath("field[@id='12173f36422446479799578ba07d96c8']/lov/@id", $lang_els->item($j));
        $speak = $this->get_xpath("field[@id='e670ac0f2c3e48a3b13d487e66ea7889']/lov/@id", $lang_els->item($j));
        $understand = $this->get_xpath("field[@id='aa02c54f1e5b4672a0b96def14e5b02e']/lov/@id", $lang_els->item($j));
        $peer_review = $this->get_xpath("field[@id='fc6ac63e9ec04129aec7b26e5a729920']/lov/@id", $lang_els->item($j));
        $language["read"] = ($read == $CCV_CONST["Yes-No"]["Yes"]) ? true : false;
        $language["write"] = ($write == $CCV_CONST["Yes-No"]["Yes"]) ? true : false;
        $language["speak"] = ($speak == $CCV_CONST["Yes-No"]["Yes"]) ? true : false;
        $language["understand"] = ($understand == $CCV_CONST["Yes-No"]["Yes"]) ? true : false;
        $language["peer_review"] = ($peer_review == $CCV_CONST["Yes-No"]["Yes"]) ? true : false;
        $languages[$l_id] = $language;
      }
      $records["languages"] = $languages;
      // Get Telephone Info
      $telephone = array();
      $phone_els = $this->m_xpath->query("//section[@id='2a3366209cf8477f82556b045188f131']");
      for ($j = 0; !is_null($phone_els) && $j < $phone_els->length; $j++)
      {
        $phone = array();
        $t_id = $this->get_xpath("@recordId", $phone_els->item($j));
        $phone["primary_indicator"] = ($this->get_xpath("@primaryIndicator", $phone_els->item($j)) == "true");
        $phone["type"] = $this->get_xpath("field[@id='ccef121ae875427f829024aabb39fa8c']/lov/@id", $phone_els->item($j));
        $phone["country_code"] = $this->get_xpath("field[@id='63dedd46a5204cda8257227bbb3b6675']/value", $phone_els->item($j));
        $phone["area_code"] = $this->get_xpath("field[@id='13cdf3a5e13643f5bc74566bf075253c']/value", $phone_els->item($j));
        $phone["number"] = $this->get_xpath("field[@id='1ca756fe70964371a2b9f57bdf567a5d']/value", $phone_els->item($j));
        $phone["extension"] = $this->get_xpath("field[@id='afe0657785084098bb718345280eb840']/value", $phone_els->item($j));
        $date = $this->get_xpath("field[@id='69c67fae5d4849d08f4f9799ae0a2335']/value", $phone_els->item($j));
        @list($phone["start_year"], $phone["start_month"], $phone["start_day"]) = explode("-", $date);
        $date = $this->get_xpath("field[@id='a90e95e1d278467eaf1847464f09f39f']/value", $phone_els->item($j));
        @list($phone["end_year"], $phone["end_month"], $phone["end_day"]) = explode("-", $date);
        $phone["start_year"] = ($phone["start_year"] == null) ? "" : $phone["start_year"];
        $phone["start_month"] = ($phone["start_month"] == null) ? "" : $phone["start_month"];
        $phone["start_day"] = ($phone["start_day"] == null) ? "" : $phone["start_day"];
        $phone["end_year"] = ($phone["end_year"] == null) ? "" : $phone["end_year"];
        $phone["end_month"] = ($phone["end_month"] == null) ? "" : $phone["end_month"];
        $phone["end_day"] = ($phone["end_day"] == null) ? "" : $phone["end_day"];

        $telephone[$t_id] = $phone;
      }
      $records["telephone"] = $telephone;
    }
    
    return $records;
  } // }}}
  
  /**
   * Parses the list of employment and returns (some of its) data
   * as an associative array for convenience
   */
  public function getEmployment() // {{{
   {
    global $CCV_CONST;
    $records = array();
    $elements = $this->m_xpath->query("//section[@id='b857f61b33484cb093068bd2da764f99']");
    for ($i = 0; !is_null($elements) && $i < $elements->length; $i++)
    {
      $record = array();
      $id = $this->get_xpath("@recordId", $elements->item($i));
      $record['type'] = $this->get_xpath("field[@id='9510a03a308f43ceb8cd046aeffa9499']/lov/@id", $elements->item($i));
      $record['title'] = $this->get_xpath("field[@id='886807b87b624978bc8ca9045ff56e47']/value", $elements->item($i));
      $record['status'] = $this->get_xpath("field[@id='ef7bd6fa8dd040449fa181f0ca4530e4']/lov/@id", $elements->item($i));
      $record['rank'] = $this->get_xpath("field[@id='500e1360abd14972bc1ef844a8b98087']/lov/@id", $elements->item($i));
      $date = $this->get_xpath("field[@id='c7e85d10d10249c68b28c71fc80ec570']/value", $elements->item($i));
      @list($record["start_year"], $record["start_month"]) = explode("/", $date);
      $date = $this->get_xpath("field[@id='b4681f52d85440829faa3160ba3bb31f']/value", $elements->item($i));
      @list($record["end_year"], $record["end_month"]) = explode("/", $date);
      $record["end_year"] = ($record["end_year"] == null) ? "" : $record["end_year"];
      $record["end_month"] = ($record["end_month"] == null) ? "" : $record["end_month"];
      
      $record['organization_country'] = $this->get_xpath("field[@id='5dd34f14c1ea47c09e8ddcd202653814']/refTable/linkedWith[@label='Country']/@value", $elements->item($i));
      $record['organization_subdivision'] = $this->get_xpath("field[@id='5dd34f14c1ea47c09e8ddcd202653814']/refTable/linkedWith[@label='Subdivision']/@value", $elements->item($i));
      $record['organization_type'] = $this->get_xpath("field[@id='5dd34f14c1ea47c09e8ddcd202653814']/refTable/linkedWith[@label='Organization Type']/@value", $elements->item($i));
      $record['organization_name'] = $this->get_xpath("field[@id='5dd34f14c1ea47c09e8ddcd202653814']/refTable/linkedWith[@label='Organization']/@value", $elements->item($i));
      $record['department'] = $this->get_xpath("field[@id='cd6e5e97994e42f893bd5c9e7212c94b']/value", $elements->item($i));
      $record['faculty'] = $this->get_xpath("field[@id='58cf7001d283421b91f5da21f0ef2188']/value", $elements->item($i));
      $record['tenure'] = $this->get_xpath("field[@id='b0eca39ad77346648180dec948d13432']/lov/@id", $elements->item($i));
      $records[$id] = $record;
    }
    return $records;
   } // }}}
  
  /**
   * Parses the list of courses taught info and returns (some of its) data
   * as an associative array for convenience
   */
  public function getCoursesTaught() // {{{
   {
    global $CCV_CONST;
    $records = array();
    $elements = $this->m_xpath->query("//section[@id='9dc74140d0ff4b26a2d4a559bc9b5a2b']");
    for ($i = 0; !is_null($elements) && $i < $elements->length; $i++)
    {
      $record = array();
      $id = $this->get_xpath("@recordId", $elements->item($i));
      $record["role"] = $this->get_xpath("field[@id='cefdb78ecd9e43fb8554d21e7d454132']/value", $elements->item($i));
      $record["department"] = $this->get_xpath("field[@id='b532bb9be90e4a93a879f23e79cfd652']/value", $elements->item($i));
      $record["semester"] = $this->get_xpath("field[@id='bab7abad3efb404897984bee6ed33692']/value", $elements->item($i));
      $record["code"] = $this->get_xpath("field[@id='d62dd205bef0463a8a23436c75f83f41']/value", $elements->item($i));
      $record["title"] = $this->get_xpath("field[@id='95728285a48242a896b5727c98a7c0c5']/value", $elements->item($i));
      $record["level"] = $this->get_xpath("field[@id='6db7aff8daf74420abcca56f8e6d6cc3']/lov/@id", $elements->item($i));
      $date = $this->get_xpath("field[@id='76f08a7bda38475bb15660d4fc57745f']/value", $elements->item($i));
      @list($record["start_date_year"], $record["start_date_month"], $record["start_date_day"]) = explode("-", $date);
      $date = $this->get_xpath("field[@id='c87587a998c04d3bbb23e853516d2f94']/value", $elements->item($i));
      @list($record["end_date_year"], $record["end_date_month"], $record["end_date_day"]) = explode("-", $date);
      $record["nb_students"] = $this->get_xpath("field[@id='c2b79aa4e9e0431db8f3c4e2c32db3b0']/value", $elements->item($i));
      $record["nb_credits"] = $this->get_xpath("field[@id='c2b79aa4e9e0431db8f3c4e2c32db3b0']/value", $elements->item($i));
      $record["nb_credits"] = $this->get_xpath("section[@id='05b1c7c941194144b786690d89ba7c8c']/field[@id='97231512141a452a82151cc162e9a59c']/value", $elements->item($i));
      $records[$id] = $record;
    }
    return $records;
   } // }}}

  /**
   * Parses the list of reviewed journal papers and returns (some of its)
   * data as an associative array for convenience
   */
  public function getReviewedJournalPapers() // {{{
  {
    global $CCV_CONST;
    $records = array();
    $elements = $this->m_xpath->query("//section[@id='3f4e029fb3a141958126bcbc9d086acd']");
    for ($i = 0; !is_null($elements) && $i < $elements->length; $i++)
    {
      $record = array();
      $id = $this->get_xpath("@recordId", $elements->item($i));
      $record["journal"] = $this->get_xpath("field[@id='e206eee3330a41c6ada624c29265fde7']/value", $elements->item($i));
      $record["publisher"] = $this->get_xpath("field[@id='9b9aaa304dcf4bd78e20c211ba3b0bee']/value", $elements->item($i));
      $record["numpapers"] = $this->get_xpath("field[@id='0c8f14842ab04c8daa4749b864f46a6c']/value", $elements->item($i));
      $record["blindtype"] = $this->get_xpath("field[@id='59990065210f4bc084270ef862602cc4']/lov/@id", $elements->item($i));
      $records[$id] = $record;
    }
    return $records;
  } // }}}
  
  /**
   * Parses the list of reviewed conference papers and returns (some of its)
   * data as an associative array for convenience
   */
  public function getReviewedConferencePapers() // {{{
  {
    global $CCV_CONST;
    $records = array();
    $elements = $this->m_xpath->query("//section[@id='7cc778c33e64469987c55e2078be60d3']");
    for ($i = 0; !is_null($elements) && $i < $elements->length; $i++)
    {
      $record = array();
      $id = $this->get_xpath("@recordId", $elements->item($i));
      $record["conference"] = $this->get_xpath("field[@id='9cdaafaa865246bc9637444cb90ae9a6']/value", $elements->item($i));
      $record["numpapers"] = $this->get_xpath("field[@id='7189023217b44dba9d4ac5c76ffe6c10']/value", $elements->item($i));
      $record["blindtype"] = $this->get_xpath("field[@id='b5f58c56b4544a969ac6013d1d7de0fa']/lov/@id", $elements->item($i));
      $records[$id] = $record;
    }
    return $records;
  } // }}}
  
  /**
   * Parses the list of committees and returns (some of its)
   * data as an associative array for convenience
   */
  public function getCommittees() // {{{
  {
    global $CCV_CONST;
    $records = array();
    $elements = $this->m_xpath->query("//section[@id='6c3b449732a84ac9af45d6935b8323d9']");
    for ($i = 0; !is_null($elements) && $i < $elements->length; $i++)
    {
      $record = array();
      $id = $this->get_xpath("@recordId", $elements->item($i));
      $record["organization_name"] = $this->get_xpath("field[@id='e1f84da9b6df40fe9db972af2f85e5eb']/refTable/linkedWith[@label='Organisation']/@value", $elements->item($i));
      $record["organization_country"] = $this->get_xpath("field[@id='e1f84da9b6df40fe9db972af2f85e5eb']/refTable/linkedWith[@label='Pays']/@value", $elements->item($i));
      $record["name"] = $this->get_xpath("field[@id='b3c21a20aeee49cea5c1c33029da4c4c']/value", $elements->item($i));
      $role = $this->get_xpath("field[@id='da89e8800c6641be91b0b21a61118c09']/lov/@id", $elements->item($i));
      $record["role"] = $this->getCaptionFromValue($role);
      $start_date = $this->get_xpath("field[@id='4c1a9700923d4bf9b541e479f4f32a66']/value", $elements->item($i));
      @list($record["start_year"], $record["start_month"]) = explode("/", $start_date);
      $end_date = $this->get_xpath("field[@id='1db804cbb6f04c64b57e60c306557d91']/value", $elements->item($i));
      @list($record["end_year"], $record["end_month"]) = explode("/", $end_date);
      $records[$id] = $record;
    }
    return $records;
  } // }}}
  
  /**
   * Reverses the first/last names of authors in the list; e.g. takes
   * "Doe J, Klein C, Einstein A" and returns "J. Doe, C. Klein,
   * A. Einstein"
   * @param $s The string to process
   * @param $dots Optional; if set to false, will not add periods after
   *  the initials
   */
  public static function reverseAuthors($s, $dots = true) // {{{
  {
    $out = "";
    $authors = explode(",", $s);
    $rec_no = 0;
    foreach ($authors as $author)
    {
      if ($rec_no++ > 0)
        $out .= ", ";
      $p = strrpos($author, " ");
      if ($p === false)
        $out .= "$author";
      else
      {
        $last_names = trim(substr($author, 0, $p));
        $first_names = trim(substr($author, $p));
        if ($dots)
          $first_names = preg_replace("/(\\w)/", "$1.", $first_names);
        $out .= "$first_names $last_names";
      }
    }
    return trim($out);
  } // }}}
  
  /**
   * Reverse a single author, i.e. takes "Einstein, Albert" and returns
   * "Albert Einstein".
   * @param $s The string to process
   * @param $dots Optional; if set to false, will not add periods after
   *  the first name part
   */
  public static function reverseAuthor($s, $dots = true) // {{{
  {
    $out = "";
    @list($last, $first) = explode(",", $s);
    $out .= $first;
    if ($dots)
      $out .= ".";
    $out .= " ".$last;
    return $out;
  } // }}}
  
  /**
   * Returns the (English) caption associated to a particular constant,
   * according to the online documentation of the CCV
   * @param $value The value to look for
   * @param $key Optional. If specified, the function will only look
   *   for the value in a particular subsection of the list (e.g.
   *   "Funding Organization")
   * @return The caption associated to that value
   */
  public static function getCaptionFromValue($value, $key = null) // {{{
  {
    global $CCV_CONST;
    if ($key === null)
    {
      $to_iterate = array_values($CCV_CONST);
      foreach ($to_iterate as $entry)
      {
        foreach ($entry as $caption => $val)
        {
          if ($val === $value)
            return $caption;
        }
      }
    }
    else
    {
      $to_iterate = $CCV_CONST[$key];
      foreach ($to_iterate as $caption => $val)
      {
        if ($val === $value)
          return $caption;
      }
    }
    return "?";
  } // }}}
  
  /**
   * Returns the first value of the query
   */
  private function get_xpath($query, $ref_element) // {{{
  {
    $els = $this->m_xpath->query($query, $ref_element);
    if ($els && $els->length > 0)
      return trim($els->item(0)->nodeValue);
    return "";
  } // }}}
} // }}}

/* :folding=explicit:wrap=none: */
?>
