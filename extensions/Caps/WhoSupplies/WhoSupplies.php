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
                <h3>Physician Regulations</h3>
                <ul>
                    <li>There is no longer any requirement for a health care professional to witness a person taking either of the drugs in Mifegymiso, <a href='https://www.caps-cpca.ubc.ca/index.php/Special:LatestNews?pdf=2'>read more</a></li>
                    <li>People are not required to present a signed consent form to a pharmacist when filling a Rx for Mife, <a href='https://www.caps-cpca.ubc.ca/index.php/Special:LatestNews?pdf=3' >read more</a></li>
                    <li>On May 18th, 2017 Celopharma, in collaboration with Health Canada, issued communication to clarify the different requirements to prescribe, order, stock, and/or dispense Mifegymiso®, <a href='{$wgServer}{$wgScriptPath}/data/HPRC_Mifegymiso_{$wgLang->getCode()}-signed.pdf'>read more</a></li>
                    <li>On May 18th, Health Canada released a Dear Healthcare Professional Letter to clarify the different requirements associate with Mifegymiso®, <a href='http://healthycanadians.gc.ca/recall-alert-rappel-avis/hc-sc/2017/63330a-eng.php' target='_blank'>read more</a></li>
                    <li>On November 7th, 2017, Health Canada announced significant changes to mifepristone distribution, prescribing and dispensing processes. To view Health Canada's communication, <a href='http://healthycanadians.gc.ca/recall-alert-rappel-avis/hc-sc/2017/65030a-eng.php' target='_blank'>click here</a>. For a summary developed by the CAPS team, <a href='https://www.caps-cpca.ubc.ca/index.php/Special:LatestNews'>click here</a></li>
                </ul>
                
                <h3>Pharmacist Regulations</h3>
                <ul>
                    <li>People are not required to present a signed consent form to a pharmacist when filling a Rx for Mife, <a href='https://www.caps-cpca.ubc.ca/index.php/Special:LatestNews?pdf=3' >read more</a></li>
                    <li>Health Canada’s <a href='https://www.caps-cpca.ubc.ca/AnnokiUploadAuth.php/7/7a/Patients_Mifegymiso_Eng_guide_01_19_17.pdf' target='_blank'>Patient Information Guide</a></li>
                    <li>On May 18th, 2017 Celopharma, in collaboration with Health Canada, issued communication to clarify the different requirements to prescribe, order, stock, and/or dispense Mifegymiso®, <a href='{$wgServer}{$wgScriptPath}/data/HPRC_Mifegymiso_{$wgLang->getCode()}-signed.pdf'>read more</a></li>
                    <li>On November 7th, 2017 Health Canada announced that all pharmacists across Canada may dispense Mifegymiso® directly to patients. <a href='https://www.caps-cpca.ubc.ca/index.php/File:Canadian_CPhA_Mifegymiso_access_coverage_advocacy_KT_110817.pdf'>Click here</a> to view an excellent resource developed by our partners at the Canadian Pharmacists Association.</li>
                </ul>
                
                <h3>Coverage Information</h3>
                <ul>
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
                    <li>On April 18th, 2017, the Common Drug Review (CDR) expert panel endorsed public coverage for Mifegymiso® in Canada, read more from the CDR <a href='https://www.cadth.ca/mifepristone-and-misoprostol-0' target='_blank'>here</a> and/or from the media <a href='http://www.theglobeandmail.com/news/national/expert-panel-endorses-public-coverage-for-abortion-pill-in-canada/article34757082/%20(April%2020th,%202017)' target='_blank'>here</a> (April 19th, 2017)</li>
                    <li>Click <a href='https://www.cadth.ca/sites/default/files/cdr/complete/SR0502_complete_Mifegymiso-Apr-20-17-e.pdf' target='_blank'>here</a> for the Canadian Agency for Drugs and Technologies in Health (CADTH) Common Drug Review of Mifegymiso, 'Final Recommendations and Reasons' report</li>
                    <li>The NIHB is expected to publicly announce full coverage of Mifegymiso® for First Nations and Inuit women, <a href='https://www.caps-cpca.ubc.ca/index.php/Special:LatestNews?pdf=12'>read more</a></li>
                    <li><a href='https://www.caps-cpca.ubc.ca/index.php/File:Canadian_CPhA_Mifegymiso_access_coverage_advocacy_KT_110817.pdf'>Click here</a> for an infographic that shows Mifegymiso® coverage across Canada, as of December 15th, 2017.</li>
                </ul>
                
                <h3>Shipment/Distributing Information</h3>
                <ul>
                    <li>The approval of Mifegymiso® has exposed both the legal and practical deficiencies of Health Canada's drug review process. Read more on this analysis <a href='http://policyoptions.irpp.org/magazines/april-2017/regulatory-risk-mismanagement-and-the-abortion-pill/' target='_blank'>here</a> (April 25th, 2017)</li>
                </ul>
            </div>

            <h2 id='AB'>Alberta</h2>
            <div>
                <h3>Physician Regulations</h3>
                <ul>
                    <li>See information under 'National Regulations'</li>
                </ul>
                
                <h3>Pharmacist Regulations</h3>
                <ul>
                    <li>On June 12, 2017, the Alberta College of Pharmacists published guidelines to support the practice of pharmacy professionals dispensing Mifegymiso®, <a href='https://pharmacists.ab.ca/guidelines-dispensing-mifegymiso' target='_blank'>read more</a></li>
                    <li>Alberta pharmacist Dr. Nese Yuksel, has written additional information for AB Pharmacists, <a href='https://secure.campaigner.com/CSB/public/ReadmoreContent.aspx?id=28435696&campaignid=21466857' target='_blank'>read more</a></li>
                </ul>
                
                <h3>Coverage Information</h3>
                <ul>
                    <li>As reported in the media, Mifegymiso®, ~ $300/dose (Oct. 7th, 2016) <a href='http://edmontonjournal.com/news/politics/paula-simons-access-to-abortion-drug-one-step-closer-for-alberta-women' target='_blank'>read more</a></li>
                    <li>As reported in the media on March 6th 2017, Calgary’s Kensington Clinic is providing Mifegymiso® for free, <a href='https://www.thestar.com/opinion/commentary/2017/03/06/abortion-pill-rollout-deeply-flawed-mallick.html' target='_blank'>read more</a></li>
                    <li>Following the positive listing by the Canadian expert review of Mifegymiso, Alberta became the second province (after New Brunswick) to announce that it will be offering universal access to the drug, <a href='https://beta.theglobeandmail.com/news/national/alberta-promises-to-offer-universal-access-to-the-abortion-pill/article34768401/?ref=http://www.theglobeandmail.com&service=mobile' target='_blank'>read more from the media</a> (April 20th, 2017)</li>
                    <li>On July 24th, 2017 government officials from the province of Alberta announced that mifepristone coverage is now fully operational across their province, <a href='http://www.timescolonist.com/alberta-latest-province-to-cover-the-cost-of-abortion-pill-mifegymiso-1.21338876' target='_blank'>read more</a></li>
                </ul>
                
                <h3>Shipment/Distributing Information</h3>
                <ul>
                    <li>As of Feb. 24, 2017, Celopharma has shipped mifepristone to clinics in Calgary</li>
                </ul>
            </div>
            
            <h2 id='BC'>British Columbia</h2>
            <div>
                <h3>Physician Regulations</h3>
                <ul>
                    <li>Both the <a href='https://www.cpsbc.ca/for-physicians/college-connector/2016-V04-06/01' target='_blank'>College of Physicians and Surgeons of BC</a> and the <a href='http://www.bcpharmacists.org/mifegymiso' target='_blank'>College of Pharmacists of BC</a> have provided guidance to their members on dispensing mifepristone</li>
                </ul>
                
                <h3>Pharmacist Regulations</h3>
                <ul>
                    <li>Both the <a href='https://www.cpsbc.ca/for-physicians/college-connector/2016-V04-06/01' target='_blank'>College of Physicians and Surgeons of BC</a> and the <a href='http://www.bcpharmacists.org/mifegymiso' target='_blank'>College of Pharmacists of BC</a> have provided guidance to their members on dispensing mifepristone</li>
                    <li>On January 2, 2018, PharmaNet recently posted a newsletter detailing how pharmacies across BC will be able to access subsidized Mifegymiso. For more information, <a href='http://www.gov.bc.ca/pharmacarenewsletter' target='_blank'>click here</a></li>
                </ul>
                
                <h3>Coverage Information</h3>
                <ul>
                    <li>As of January 15, 2018, the BC Ministry of Health PharmaCare program will provide Mifegymiso® at no charge to BC Residents through BC's community pharmacies, <a href='http://www.vancourier.com/news/free-access-to-abortion-pill-a-game-changer-for-b-c-women-1.23135464' target='_blank'>read more</a></li>
                </ul>
                
                <h3>Shipment/Distributing Information</h3>
                <ul>
                    <li>As of January 15, 2018, mifepristone will be distributed by the BC Centre for Disease Control to pharmacies throughout British Columbia, <a href='https://www2.gov.bc.ca/assets/gov/health/health-drug-coverage/pharmacare/newsletters/news18-001.pdf' target='_blank'>read more</a></li>
                </ul>
            </div>
            
            <h2 id='MB'>Manitoba</h2>
            <div>
                <h3>Physician Regulations</h3>
                <ul>
                    <li>See information under 'National Regulations'</li>
                </ul>
                
                <h3>Pharmacist Regulations</h3>
                <ul>
                    <li>In June, 2017, the Manitoba College of Pharmacists approved guidelines for the distribution of Mifegymiso® in Manitoba, specifying that pharmacist's in Manitoba cannot dispense Mifegymiso® directly to a patient, <a target='_blank' href='http://www.cphm.ca/uploaded/web/Guidelines/Mifegymiso/Final%20Mifegymiso%20Guideline%20for%20Pharmacists.pdf'>read more</a></li>
                    <li>On November 7th, 2017 Health Canada announced that all pharmacists across Canada may dispense Mifegymiso® directly to patients. <a href='https://www.caps-cpca.ubc.ca/index.php/File:Canadian_CPhA_Mifegymiso_access_coverage_advocacy_KT_110817.pdf'>Click here</a> to view an excellent resource developed by our partners at the Canadian Pharmacists Association.</li>
                </ul>
                
                <h3>Coverage Information</h3>
                <ul>
                    <li>As reported in the media on March 9th, 2017, Winnipeg Regional Health Authority has confirmed Mifegymiso® is now available at Health Sciences Centre's Women's Hospital, but patients must pay $350, <a href='http://www.cbc.ca/beta/news/canada/manitoba/abortion-pill-available-at-hsc-in-winnipeg-but-patients-will-pay-1.4016440' target='_blank'>read more</a></li>
                    <li>On July 20th, 2017 government officials from the province of Manitoba announced that mifepristone will be covered at select sites, <a href='http://www.cbc.ca/news/canada/manitoba/manitoba-to-cover-cost-of-abortion-pill-mifegymiso-at-approved-centres-1.4214354' target='_blank'>read more</a></li>
                </ul>
                
                <h3>Shipment/Distributing Information</h3>
                <ul>
                    <li>As of Feb. 24, 2017, Celopharma has shipped mifepristone to clinics in Winnipeg</li>
                </ul>
            </div>
            
            <h2 id='NB'>New Brunswick</h2>
            <div>
                <h3>Physician Regulations</h3>
                <ul>
                    <li>See information under 'National Regulations'</li>
                </ul>
                
                <h3>Pharmacist Regulations</h3>
                <ul>
                    <li>See information under 'National Regulations'</li>
                </ul>
                
                <h3>Coverage Information</h3>
                <ul>
                    <li>As reported in the media, Student Health Plans covering: St Thomas University, $5 co-pay (Feb. 13th, 2017) <a href='https://www.lifesitenews.com/news/for-just-a-5-co-pay-abortion-drug-will-be-available-to-students-at-st.-thom' target='_blank'>read more</a></li>
                    <li>As reported in the media, the New Brunswick provincial government is the first province/territory in Canada to announce universal coverage for mifepristone (April 4th, 2017), <a href='http://www.cbc.ca/news/canada/new-brunswick/abortion-pill-new-brunswick-1.4054517' target='_blank'>read more</a></li>
                    <li>On July 7th, the Health Minister of New Brunswick announced that the provincial program to provide universal access to mifepristone is officially operational and available to 'New Brunswickers', <a href='http://www2.gnb.ca/content/gnb/en/news/news_release.2017.07.0952.html' target='_blank'>read more</a> here.</li>
                </ul>
                
                <h3>Shipment/Distributing Information</h3>
                <ul>
                    <li>As reported in the media, Mifepristone is not making inroads into New Brunswick; only 14 physicians have registered for the medical abortion training (Feb. 27th, 2017) <a href='http://www.cbc.ca/news/canada/new-brunswick/mifegymiso-canada-health-doctors-1.4000643' target='_blank'>read more</a></li>
                </ul>
            </div>
            
            <h2 id='NL'>Newfoundland and Labrador</h2>
            <div>
                <h3>Physician Regulations</h3>
                <ul>
                    <li>See information under 'National Regulations'</li>
                </ul>
                
                <h3>Pharmacist Regulations</h3>
                <ul>
                    <li>See information under 'National Regulations'</li>
                </ul>
                
                <h3>Coverage Information</h3>
                <ul>
                    <li>See information under 'National Regulations'</li>
                </ul>
                
                <h3>Shipment/Distributing Information</h3>
                <ul>
                    <li>As of April 11th, 2017, Celopharma has shipped mifepristone to St. John’s</li>
                </ul>
            </div>
            
            <h2 id='NT'>Northwest Territories</h2>
            <div>
                <h3>Physician Regulations</h3>
                <ul>
                    <li>See information under 'National Regulations'</li>
                </ul>
                
                <h3>Pharmacist Regulations</h3>
                <ul>
                    <li>See information under 'National Regulations'</li>
                </ul>
                
                <h3>Coverage Information</h3>
                <ul>
                    <li>See information under 'National Regulations'</li>
                </ul>
                
                <h3>Shipment/Distributing Information</h3>
                <ul>
                    <li>As of Jan. 28, 2017, Canadian distributor Celopharma has not reported shipments here to date</li>
                </ul>
            </div>
            
            <h2 id='NS'>Nova Scotia</h2>
            <div>
                <h3>Physician Regulations</h3>
                <ul>
                    <li>The College of Physicians and Surgeons of Nova Scotia has become the third province, after British Columbia and Ontario to announce their support of pharmacist dispensing of Mifegymiso®, <a href='https://www.cpsns.ns.ca/DesktopModules/Bring2mind/DMX/Download.aspx?PortalId=0&TabId=129&EntryId=293' target='_blank'>read more</a>.</li>
                </ul>
                
                <h3>Pharmacist Regulations</h3>
                <ul>
                    <li>The Nova Scotia College of Pharmacists has published the following guidance to pharmacists and pharmacy technicians when dispensing Mifegymiso®: <a href='http://www.nspharmacists.ca/wp-content/uploads/2017/06/Guidance_DispensingMifegymiso.pdf' target='_blank'>Practice Guidance: Dispensing Mifegymiso</a>.</li>
                </ul>
                
                <h3>Coverage Information</h3>
                <ul>
                    <li>Mifegymiso® may not be covered by most provincial drug plans, individuals will have to pay anywhere from $270 to $300 out of pocket, (Sept. 26th, 2016) <a href='http://www.shns.ca/?q=tags/mifegymiso' target='_blank'>read more</a></li>
                    <li>On September 22nd, 2017 government officials from the province of Nova Scotia  announced universal coverage of mifepristone, <a target='_blank' href='http://www.cbc.ca/news/canada/nova-scotia/nova-scotia-abortion-no-referral-pill-mifegymiso-1.4301943'>read more</a></li>
                </ul>
                
                <h3>Shipment/Distributing Information</h3>
                <ul>
                    <li>As of Jan 28th, 2017, Canadian distributor Celopharma has not reported shipments here to date</li>
                </ul>
            </div>
            
            <h2 id='NU'>Nunavut</h2>
            <div>
                <h3>Physician Regulations</h3>
                <ul>
                    <li>See information under 'National Regulations'</li>
                </ul>
                
                <h3>Pharmacist Regulations</h3>
                <ul>
                    <li>See information under 'National Regulations'</li>
                </ul>
                
                <h3>Coverage Information</h3>
                <ul>
                    <li>See information under 'National Regulations'</li>
                </ul>
                
                <h3>Shipment/Distributing Information</h3>
                <ul>
                    <li>As of Jan. 28, 2017, Canadian distributor Celopharma has not reported shipments here to date</li>
                </ul>
            </div>
            
            <h2 id='ON'>Ontario</h2>
            <div>
                <h3>Physician Regulations</h3>
                <ul>
                    <li>The College of Physicians and Surgeons of Ontario has issued guidance for its members, <a href='http://www.cpso.on.ca/Policies-Publications/Positions-Initiatives/Mifegymiso' target='_blank'>click here</a> for details</li>
                </ul>
                
                <h3>Pharmacist Regulations</h3>
                <ul>
                    <li>On March 7th, 2017, the Ontario College of Pharmacists published guidelines to support the practice of pharmacy professionals dispensing Mifegymiso® in Ontario, joining Bristish Columbia <a href='http://www.ocpinfo.com/library/practice-related/download/Dispensing_Mifegymiso.pdf' target='_blank'>read more</a></li>
                </ul>
                
                <h3>Coverage Information</h3>
                <ul>
                    <li>On April 27th, the Ontario Liberal government announced its provincial budget for 2017 which includes universal coverage of Mifegymiso®, read more from the media <a href='http://news.nationalpost.com/news/canada/free-abortion-pill-cash-for-boobs-and-babies-top-23-takeaways-from-the-ontario-budget' target='_blank'>here</a></li>
                    <li>The Ontario government has announced full coverage of Mifegymiso® to take effect by August 10th, 2017, <a href='http://www.cbc.ca/news/canada/toronto/abortion-pill-mifegymiso-1.4233611' target='_blank'>read more</a></li>
                    <li>Pharmacy billing procedures including reimbursement and claim submissions for Mifegymiso®, <a href='http://www.health.gov.on.ca/en/pro/programs/drugs/opdp_eo/eo_communiq.aspx' target='_blank'>click here</a></li>
                </ul>
                
                <h3>Shipment/Distributing Information</h3>
                <ul>
                    <li>Distributor in Ontario via Celopharma or wholesaler, McKesson <a href='https://www.mckesson.ca/mckesson-pharmaceutical' target='_blank'>read more</a></li>
                    <li>As of Feb. 24, 2017, Celopharma has shipped mifepristone to clinics in Toronto and Ottawa</li>
                    
                    <li>As of April 11th, 2017, in addition to Toronto and Ottawa, Celopharma has shipped mifepristone to Kingston</li>
                    
                </ul>
            </div>
            
            <h2 id='PE'>Prince Edward Island</h2>
            <div>
                <h3>Physician Regulations</h3>
                <ul>
                    <li>See information under 'National Regulations'</li>
                </ul>
                
                <h3>Pharmacist Regulations</h3>
                <ul>
                    <li>See information under 'National Regulations'</li>
                </ul>
                
                <h3>Coverage Information</h3>
                <ul>
                    <li>As reported in the media, Student Health Plans covering: UPEI (90% covered) and Holland College (80% covered) (Feb. 10th, 2017) <a href='http://www.cbc.ca/news/canada/prince-edward-island/abortion-drug-pill-mifegymiso-pei-doctors-pharmacists-1.3976558' target='_blank'>read more info</a></li>
                    <li>PEI is still exploring coverage options. As reported in the media, by mid-June, 22 physicians from PEI had completed the training, and another four had registered for the course, <a href='http://www.cbc.ca/news/canada/prince-edward-island/pei-mifegymiso-1.4225850' target='_blank'>read more</a></li>
                </ul>
                
                <h3>Shipment/Distributing Information</h3>
                <ul>
                    <li>As of Jan 28th, 2017, Canadian distributor Celopharma has not reported shipments here to date</li>
                </ul>
            </div>
            
            <h2 id='QC'>Quebec</h2>
            <div>
                <h3>Physician Regulations</h3>
                <ul>
                    <li>For information on the jurisdictional requirements of prescribing and dispensing mifepristone in Quebec, <a href='http://www.cmq.org/nouvelle/en/pilule-abortive-directives-cliniques.aspx' target='_blank'>click here</a>.</li>
                </ul>
                
                <h3>Pharmacist Regulations</h3>
                <ul>
                    <li>For information on the jurisdictional requirements of prescribing and dispensing mifepristone in Quebec, <a href='http://www.cmq.org/nouvelle/en/pilule-abortive-directives-cliniques.aspx' target='_blank'>click here</a>.</li>
                </ul>
                
                <h3>Coverage Information</h3>
                <ul>
                    <li>Public coverage of Mifegymiso® is currently undergoing an evaluation process (Feb. 6th, 2017) <a href='http://www.cmq.org/nouvelle/en/recommandations-interimaires-bon-usage-mifegymiso.aspx'>read more</a></li>
                    <li>On July 6th, Quebec became the 4th province in Canada to announce full coverage of Mifegymiso®! To read more, <a href='http://www.msss.gouv.qc.ca/documentation/salle-de-presse/ficheCommunique.php?id=1359' target='_blank'>click here</a></li>
                    <li>As reported in the media on December 15th, mifepristone became available in the province of <a href='http://montrealgazette.com/news/local-news/abortion-pill-will-be-available-in-quebec-as-of-dec-15' target='_blank'>Quebec</a> - for free!</li>
                </ul>
                
                <h3>Shipment/Distributing Information</h3>
                <ul>
                    <li>As of Jan 28th, 2017, Canadian distributor Celopharma has not reported shipments here to date</li>
                </ul>
            </div>
            
            <h2 id='SK'>Saskatchewan</h2>
            <div>
                <h3>Physician Regulations</h3>
                <ul>
                    <li>See information under 'National Regulations'</li>
                </ul>
                
                <h3>Pharmacist Regulations</h3>
                <ul>
                    <li>The College of Pharmacists of Saskatchewan has developed guidelines that stipulate it is within the scope of practice of pharmacy professionals in Saskatchewan to dispense medications directly to patients, <a href='https://scp.in1touch.org/document/3774/Mifegymiso_Guidance_FINAL.pdf' target='_blank'>read more</a></li>
                </ul>
                
                <h3>Coverage Information</h3>
                <ul>
                    <li>On August 31st, 2017, Mifegymiso was added to <a href='http://formulary.drugplan.ehealthsask.ca/default.aspx' target='_blank'>The Saskatchewan Formulary</a>, meaning the cost charged to a patient will vary based on individual coverage and eligibility through benefit programs, <a href='http://www.saskatchewan.ca/government/news-and-media/2017/august/31/mifegymiso-added-to-formulary' target='_blank'>read more</a></li>
                </ul>
                
                <h3>Shipment/Distributing Information</h3>
                <ul>
                    <li>As reported in the media on March 6th, 2017, Mifegymiso® has been shipped to Saskatoon, <a href='http://globalnews.ca/news/3291520/abortion-pill-mifegymiso-starts-to-arrive-in-saskatchewan/' target='_blank'>read more</a></li>
                </ul>
            </div>
            
            <h2 id='YT'>Yukon</h2>
            <div>
                <h3>Physician Regulations</h3>
                <ul>
                    <li>See information under 'National Regulations'</li>
                </ul>
                
                <h3>Pharmacist Regulations</h3>
                <ul>
                    <li>See information under 'National Regulations'</li>
                </ul>
                
                <h3>Coverage Information</h3>
                <ul>
                    <li>See information under 'National Regulations'</li>
                </ul>
                
                <h3>Shipment/Distributing Information</h3>
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
