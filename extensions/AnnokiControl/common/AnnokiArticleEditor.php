<?php
/** 
 * Convenience class for modifying articles.
 * @package Annoki
 * @subpackage AnnokiControl
 * @author Brendan Tansey
 */	

/** 
 * Functions that can be used for creating, modifying, and deleting articles on the wiki. 
 * @package Annoki
 * @subpackage AnnokiControl
 * @author Brendan Tansey
 */
class AnnokiArticleEditor {
  
  /**
   * Flags to use when editing a page.  See Article class for options.
   * @var int
   */
  static $edit_flags = EDIT_SUPPRESS_RC; //Set to 0 to disable

  /**
   * Deletes an article.
   * @param Aticle $article The article to delete.
   * @param string $reason The reason for deletion.
   * @return boolean True on success, false on failure.
   */
    public static function deleteArticle($article, $reason='AAE deletion'){
       $title = $article->getTitle();
       if (!$title->userCan('create'))
         return false;

       $suppress = self::$edit_flags & EDIT_SUPPRESS_RC;

       //print "Suppress: ".($suppress?'true':'false')."\n";

       return $article->doDeleteArticle($reason, $suppress);
    }
    
    /**
     * Creates a new article.
     * @param Article $article The article to create.
     * @param string $contents The contents of the soon-to-be article.
     * @param string $summary A description of the article creation.
     * @param User $user The user who will be attributed with the article creation.
     * @param int $flags The flags to use when creating the article.  See the MediaWiki Article class for details.
     * @return Status A Status object containing success/failure information.
     */
    public static function createNewArticle($article, $contents=null, $summary='AAE added new article', $user=null, $flags = null){
      global $wgUser;

      if ($user===null)
	$user = $wgUser;

      if ($flags===null)
	$flags = self::$edit_flags;

      $tempUser = $wgUser;
      $wgUser = $user;
      
      $title = $article->getTitle();
      
       if(is_null($contents) || $contents == "")
         $contents = "Enter Your Text Here!";
        
       $success = $article->doEdit($contents, $summary, EDIT_NEW | $flags); //|EDIT_DEFER_UPDATES);

       $wgUser = $tempUser;
       
       return $success;
     }

    /**
     * Replaces the contents of an article with whatever is given as a parameter.
     * @param Article $article The article to be edited.
     * @param string $contents The new contents of the article.
     * @param string $editSummary A reason for making the edit.
     * @return bool True on success, false on failure.
     */
    public static function replaceArticleContent($article, $contents, $editSummary='AAE replaced article content'){
    	$title = $article->getTitle();
    	if (!$title->userCan('edit'))
    	return false;

    	$success = $article->doEdit($contents, $editSummary, EDIT_UPDATE | self::$edit_flags);
    }

     /**
      * Appends a link to another internal page to the end of an article.
      * @param Article $article The article to be edited.
      * @param string $link The fully qualified page name to which the article will link.
      * @param string $summary A summary of the change.
      * @param string $textAfterLink Text that will appear after the link on the article.
      * @param string $altLinkText Text that will be used instead of the linked page name as the link text.
      * @return mixed True on success, or an error string on failure.  Make sure to compare return value using ===.
      */
    public static function addLinkToArticle($article, $link, $summary='AAE: added link', $textAfterLink="", $altLinkText=null){
       $title = $article->getTitle();
       if (!$title->userCan('edit'))
         return "User cannot edit";

       $currentRev = Revision::newFromTitle($article->getTitle());
       $text = $currentRev->revText();
            
       $tempLinkString = self::createLinkWikitext($link, $textAfterLink, $altLinkText);

       if(strpos($text,$tempLinkString) === false){
	 $text .= "\n\n".$tempLinkString;
	 
	 $success = $article->doEdit($text, $summary, EDIT_UPDATE | self::$edit_flags);//|EDIT_DEFER_UPDATES);
	 if ($success)
	   $success = true; //Just in case doEdit() returns 1, or some other 'true' value.
	 else
	   $success = "Article edit failed";
       }
       else
	 $success = "Link already exists";
       
       return $success;
     }

    /**
     * Edits a page to either append the given text to the content, or replace the existing content with the given text.
     * @param Article $article The article to edit.
     * @param string $edit The new text that will appear on the article.
     * @param string $summary A reason for the edit.
     * @param boolean $append True to place the new text at the end of the page; false to overwrite the page.
     * @param User $user The user to be credited with the edit.
     * @return Status A Status object containing success/failure information.
     */
    public static function editArticle($article, $edit, $summary='AAE: edited article', $append=false, $user=null){
    	global $wgUser;

    	if ($user===null)
    	$user = $wgUser;

    	$tempUser = $wgUser;
    	$wgUser = $user;

    	$text = '';
    	if ($append){
    		$currentRev = Revision::newFromTitle($article->getTitle());
    		$text = $currentRev->revText();
    	}

    	$text .= $edit;

    	$success = $article->doEdit($text, $summary, EDIT_UPDATE | self::$edit_flags);//|EDIT_DEFER_UPDATES);
    	$wgUser = $tempUser;

    	return $success;
    }

    /**
     * Format a link as wiki text.
     * @param $link The page that will serve as the endpoint for the link.
     * @param $textAfterLink Text that will appear after the link.
     * @param $altLinkText Text that will appear in place of the linked page name.
     * @return string The wiki text for the link.
     */
    public static function createLinkWikitext($link, $textAfterLink="", $altLinkText=null){
      if (is_null($altLinkText))
	$tempLinkString = "[[$link]]";
      else
	$tempLinkString = "[[$link | $altLinkText]]";
      
      return $tempLinkString.$textAfterLink;
    }
}
?>
