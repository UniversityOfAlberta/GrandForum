<?php

/**
 * implements Special:Prefixindex
 * @ingroup SpecialPage
 */
class SpecialPrefixindex extends SpecialAllpages {
	// Inherit $maxPerPage
	
	function __construct(){
		parent::__construct( 'Prefixindex' );
	}
	
	/**
	 * Entry point : initialise variables and call subfunctions.
	 * @param $par String: becomes "FOO" when called like Special:Prefixindex/FOO (default null)
	 */
	function execute( $par ) {
		global $wgRequest, $wgOut, $wgContLang;

		$this->setHeaders();
		$this->outputHeader();

		# GET values
		$from = $wgRequest->getVal( 'from' );
		$prefix = $wgRequest->getVal( 'prefix', '' );
		$namespace = $wgRequest->getInt( 'namespace' );
		$namespaces = $wgContLang->getNamespaces();

		$wgOut->setPagetitle( ( $namespace > 0 && in_array( $namespace, array_keys( $namespaces ) ) )
			? wfMsg( 'allinnamespace', str_replace( '_', ' ', $namespaces[$namespace] ) )
			: wfMsg( 'prefixindex' )
		);

		if( isset( $par ) ){
			$this->showPrefixChunk( $namespace, $par, $from );
		} elseif( isset( $prefix ) ){
			$this->showPrefixChunk( $namespace, $prefix, $from );
		} elseif( isset( $from ) ){
			$this->showPrefixChunk( $namespace, $from, $from );
		} else {
			$wgOut->addHTML( $this->namespacePrefixForm( $namespace, null ) );
		}
	}
	
	/**
	* HTML for the top form
	* @param integer $namespace A namespace constant (default NS_MAIN).
	* @param string $from dbKey we are starting listing at.
	*/
	function namespacePrefixForm( $namespace = NS_MAIN, $from = '' ) {
		global $wgScript;
		$t = $this->getTitle();

		$out  = Xml::openElement( 'div', array( 'class' => 'namespaceoptions' ) );
		$out .= Xml::openElement( 'form', array( 'method' => 'get', 'action' => $wgScript ) );
		$out .= Xml::hidden( 'title', $t->getPrefixedText() );
		$out .= Xml::openElement( 'fieldset' );
		$out .= Xml::element( 'legend', null, wfMsg( 'allpages' ) );
		$out .= Xml::openElement( 'table', array( 'id' => 'nsselect', 'class' => 'allpages' ) );
		$out .= "<tr>
				<td class='mw-label'>" .
				Xml::label( wfMsg( 'allpagesprefix' ), 'nsfrom' ) .
				"</td>
				<td class='mw-input'>" .
					Xml::input( 'from', 30, str_replace('_',' ',$from), array( 'id' => 'nsfrom' ) ) .
				"</td>
			</tr>
			<tr>
				<td class='mw-label'>" .
					Xml::label( wfMsg( 'namespace' ), 'namespace' ) .
				"</td>
				<td class='mw-input'>" .
					Xml::namespaceSelector( $namespace, null ) . ' ' .
					Xml::submitButton( wfMsg( 'allpagessubmit' ) ) .
				"</td>
				</tr>";
		$out .= Xml::closeElement( 'table' );
		$out .= Xml::closeElement( 'fieldset' );
		$out .= Xml::closeElement( 'form' );
		$out .= Xml::closeElement( 'div' );
		return $out;
	}

	/**
	 * @param integer $namespace (Default NS_MAIN)
	 * @param string $from list all pages from this name (default FALSE)
	 */
	function showPrefixChunk( $namespace = NS_MAIN, $prefix, $from = null ) {
		global $wgOut, $wgUser, $wgContLang, $wgLang;

		$sk = $wgUser->getSkin();

		if (!isset($from)) $from = $prefix;

		$fromList = $this->getNamespaceKeyAndText($namespace, $from);
		$prefixList = $this->getNamespaceKeyAndText($namespace, $prefix);
		$namespaces = $wgContLang->getNamespaces();

		if ( !$prefixList || !$fromList ) {
			$out = wfMsgWikiHtml( 'allpagesbadtitle' );
		} elseif ( !in_array( $namespace, array_keys( $namespaces ) ) ) {
			// Show errormessage and reset to NS_MAIN
			$out = wfMsgExt( 'allpages-bad-ns', array( 'parseinline' ), $namespace );
			$namespace = NS_MAIN;
		} else {
			list( $namespace, $prefixKey, $prefix ) = $prefixList;
			list( /* $fromNs */, $fromKey, $from ) = $fromList;

			### FIXME: should complain if $fromNs != $namespace

			$dbr = wfGetDB( DB_SLAVE );

			$res = $dbr->select( 'page',
				array( 'page_namespace', 'page_title', 'page_is_redirect' ),
				array(
					'page_namespace' => $namespace,
					'page_title LIKE \'' . $dbr->escapeLike( $prefixKey ) .'%\'',
					'page_title >= ' . $dbr->addQuotes( $fromKey ),
				),
				__METHOD__,
				array(
					'ORDER BY'  => 'page_title',
					'LIMIT'     => $this->maxPerPage + 1,
					'USE INDEX' => 'name_title',
				)
			);

			### FIXME: side link to previous

			$n = 0;
			if( $res->numRows() > 0 ) {
				$out = '<table class="wikitable sortable" width="100%" bgcolor="#aaaaaa" cellspacing="1" cellpadding="2" style="text-align:center;">';
				$out .= '<tr bgcolor="#F2F2F2"><th> User Page </th><th> Contributing Users </th><th> Last Modified </th></tr>';
	
				while( ( $n < $this->maxPerPage ) && ( $s = $res->fetchObject() ) ) {
					$t = Title::makeTitle( $s->page_namespace, $s->page_title );
					if( $t ) {
						$link = ($s->page_is_redirect ? '<div class="allpagesredirect">' : '' ) .
							$sk->makeKnownLinkObj( $t, htmlspecialchars( $t->getText() ), false, false ) .
							($s->page_is_redirect ? '</div>' : '' );
					} else {
						$link = '[[' . htmlspecialchars( $s->page_title ) . ']]';
					}
					
					//$pr = getPageRankFor($s->page_namespace, $s->page_title);
					
					$sql = "SELECT r.rev_timestamp, p.page_id
						FROM mw_page p, mw_revision r
						WHERE p.page_title = '{$s->page_title}'
						AND p.page_namespace = '{$s->page_namespace}'
						AND p.page_latest = r.rev_id";
					$data = $dbr->query($sql);
					$row = $dbr->fetchRow($data);
					
					$year = substr($row['rev_timestamp'], 0, 4);
					$month = substr($row['rev_timestamp'], 4, 2);
					$day = substr($row['rev_timestamp'], 6, 2);
					
					$sql = "SELECT DISTINCT user_name 
						FROM mw_revision, mw_page, mw_user 
						WHERE rev_page = page_id
						AND page_id = '{$row['page_id']}'
						AND rev_user = user_id";
					$result = $dbr->query($sql);
					$users = "";
					while($row = $dbr->fetchRow($result)){
						$users .= ", {$row['user_name']}";
					}
					if(strlen($users) > 0){
						$users[0] = null;
					}
					
					$out .= '<tr bgcolor="#FFFFFF">';
					$out .= "<td align='left'>$link</td><td align='left'> $users </td><td> $year-$month-$day </td>";
					$out .= '</tr>';
					$n++;
				}
				$out .= '</tr>';
				$out .= "</table>";
			} else {
				$out = '';
			}
		}

		if ( $this->including() ) {
			$out2 = '';
		} else {
			$nsForm = $this->namespacePrefixForm( $namespace, $prefix );
			$self = $this->getTitle();
			$out2 = Xml::openElement( 'table', array( 'border' => '0', 'id' => 'mw-prefixindex-nav-table' ) )  .
				'<tr>
					<td>' .
						$nsForm .
					'</td>
					<td id="mw-prefixindex-nav-form">' .
						$sk->makeKnownLinkObj( $self, wfMsg ( 'allpages' ) );

			if( isset( $res ) && $res && ( $n == $this->maxPerPage ) && ( $s = $res->fetchObject() ) ) {
				$namespaceparam = $namespace ? "&namespace=$namespace" : "";
				$out2 = $wgLang->pipeList( array(
					$out2,
					$sk->makeKnownLinkObj(
						$self,
						wfMsgHtml( 'nextpage', str_replace( '_',' ', htmlspecialchars( $s->page_title ) ) ),
						"from=" . wfUrlEncode( $s->page_title ) .
						"&prefix=" . wfUrlEncode( $prefix ) . $namespaceparam )
				) );
			}
			$out2 .= "</td></tr>" .
				Xml::closeElement( 'table' );
		}

		$wgOut->addHTML( $out2 . $out );
	}
}
