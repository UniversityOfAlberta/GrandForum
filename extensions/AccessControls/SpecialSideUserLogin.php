<?php

use MediaWiki\Auth\AuthManager;

class SpecialSideUserLogin extends LoginSignupSpecialPage {

    function __construct(){
        parent::__construct('UserLogin');
    }
    
    protected function mainLoginForm( array $requests, $msg = '', $msgtype = 'error' ) {
        global $wgTitle;
        if(!empty($_POST) && $wgTitle->getText() != "UserLogin"){
            $msg = "";
        }
        parent::mainLoginForm($requests, $msg, $msgtype);
    }
    
    protected function postProcessFormDescriptor( &$formDescriptor, $requests ) {
        parent::postProcessFormDescriptor($formDescriptor, $requests);
        unset($formDescriptor['linkcontainer']);
        if(isset($formDescriptor['username']) && isset($formDescriptor['password'])){
            $formDescriptor['username']['autofocus'] = false;
            $formDescriptor['password']['autofocus'] = false;
        }
        unset($formDescriptor['username']['label-raw']);
        unset($formDescriptor['password']['label-message']);
        //TODO: Might need to fix!!! $formDescriptor['passwordReset']['cssclass'] = " underlined highlights-text";
    }
    
    protected function isSignup() {
		return false;
	}
	
	/**
	 * Run any hooks registered for logins, then HTTP redirect to
	 * $this->mReturnTo (or Main Page if that's undefined).  Formerly we had a
	 * nice message here, but that's really not as useful as just being sent to
	 * wherever you logged in from.  It should be clear that the action was
	 * successful, given the lack of error messages plus the appearance of your
	 * name in the upper right.
	 * @param bool $direct True if the action was successful just now; false if that happened
	 *    pre-redirection (so this handler was called already)
	 * @param StatusValue|null $extraMessages
	 */
	protected function successfulAction( $direct = false, $extraMessages = null ) {
		$secureLogin = $this->getConfig()->get( MainConfigNames::SecureLogin );

		$user = $this->targetUser ?: $this->getUser();
		$session = $this->getRequest()->getSession();

		if ( $direct ) {
			$user->touch();

			$this->clearToken();

			if ( $user->requiresHTTPS() ) {
				$this->mStickHTTPS = true;
			}
			$session->setForceHTTPS( $secureLogin && $this->mStickHTTPS );

			// If the user does not have a session cookie at this point, they probably need to
			// do something to their browser.
			if ( !$this->hasSessionCookie() ) {
				$this->mainLoginForm( [ /*?*/ ], $session->getProvider()->whyNoSession() );
				// TODO something more specific? This used to use nocookieslogin
				return;
			}
		}

		# Run any hooks; display injected HTML if any, else redirect
		$injected_html = '';
		$this->getHookRunner()->onUserLoginComplete(
			$user, $injected_html, $direct );

		if ( $injected_html !== '' || $extraMessages ) {
			$this->showSuccessPage( 'success', $this->msg( 'loginsuccesstitle' ),
				'loginsuccess', $injected_html, $extraMessages );
		} else {
			$helper = new LoginHelper( $this->getContext() );
			$helper->showReturnToPage( 'successredirect', $this->mReturnTo, $this->mReturnToQuery,
				$this->mStickHTTPS );
		}
	}
	
	protected function logAuthResult( $success, $status = null ) {
		LoggerFactory::getInstance( 'authevents' )->info( 'Login attempt', [
			'event' => 'login',
			'successful' => $success,
			'status' => strval( $status ),
		] );
	}
	
	protected function getDefaultAction( $subPage ) {
		return AuthManager::ACTION_LOGIN;
	}
    
    function render(){
        global $wgTitle;
        if($wgTitle->getText() == "UserLogin"){
            return;
        }
        $this->getOutput()->clearHTML();
        $this->beforeExecute("");
        $this->execute("");
        echo $this->getOutput()->getHTML();
        echo "<script type='text/javascript'>
            $('#side form[name=userlogin]').attr('action', $('#side form[name=userlogin]').attr('action').replace('index.php?title=Special:UserLogin', 'index.php/Special:UserLogin?title=Special:UserLogin'));
        </script>";
    }

}

?>
