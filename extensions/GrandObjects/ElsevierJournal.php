<?php

/**
 * @package GrandObjects
 */

class ElsevierJournal extends Journal {

    /**
     * Returns Journals from the given issn
     * @param string $issn The issn of the journal
     * @return array The Journals from the given issn. 
     * (Will only return journals from the more recent year)
     */
    static function newFromIssn($issn){
        global $config;
        $md5 = md5($issn);
        if(Cache::exists("elsevier_{$md5}")){
            $output = Cache::fetch("elsevier_{$md5}");
        }
        else{
            $url = "https://api.elsevier.com/content/serial/title/?issn={$issn}&apiKey={$config->getValue('elsevierApi')}&count=25&httpAccept=application/json";
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, $url);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
            $output = json_decode(curl_exec($ch));
            curl_close($ch);
            Cache::store("elsevier_{$md5}", $output, 60*60*24*7);
        }
        $journals = array();
        if(isset($output->{'serial-metadata-response'}->entry)){
            foreach($output->{'serial-metadata-response'}->entry as $entry){
                $journals[] = new ElsevierJournal(array(array(
                    'year' => "",
                    'short_title' => @$entry->{'dc:title'},
                    'iso_abbrev' => @$entry->{'dc:title'},
                    'title' => @$entry->{'dc:title'},
                    'description' => @$entry->{'dc:publisher'},
                    'issn' => @$entry->{'prism:issn'},
                    'eissn' => @$entry->{'prism:eIssn'},
                    'snip' => @$entry->SNIPList->SNIP[0]->{'$'}
                )));
            }
        }
        return $journals;
    }

    /**
     * Returns all Journals
     * @return Journals The array of all Journal objects
     */
    static function getAllJournals(){
        return array();
    }

    static function getAllJournalsBySearch($string){
        global $config;
        $search = urlencode($string);
        $md5 = md5($search);
        if(Cache::exists("elsevier_{$md5}")){
            $output = Cache::fetch("elsevier_{$md5}");
        }
        else{
            if(preg_match("/^(.{4}-.{4})$/m", $search)){
                // Looks like an issn
                $url = "https://api.elsevier.com/content/serial/title/?issn={$search}&apiKey={$config->getValue('elsevierApi')}&count=100&httpAccept=application/json";
            }
            else{
                $url = "https://api.elsevier.com/content/serial/title/?title={$search}&apiKey={$config->getValue('elsevierApi')}&count=100&httpAccept=application/json";
            }
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, $url);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
            $output = json_decode(curl_exec($ch));
            curl_close($ch);
            Cache::store("elsevier_{$md5}", $output, 60*60*24*7);
        }
        $journals = array();
        if(isset($output->{'serial-metadata-response'}->entry)){
            foreach($output->{'serial-metadata-response'}->entry as $entry){
                $percent = 0;
                similar_text(strtolower($string), strtolower(@$entry->{'dc:title'}), $percent);
                $journal = new ElsevierJournal(array(array(
                    'year' => "",
                    'short_title' => @$entry->{'dc:title'},
                    'iso_abbrev' => @$entry->{'dc:title'},
                    'title' => @$entry->{'dc:title'},
                    'description' => @$entry->{'dc:publisher'},
                    'issn' => @$entry->{'prism:issn'},
                    'eissn' => @$entry->{'prism:eIssn'},
                    'snip' => @$entry->SNIPList->SNIP[0]->{'$'}
                )));
                $journal->similarity = $percent;
                $journals[] = $journal;
            }
        }
        usort($journals, function($a, $b){
            return ($b->similarity - $a->similarity);
        });
        return $journals;     
    }
    
    function getRank(){
        return "";
    }

}

?>
