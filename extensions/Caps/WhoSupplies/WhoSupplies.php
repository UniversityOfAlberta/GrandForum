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
                    <li>Health Canada’s Consent Form & Patient Information Guide (2 PDFs attached)</li>
                    <li>Awaiting the <a href='https://www.cadth.ca/about-cadth/what-we-do/products-services/cdr' target='_blank'>Canadian Agency for Drugs and Technologies in Health</a> (CADTH) Common Drug Review of Mifegymiso®, scheduled for March 2017. Approval may impact insurance coverage and provincial and territorial formularies (i.e list publically-funded drugs for individuals who qualify and/or do not have private insurance, for example, individuals on social assistance).</li>
                </ul>
            </div>

            <h2 id='AB'>Alberta</h2>
            <div>
                <ul>
                    <li>Mifegymiso®, ~ $300/dose (Oct. 7th, 2016) <a href='http://edmontonjournal.com/news/politics/paula-simons-access-to-abortion-drug-one-step-closer-for-alberta-women' target='_blank'>read more</a></li>
                    <li>Celopharma has shipped mifepristone to clinics in Calgary (Feb. 24th, 2017)</li>
                </ul>
            </div>
            
            <h2 id='BC'>British Columbia</h2>
            <div>
                <ul>
                    <li>Both the College of Physicians and Surgeons of BC and the College of Pharmacists of BC say, treat Mifegymiso like any other medication <a href='https://www.cpsbc.ca/for-physicians/college-connector/2016-V04-06/01' target='_blank'>read more</a></li>
                    <li>In private clinics, individuals must pay out of pocket, $300-750 per package <a href='http://www.willowclinic.ca/?page_id=15' target='_blank'>read more</a></li>
                    <li>Celopharma advised women to bring signed consent to pharmacy, with prescription- Not applicable in BC! <a href='http://www.bcpharmacists.org/mifegymiso' target='_blank'>read more</a></li>
                    <li>Mifegymiso® is being considered for possible coverage under the <a href='http://www2.gov.bc.ca/assets/gov/health/health-drug-coverage/pharmacare/mifepristone-misoprostol-3509-info.pdf' target='_blank'>B.C. PharmaCare Program</a> (Feb. 14th, 2017)</li>
                    <li>Celopharma has shipped mifepristone to clinics in Vancouver (Feb. 24th, 2017)</li>
                </ul>
            </div>
            
            <h2 id='MB'>Manitoba</h2>
            <div>
                <ul>
                    <li>Mifegymiso® will not be coming to Manitoba for a few more months (Feb. 9th, 2017) <a href='http://www.cbc.ca/news/canada/manitoba/mifegymiso-abortion-pill-not-in-manitoba-1.3967489' target='_blank'>read more</a></li>
                    <li>Celopharma has shipped mifepristone to clinics in Winnipeg (Feb. 24th, 2017)</li>
                </ul>
            </div>
            
            <h2 id='NB'>New Brunswick</h2>
            <div>
                <ul>
                    <li>Student Health Plans covering: St Thomas University, $5 co-pay (Feb. 13th, 2017) <a href='https://www.lifesitenews.com/news/for-just-a-5-co-pay-abortion-drug-will-be-available-to-students-at-st.-thom' target='_blank'>read more</a></li>
                    <li>Mifepristone is not making inroads into New Brunswick; only 14 physicians have registered for the medical abortion training (Feb. 27th, 2017) <a href='http://www.cbc.ca/news/canada/new-brunswick/mifegymiso-canada-health-doctors-1.4000643' target='_blank'>read more</a></li>
                </ul>
            </div>
            
            <h2 id='NL'>Newfoundland and Labrador</h2>
            <div>
                <ul>
                    <li>Canadian distributor Celopharma as not reported shipments here to date (Jan. 28th, 2017) <a href='http://www.cbc.ca/news/canada/newfoundland-labrador/abortion-drug-not-available-1.2662032' target='_blank'>read more</a></li>
                </ul>
            </div>
            
            <h2 id='NT'>Northwest Territories</h2>
            <div>
                <ul>
                    <li>Canadian distributor Celopharma as not reported shipments here to date (Jan. 28th, 2017)</li>
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
                    <li>Canadian distributor Celopharma as not reported shipments here to date (Jan. 28th, 2017)</li>
                </ul>
            </div>
            
            <h2 id='ON'>Ontario</h2>
            <div>
                <ul>
                    <li>Distributor in Ontario via Celopharma or wholesaler, McKesson <a href='https://www.mckesson.ca/mckesson-pharmaceutical' target='_blank'>read more</a></li>
                    <li>Celopharma has shipped mifepristone to clinics in Toronto and Ottawa (Feb. 24th, 2017)</li>
                </ul>
            </div>
            
            <h2 id='PE'>Prince Edward Island</h2>
            <div>
                <ul>
                    <li>Student Health Plans covering: UPEI (90% covered) and Holland College (80% covered) (Feb. 10th, 2017) <a href='http://www.cbc.ca/news/canada/prince-edward-island/abortion-drug-pill-mifegymiso-pei-doctors-pharmacists-1.3976558' target='_blank'>read more info</a></li>
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
                    <li>Canadian distributor Celopharma as not reported shipments here to date (Jan. 28th, 2017)</li>
                </ul>
            </div>
            
            <h2 id='YT'>Yukon</h2>
            <div>
                <ul>
                    <li>Celopharma has shipped mifepristone to clinics in Whitehorse (Feb. 24th, 2017)</li>
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
