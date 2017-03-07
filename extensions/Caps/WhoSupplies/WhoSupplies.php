<?php

$dir = dirname(__FILE__) . '/';
$wgSpecialPages['WhoSupplies'] = 'WhoSupplies'; # Let MediaWiki know about the special page.
$wgExtensionMessagesFiles['WhoSupplies'] = $dir . 'WhoSupplies.i18n.php';
$wgSpecialPageGroups['WhoSupplies'] = 'network-tools';

class WhoSupplies extends SpecialPage{

    function WhoSupplies() {
        parent::__construct("WhoSupplies", null, true);
    }
    
    function userCanExecute($user){
        $person = Person::newFromUser($user);
        return $person->isLoggedIn();
    }

    function execute($par){
        global $wgOut, $wgUser, $wgServer, $wgScriptPath, $wgTitle, $wgMessage, $wgLang;
        $wgOut->addHTML("<p><i>Click each province and territory to find out more information about who supplies/pays for Mifegymiso in your region</i></p>");
        $wgOut->addHTML("<div id='map' width='100%'></div>");
        $wgOut->addHTML("<script type='text/javascript' src='$wgServer$wgScriptPath/extensions/Caps/WhoSupplies/map.js'></script>");
        $wgOut->addHTML("<div id='accordion'>
            <h2>National Regulations</h2>
            <div>
                <ul>
                    <li>Health Canada’s <a href='https://www.caps-cpca.ubc.ca/AnnokiUploadAuth.php/5/5e/Patients_mifegymisopatientconsentforme11_29_16.pdf' target='_blank'>Consent Form</a> & <a href='https://www.caps-cpca.ubc.ca/AnnokiUploadAuth.php/7/7a/Patients_Mifegymiso_Eng_guide_01_19_17.pdf' target='_blank'>Patient Information Guide</a></li>
                    <li>Awaiting the <a href='https://www.cadth.ca/mifepristone-and-misoprostol' target='_blank'>Canadian Agency for Drugs and Technologies in Health</a> (CADTH) Common Drug Review of Mifegymiso®, scheduled for March 2017. Approval may impact insurance coverage and provincial and territorial formularies (i.e list publically-funded drugs for individuals who qualify and/or do not have private insurance, for example, individuals on social assistance).</li>
                    <li>As of March 4th, 2017, private insurance providers intent on Mifegymiso® coverage are:
                        <ol>
                            <li>Great West Life: Open coverage</li>
                            <li>Medavie Blue Cross: Open coverage</li>
                            <li>Manulife: Open Coverage</li>
                            <li>Pacific Blue Cross: In review; a response anticipated in 1 month</li>
                            <li>Alberta Blue Cross: In review; a response anticipated in 1 month.</li>
                            <li>TELUS: still in review</li>
                            <li>Greenshield: in review</li>
                        </ol>
                    </li>
                </ul>
            </div>

            <h2 id='AB'>Alberta</h2>
            <div>
                <ul>
                    <li>As reported in the media, Mifegymiso®, ~ $300/dose (Oct. 7th, 2016) <a href='http://edmontonjournal.com/news/politics/paula-simons-access-to-abortion-drug-one-step-closer-for-alberta-women' target='_blank'>read more</a></li>
                    <li>As of Feb. 24, 2017, Celopharma has shipped mifepristone to clinics in Calgary</li>
                    <li>As reported in the media, Calgary’s Kensington Clinic is providing Mifegymiso® for free, <a href='https://www.thestar.com/opinion/commentary/2017/03/06/abortion-pill-rollout-deeply-flawed-mallick.html' target='_blank'>read more</a></li>
                </ul>
            </div>
            
            <h2 id='BC'>British Columbia</h2>
            <div>
                <ul>
                    <li>Both the College of Physicians and Surgeons of BC and the College of Pharmacists of BC  have provided guidance to their members on dispensing mifepristone</li>
                    <li>In private clinics, individuals must pay out of pocket, ~$300 per package </li>
                    <li>Celopharma advised women to bring signed consent to pharmacy, with prescription- Not applicable in BC! <a href='http://www.bcpharmacists.org/mifegymiso' target='_blank'>read more</a></li>
                    <li>Mifegymiso® is being considered for possible coverage under the <a href='http://www2.gov.bc.ca/assets/gov/health/health-drug-coverage/pharmacare/mifepristone-misoprostol-3509-info.pdf' target='_blank'>B.C. PharmaCare Program</a> (Feb. 14th, 2017)</li>
                    <li>As of Feb. 24, 2017, Celopharma has shipped mifepristone to clinics in Vancouver</li>
                </ul>
            </div>
            
            <h2 id='MB'>Manitoba</h2>
            <div>
                <ul>
                    <li>As reported in the media, Mifegymiso® will not be coming to Manitoba for a few more months <a href='http://www.cbc.ca/news/canada/manitoba/mifegymiso-abortion-pill-not-in-manitoba-1.3967489' target='_blank'>read more</a></li>
                    <li>As of Feb. 24, 2017, Celopharma has shipped mifepristone to clinics in Winnipeg</li>
                </ul>
            </div>
            
            <h2 id='NB'>New Brunswick</h2>
            <div>
                <ul>
                    <li>As reported in the media, Student Health Plans covering: St Thomas University, $5 co-pay (Feb. 13th, 2017) <a href='https://www.lifesitenews.com/news/for-just-a-5-co-pay-abortion-drug-will-be-available-to-students-at-st.-thom' target='_blank'>read more</a></li>
                    <li>As reported in the media, Mifepristone is not making inroads into New Brunswick; only 14 physicians have registered for the medical abortion training (Feb. 27th, 2017) <a href='http://www.cbc.ca/news/canada/new-brunswick/mifegymiso-canada-health-doctors-1.4000643' target='_blank'>read more</a></li>
                </ul>
            </div>
            
            <h2 id='NL'>Newfoundland and Labrador</h2>
            <div>
                <ul>
                    <li>As reported in the media, Canadian distributor Celopharma has not reported shipments here to date (Jan. 28th, 2017) <a href='http://www.cbc.ca/news/canada/newfoundland-labrador/abortion-drug-not-available-1.2662032' target='_blank'>read more</a></li>
                </ul>
            </div>
            
            <h2 id='NT'>Northwest Territories</h2>
            <div>
                <ul>
                    <li>As of Jan. 28, 2017, Canadian distributor Celopharma has not reported shipments here to date</li>
                </ul>
            </div>
            
            <h2 id='NS'>Nova Scotia</h2>
            <div>
                <ul>
                    <li>Mifegymiso®  may not be covered by most provincial drug plans, individuals will have to pay anywhere from $270 to $300 out of pocket, (Sept. 26th, 2016) <a href='http://www.shns.ca/?q=tags/mifegymiso' target='_blank'>read more</a></li>
                </ul>
            </div>
            
            <h2 id='NU'>Nunavut</h2>
            <div>
                <ul>
                    <li>As of Jan. 28, 2017, Canadian distributor Celopharma has not reported shipments here to date</li>
                </ul>
            </div>
            
            <h2 id='ON'>Ontario</h2>
            <div>
                <ul>
                    <li>Distributor in Ontario via Celopharma or wholesaler, McKesson <a href='https://www.mckesson.ca/mckesson-pharmaceutical' target='_blank'>read more</a></li>
                    <li>As of Feb. 24, 2017, Celopharma has shipped mifepristone to clinics in Toronto and Ottawa</li>
                </ul>
            </div>
            
            <h2 id='PE'>Prince Edward Island</h2>
            <div>
                <ul>
                    <li>As reported in the media, Student Health Plans covering: UPEI (90% covered) and Holland College (80% covered) (Feb. 10th, 2017) <a href='http://www.cbc.ca/news/canada/prince-edward-island/abortion-drug-pill-mifegymiso-pei-doctors-pharmacists-1.3976558' target='_blank'>read more info</a></li>
                </ul>
            </div>
            
            <h2 id='QC'>Quebec</h2>
            <div>
                <ul>
                    <li>Public coverage of Mifegymiso® is currently undergoing an evaluation process (Feb. 6th, 2017) <a href='http://www.cmq.org/nouvelle/en/recommandations-interimaires-bon-usage-mifegymiso.aspx'>read more</a></li>
                </ul>
            </div>
            
            <h2 id='SK'>Saskatchewan</h2>
            <div>
                <ul>
                    <li>As reported in the media on March 6th, 2017, Mifegymiso® has been shipped to Saskatoon, <a href='http://globalnews.ca/news/3291520/abortion-pill-mifegymiso-starts-to-arrive-in-saskatchewan/' target='_blank'>read more</a></li>
                </ul>
            </div>
            
            <h2 id='YT'>Yukon</h2>
            <div>
                <ul>
                    <li>As of Feb. 24, 2017, Celopharma has shipped mifepristone to clinics in Whitehorse</li>
                </ul>
            </div>
        </div>
        
        <script type='text/javascript'>
            $('#accordion').accordion({
                autoHeight: false
            });
        </script>");
    }

}

?>
