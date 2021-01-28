<?php

$dir = dirname(__FILE__) . '/';
$wgSpecialPages['WhoSupplies'] = 'WhoSupplies'; # Let MediaWiki know about the special page.
$wgExtensionMessagesFiles['WhoSupplies'] = $dir . 'WhoSupplies.i18n.php';
$wgSpecialPageGroups['WhoSupplies'] = 'network-tools';

class WhoSupplies extends SpecialPage{

    function __construct() {
        parent::__construct("WhoSupplies", null, true);
    }
    
    function userCanExecute($user){
        $person = Person::newFromUser($user);
        return $person->isLoggedIn();
    }

    function execute($par){
        global $wgOut, $wgUser, $wgServer, $wgScriptPath, $wgTitle, $wgMessage, $wgLang;
        $wgOut->addHTML("<p><i>Click each province and territory to find out more information about the shipment/distribution of MIFE, practitioner specific guidelines/regulations and, if MIFE is covered in your region.</i></p>");
        $wgOut->addHTML("<div id='map' width='100%'></div>");
        $wgOut->addHTML("<script type='text/javascript' src='$wgServer$wgScriptPath/extensions/Caps/WhoSupplies/map.js'></script>");
        if($wgLang->getCode() == "en"){
        $wgOut->addHTML("<div id='accordion'>
            <h2>National Regulations</h2>
            <div>
                <h3>Physician Regulations</h3>
                <ul>
                    <li>There is no longer any requirement for a health care professional to witness a person taking either of the drugs in Mifegymiso, <a href='https://www.caps-cpca.ubc.ca/index.php/Special:LatestNews?pdf=2'>read more</a></li>
                    <li>People are not required to present a signed consent form to a pharmacist when filling a Rx for Mife, <a href='https://www.caps-cpca.ubc.ca/index.php/Special:LatestNews?pdf=3' >read more</a></li>
                    <li>On May 18th, 2017 Celopharma, in collaboration with Health Canada, issued communication to clarify the different requirements to prescribe, order, stock, and/or dispense Mifegymiso®, <a href='{$wgServer}{$wgScriptPath}/data/HPRC_Mifegymiso_{$wgLang->getCode()}-signed.pdf'>read more</a></li>
                    <li>On May 18th, Health Canada released a Dear Healthcare Professional Letter to clarify the different requirements associated with Mifegymiso®, <a href='http://healthycanadians.gc.ca/recall-alert-rappel-avis/hc-sc/2017/63330a-eng.php' target='_blank'>read more</a></li>
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
                    
                    
                    <li>As of May 2017,the NIHB will provide full coverage of Mifegymiso® for First Nations and Inuit women, <a href='https://www.caps-cpca.ubc.ca/index.php/Special:LatestNews?pdf=12'>read more</a></li>
                    <li><a href='https://www.caps-cpca.ubc.ca/index.php/File:Canadian_CPhA_Mifegymiso_access_coverage_advocacy_KT_110817.pdf'>INFOGRAPHIC MAP</a> of Mifegymiso® coverage across Canada.  The French Version is available <a href='https://www.caps-cpca.ubc.ca/AnnokiUploadAuth.php/4/41/Canadian_Mifegymiso_advocacy_version_Fr.pdf'>here</a></li>
                    <li>
                    In March 2018, The National Defense and Canadian Armed Forces added Mifegymiso to their <a href='http://www.cmp-cpm.forces.gc.ca/hs/en/drug-benefit-list/index.asp'>Drug Benefit </a>List for people covered by Canadian Forces Health Services.
                    </li>
                    <li>
                    <a href='https://www.caps-cpca.ubc.ca/AnnokiUploadAuth.php/4/4c/Canadian_MifeFederalPatientsInfoSheet_March2018.pdf'>Correctional Services of Canada</a> have announced they will also be listing Mifegymiso® to their formulary as of April 1st, 2018!

                    </li>
                The <a href='http://www.mifegymiso.com/interim-federal-health-program'>Interim Federal Health Program (IFHP)</a> provides full coverage for protected persons, including resettled refugees and refugee claimants in those provinces that have added Mifegymiso® to their formulary (i.e. AB, BC, MB, NB, NS, QC & SK, not ON, NFLD or PEI or the Territories)


                </li>
                    
                </ul>
                
                <h3>Shipment/Distributing Information</h3>
                <ul>
                    <li>The approval of Mifegymiso® has exposed both the legal and practical deficiencies of Health Canada's drug review process. Read more on this analysis <a href='http://policyoptions.irpp.org/magazines/april-2017/regulatory-risk-mismanagement-and-the-abortion-pill/' target='_blank'>here</a> (April 25th, 2017)</li>
                </ul>
                <h3>Nurse Practitioner Regulations</h3>
                <ul>
                <li>
                As of February 2018, The College of Nurses of Ontario, Yukon, Alberta,  Nova Scotia, and British Columbia have announced their support of NP prescribing (for more information, please click on each province on the map.)

                </li>
                <li>In those provinces whose nursing regulatory bodies have announced their support of NP prescribing of Mifegymiso® (AB, BC, ON, NS & YK), NPs should consider completing the <a href='https://sogc.org/online-courses/courses.html/event-info/details/id/229' target='_blank'> Non-Accredited Medical Abortion Training e-learning program</a> developed for health professionals
                </li>
                <li>On November 7th, 2017, Health Canada modified the Mifegymiso® <a href='http://healthycanadians.gc.ca/recall-alert-rappel-avis/hc-sc/2017/65030a-eng.php' target='_blank'>product monograph</a>.
                        <b>Language has been changed to “health professional” rather than “physician”</b>, accounting for the appropriate prescribing and dispensing scope of practice of among pharmacist, nurse practitioner, nursing and midwifery groups.”</li>
                </ul>
                <h3>Midwife Regulations</h3>
                <ul>
                <li>On November 7th, 2017, Health Canada modified the Mifegymiso® <a href='http://healthycanadians.gc.ca/recall-alert-rappel-avis/hc-sc/2017/65030a-eng.php' target='_blank'>product monograph</a>.
                        <b>Language has been changed to “health professional” rather than “physician”</b>, accounting for the appropriate prescribing and dispensing scope of practice of among pharmacist, nurse practitioner, nursing and midwifery groups.”</li>
                </ul>

                <h3>Tips on local initiatives to support your practice</h3>
                <ul>
                    <li>Click on your province!</li>
                </ul>

                <h3>Billing Codes</h3>
                <ul>
                    <li>Please click on your province to find out more information regarding mifepristone medical abortion billing codes. Have something to add? Let us know! <a href='mailto:cart.grac@ubc.ca'>Email cart.grac@ubc.ca</a></li>
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
                <h3>Nurse Practitioner Regulations</h3>
                <ul>

                    <li>
                    The <a href='http://www.nurses.ab.ca/content/carna/home/about/what-is-carna/news/07-24-2017.html
'>College of Registered Nurses of Alberta </a> announced on July 24th, 2017 that they would support NP provision of Mifegymiso® once Health Canada changed national regulations around “physician-only” prescribing and dispensing, <a href='http://www.nurses.ab.ca/content/carna/home/about/what-is-carna/news/07-24-2017.html
'>read more</a>
                    </li>
        
                </ul>
                <h3>Midwife Regulations</h3>
                <ul>
                    <li>See information under 'National Regulations'</li>
                </ul>
                <h3>Tips on local initiatives to support your practice</h3>
                <ul>
                <li>
                    What’s out there? Let us know! <a href='mailto:cart.grac@ubc.ca'>Email cart.grac@ubc.ca</a>
                </li>
                </ul>

                <h3>Billing Codes</h3>
                <ul>
                    <li>As of May 10th, 2018, the province of AB does not have a Mife billing code. Physicians may use the following to support their practice:</li>
                    <li>04A: Initial Visit</li>
                    <li>03A: Follow-up Visit</li>
                    <li>Alberta Health Care Insurance Plan, Schedule of Medical Benefits, <a target='_blank' href='https://open.alberta.ca/dataset/376dc12c-5bbb-494e-810b-ad3a6e13874a/resource/af9138e1-bb69-4ca8-a135-cbeffbae3f42/download/somb-medical-procedures-2017-04.pdf'>click here</a></li>
                </ul>
                
            </div>
            
            <h2 id='BC'>British Columbia</h2>
            <div>
                <h3>Physician Regulations</h3>
                <ul>
                    <li>
                    As of January 2018, Nurse Practitioners in BC may prescribe Mifegymiso® (read more). The College of Registered Nurses of British Columbia has not yet published supporting documents.
                    </li>
                    <li>Both the <a href='https://www.cpsbc.ca/for-physicians/college-connector/2016-V04-06/01' target='_blank'>College of Physicians and Surgeons of BC</a> and the <a href='http://www.bcpharmacists.org/mifegymiso' target='_blank'>College of Pharmacists of BC</a> have provided guidance to their members on dispensing mifepristone</li>
                    <li>In August 2018, the government of BC published <a href='https://www2.gov.bc.ca/assets/gov/health/practitioner-pro/bc-guidelines/ultrasound-summary.pdf' target='_blank'>Ultrasound Prioritization Guidelines</a>. Medical abortion was assigned Priority Level 2, i.e. maximum 7 calendar days.</li>
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
                <h3>Nurse Practitioner Regulations</h3>
                <ul>
                <li>
                As of January 2018, Nurse Practitioners in BC may prescribe Mifegymiso® <a href='https://archive.news.gov.bc.ca/releases/news_releases_2017-2021/2018HLTH0001-000003.htm' target='_blank'>read more</a>. The <a href='https://www.crnbc.ca/Pages/Default.aspx' target='_blank'>College of Registered Nurses of British Columbia</a>has not yet published supporting documents. 
                </li>
                </ul>
                <h3>Midwife Regulations</h3>
                <ul>
                    <li>See information under 'National Regulations'</li>
                </ul>
                <h3>Tips on local initiatives to support your practice</h3>
                <ul>
                <li>
                <a href='https://www.optionsforsexualhealth.org/clinic-services/opt-clinics'>Options for Sexual Health (Opt)</a> is a non-profit sexual health organization with 60 clinics across B.C. Clinics offer pregnancy evaluation including pregnancy testing, options counselling, and diagnosis. Not sure where to refer your patient? Connect with Opt.
                </li>
                <li>
                <a href='http://www.bcwomens.ca/health-professionals/professional-resources/abortion-contraception-resources'>The Pregnancy Options Service (POS)</a> can assist health care professionals across BC to help and support women in their communities who are facing an unintended or unplanned pregnancy. POS can provide up-to-date information about available abortion services throughout BC. Click on POS to view their contact information. 
-Please hyperlink Pregnancy Options Service (POS)
                </li>
                </ul>

                <h3>Billing Codes</h3>
                <ul>
                    <li>
                    T14545: Medical Abortion, includes all associated services rendered on the same day as the abortion, including the consultation, required components for Rh factor, associated services including counselling provided on the day of the procedure, and any medically necessary clinical imaging. Billing Amount: $159.77
                    </li>
                    <li>Ministry Of Health Medical Services Commission Payment Schedule, see page “7-15” <a href='https://www2.gov.bc.ca/assets/gov/health/practitioner-pro/medical-services-plan/msc-payment-schedule-december-2016.pdf'>click here</a></li>
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
                    <li>
                    As announced in the <a href='http://www.cbc.ca/news/canada/manitoba/mifegymiso-manitoba-accessibility-1.4473206'>media
                    </a>, as of January 2018, Mifegymiso® will be covered by Manitoba Pharmacare, after annual deductions have been met, <a href='http://www.mifegymiso.com/cost-coverage-statement/'>read more</a>
                    </li>
                    <li>As reported in the media on March 9th, 2017, Winnipeg Regional Health Authority has confirmed Mifegymiso® is now available at Health Sciences Centre's Women's Hospital, but patients must pay $350, <a href='http://www.cbc.ca/beta/news/canada/manitoba/abortion-pill-available-at-hsc-in-winnipeg-but-patients-will-pay-1.4016440' target='_blank'>read more</a></li>
                        <li>On July 20th, 2017 government officials from the province of Manitoba announced that mifepristone will be covered at select sites, <a href='http://www.cbc.ca/news/canada/manitoba/manitoba-to-cover-cost-of-abortion-pill-mifegymiso-at-approved-centres-1.4214354' target='_blank'>read more</a></li>
                        <li>
                            On June 1, 2019, the Manitoban government announced its plans to expand no-cost coverage to individuals across the province. <a href='https://nationalpost.com/news/canada/minister-says-manitoba-to-provide-universal-access-to-abortion-pill-mifegymiso' target='_blank'>Read more</a>
                        </li>
                </ul>
                
                <h3>Shipment/Distributing Information</h3>
                <ul>
                    <li>As of Feb. 24, 2017, Celopharma has shipped mifepristone to clinics in Winnipeg</li>
                </ul>
                <h3>Nurse Practitioner Regulations</h3>
                <ul>
                    <li>See information under 'National Regulations'</li>
                </ul>
                <h3>Midwife Regulations</h3>
                <ul>
                    <li>See information under 'National Regulations'</li>
                </ul>
                <h3>Tips on local initiatives to support your practice</h3>
                <ul>
                <li>
                What’s out there? Let us know! <a href='mailto:cart.grac@ubc.ca'>Email cart.grac@ubc.ca</a>
                </li>
                </ul>

                <h3>Billing Codes</h3>
                <ul>
                    <li>
                    8428: Medical Management of Early Pregnancy Failure/Elective Pregnancy Termination, including examination, assessment, the taking of cytological smears for cancer screening–cervix, management and monitoring of patients taking cytotoxic and/or prostglandin medications (e.g. Methotrexate/Misoprostol). This service may include administration of the medication, ordering blood tests, interpreting results, inquiring into possible complications and adjusting dosage(s) as necessary. Billing Amount: $167.65
                    </li>
                    <li>The Manitoba Physicians Manual, see page ‘B-5’, <a href='https://www.gov.mb.ca/health/documents/physmanual.pdf'>click here</a></li>
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
                <h3>Nurse Practitioner Regulations</h3>
                <ul>
                    <li>See information under 'National Regulations'</li>
                </ul>
                <h3>Midwife Regulations</h3>
                <ul>
                    <li>See information under 'National Regulations'</li>
                </ul>
                <h3>Tips on local initiatives to support your practice</h3>
                <ul>
                <li>
                What’s out there? Let us know! <a href='mailto:cart.grac@ubc.ca'>Email cart.grac@ubc.ca</a>
                </li>
                </ul>

                <h3>Billing Codes</h3>
                <ul>
                <li>
                Not Available for Mifepristone
                </li>
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
                    <li>On July 19th, 2018, government officials from the province of Newfoundland & Labrador announced universal coverage of mifepristone in coming to their province, this September <a target='_blank' href='https://www.cbc.ca/news/canada/newfoundland-labrador/mifegymiso-universal-coverage-1.4751509'>read more</a></li>
                </ul>
                
                <h3>Shipment/Distributing Information</h3>
                <ul>
                    <li>As of April 11th, 2017, Celopharma has shipped mifepristone to St. John’s</li>
                </ul>
                <h3>Nurse Practitioner Regulations</h3>
                <ul>
                    <li>See information under 'National Regulations'</li>
                </ul>
                <h3>Midwife Regulations</h3>
                <ul>
                    <li>See information under 'National Regulations'</li>
                </ul>
                 <h3>Tips on local initiatives to support your practice</h3>
                <ul>
                    <li>Patients with an MCP card can get a Mifegymiso prescription through their doctor, through regional health authorities or through the <a target='_blank' href='https://www.thrivecyn.ca/directory-of-services/health/athena-health-centre-formerly-the-morgentaler-clinic/'>Athena Clinic</a>, located in St. John's</li>
                    <li>What’s out there? Let us know! <a href='mailto:cart.grac@ubc.ca'> Email cart.grac@ubc.ca</a></li>
                </ul>

                <h3>Billing Codes</h3>
                <ul>
                <li>
                Not Available for Mifepristone
                </li>
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
                <h3>Nurse Practitioner Regulations</h3>
                <ul>
                    <li>See information under 'National Regulations'</li>
                </ul>
                <h3>Midwife Regulations</h3>
                <ul>
                    <li>See information under 'National Regulations'</li>
                </ul>
           
            <h3>Tips on local initiatives to support your practice</h3>
                <ul>
                <li>
                What’s out there? Let us know! <a href='mailto:cart.grac@ubc.ca'> Email cart.grac@ubc.ca</a>
                </li>
                </ul>

                <h3>Billing Codes</h3>
                <ul>
                <li>
                Not Available for Mifepristone
                </li>
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
                <h3>Nurse Practitioner Regulations</h3>
                <ul>
                    <li>
                    As of December 6th, 2017, the College of Registered Nurses of Nova Scotia has determined that it is within the scope of practice of NPs who have the appropriate knowledge, skills and judgment to prescribe Mifegymiso®, <a target='_blank' href='https://crnns.ca/publication/np-bulletin-prescribing-mifegymiso-in-nova-scotia/'>read more</a>
                    </li>
                
                </ul>
                <h3>Midwife Regulations</h3>
                <ul>
                    <li>See information under 'National Regulations'</li>
                </ul>
                <h3>Tips on local initiatives to support your practice</h3>
                <ul>
                <li>
                The Nova Scotia Health Authority has created a toll-free phone line so women can access information, arrange testing, and make an appointment with a community-based physician for a medical or surgical abortion at the QEII Health Sciences Centre in Halifax, <a href='http://nationalpost.com/pmn/news-pmn/canada-news-pmn/nova-scotia-launches-toll-free-phone-line-for-women-considering-an-abortion'>read more</a>
                </li>
                </ul>

                <h3>Billing Codes</h3>
                <ul>
                    <li>As of May 23rd, 2018, the province of NS has officially announced a new billing code for mifepristone medical abortion, <a href='https://www.ctvnews.ca/health/abortion-pill-to-become-more-widely-available-in-n-s-after-new-billing-code-adopted-1.3942030' target='_blank'>read more</a>.</li>
                    <li>03.03V Comprehensive fee for the initial visit for a medical abortion: Billing amount, $120.00</li>
                    <li>03.03 Follow up visits can be billed as regular office visits</li>
                    <li>Nova Scotia MSI Billing Physicians Manual, <a href='https://doctorsns.com/contract-and-support/billing/msi' target='_blank'>click here</a></li>
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
                <h3>Nurse Practitioner Regulations</h3>
                <ul>
                    <li>See information under 'National Regulations'</li>
                </ul>
                <h3>Midwife Regulations</h3>
                <ul>
                    <li>See information under 'National Regulations'</li>
                </ul>
                <h3>Tips on local initiatives to support your practice</h3>
                <ul>
                    <li>What’s out there? Let us know! <a href='mailto:cart.grac@ubc.ca'> Email cart.grac@ubc.ca</a></li>
                </ul>

                <h3>Billing Codes</h3>
                <ul>
                    <li>Not currently available for mifepristone.</li>
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
                    <li>There is no out of province allowance or reciprocal billing arrangement for any drug covered under the Ontario Drug Benefit or Ontario Public Drug Programs therefore, mifepristone prescriptions have to be filled by a pharmacy in Ontario to be covered.</li>
                </ul>
                
                <h3>Shipment/Distributing Information</h3>
                <ul>
                    <li>Distributor in Ontario via Celopharma or wholesaler, McKesson <a href='https://www.mckesson.ca/mckesson-pharmaceutical' target='_blank'>read more</a></li>
                    <li>As of Feb. 24, 2017, Celopharma has shipped mifepristone to clinics in Toronto and Ottawa</li>
                    
                    <li>As of April 11th, 2017, in addition to Toronto and Ottawa, Celopharma has shipped mifepristone to Kingston</li>
                    
                </ul>
                <h3>Nurse Practitioner Regulations</h3>
                <ul>
                    <li>
                    In July 2017, the College of Nurses of Ontario 
                        <a href='http://www.cno.org/en/news/2017/july-2017/what-nps-should-know-about-mifegymiso/' target='_blank'>announced their support</a> of NP prescribing of Mifegymiso®,<a href='http://www.cno.org/en/news/2017/july-2017/what-nps-should-know-about-mifegymiso/' target='_blank'>read more</a></li>


                    </li>
                    <li>
                    In December 2017, the College of Nurses of Ontario <a href='http://www.cno.org/en/learn-about-standards-guidelines/magazines-newsletters/the-standard/december-2017/Mifegymiso-update/' target='_blank'>published an update</a> to its members clarifying that Health Canada increased the number of days Mifegymiso can be provided to a client; Mifegymiso® can now be provided up to 63 days from the start of the last menstrual period, <a href='http://www.cno.org/en/learn-about-standards-guidelines/magazines-newsletters/the-standard/december-2017/Mifegymiso-update/' target='_blank'>read more</a></li>

                    </li>
                </ul>
                <h3>Midwife Regulations</h3>
                <ul>
                    <li>See information under 'National Regulations'</li>
                </ul>
                <h3>Tips on local initiatives to support your practice</h3>
                <ul>
                <li>
                <a href='https://ppottawa.ca/programs/counselling/abortion/'>Planned Parenthood Ottawa</a> is a non-profit organization, well positioned to facilitate a local community of practice in the region. <a href='https://www.caps-cpca.ubc.ca/index.php/Special:StoryManagePage#/3'> Join their discussion!</a>
                </li>
                </ul>

                <h3>Billing Codes</h3>
                <ul>
                <li>
                    <b>A920: Medical Management of Early Pregnancy</b>
                    <ul>
                        <li>initial service when a physician renders an initial assessment and administration of cytotoxic medication(s) for the termination of early pregnancy or missed abortion. The cost of the drug(s) is not included in the fee for the service.</li>
                        <li>Payment rules: Services described as consultations, assessments or counselling (and those procedures that are generally accepted components of this service) are not eligible for payment when rendered the same day to the same patient by the same physician as A920</li>
                        <li>Billing Amount, $161.15</li>
                    </ul>
                </li>
                <li>
                    <b>A921: Medical Abortion Follow-Up</b>
                    <ul>
                        <li>confirm that abortion is complete, review contraception.</li>
                        <li>Billing Amount, $33.70</li>
                    </ul> 
                </li>
                <li>
                Schedule of Benefits, Physician Services Under the Health Insurance Act, see page “K4”, <a href='http://www.health.gov.on.ca/en/pro/programs/ohip/sob/physserv/sob_master20160401.pdf
'>click here</a>
                </li>
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
                <h3>Nurse Practitioner Regulations</h3>
                <ul>
                    <li>See information under 'National Regulations'</li>
                </ul>
                <h3>Midwife Regulations</h3>
                <ul>
                    <li>See information under 'National Regulations'</li>
                </ul>
                 <h3>Tips on local initiatives to support your practice</h3>
                <ul>
                <li>
                What’s out there? Let us know! <a href='mailto:cart.grac@ubc.ca'> Email cart.grac@ubc.ca</a>
                </li>
                </ul>

                <h3>Billing Codes</h3>
                <ul>
                <li>
                Not Available for Mifepristone
                </li>
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
                <h3>Nurse Practitioner Regulations</h3>
                <ul>
                    <li>See information under 'National Regulations'</li>
                </ul>
                <h3>Midwife Regulations</h3>
                <ul>
                    <li>See information under 'National Regulations'</li>
                </ul>
                 <h3>Tips on local initiatives to support your practice</h3>
                <ul>
                <li>
                What’s out there? Let us know! <a href='mailto:cart.grac@ubc.ca'> Email cart.grac@ubc.ca</a>
                </li>
                </ul>

                <h3>Billing Codes</h3>
                <ul>
                    <li>15313 - clinic: global evaluation for medical abortion of a pregnancy of 63 days or less, including the visit, therapy and counseling - 133.35$</li>
                    <li>15314 - hospital: global evaluation for medical abortion of a pregnancy of 63 days or less, including the visit, therapy and counseling - 100.00$</li>
                    <li>15315 - clinic: ultrasonography for the evaluation of the medical abortion - $36.45</li>
                    <li>15316 - hospital: ultrasonography for the evaluation of the medical abortion - $12.50</li>
                    <li>15317 - clinic: follow-up visit to confirm the completion of medical abortion - $37.50</li>
                    <li>15318 - hospital: follow-up visit to confirm the completion of medical abortion - $28.15</li>
                    <li>15319 - clinic: ultrasonography for follow-up visit - $32.30</li>
                    <li>15320 - hospital: ultrasonography for follow-up visit - $8.35</li>
                    <li>For more information on remuneration, please <a target='_blank' href='https://www.caps-cpca.ubc.ca/index.php/File:Canadian_RAMQ_-_Tarification_IVG_m%C3%A9dicale_27_mars_2018.pdf'>click here</a></li>
                </li>
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
                <h3>Nurse Practitioner Regulations</h3>
                <ul>
                    <li>On October 18th, 2018, the Registered Nurses Association of Saskatchewan became the 8th province/territory to announce their support of NP provision of mifepristone medical abortion, to read more, <a href='https://www.srna.org/rnnp/prescribe-mifegymiso/' target='_blank'>click here</a></li>
                </ul>
                <h3>Midwife Regulations</h3>
                <ul>
                    <li>See information under 'National Regulations'</li>
                </ul>
                <h3>Tips on local initiatives to support your practice</h3>

                <h3>Billing Codes</h3>
                <ul>
                    <li>5P: Initial assessment, $56.00 (referred) or $44.75 (not referred)</li>
                    <li>7P: Follow-up assessment, $31.75</li>
                    <li>50P: Therapeutic abortion (surgical) in the first trimester. Billing Amount: $177.50</li>
                    <li>250P: Therapeutic abortion (includes incomplete and missed abortion) surgical abortions in the second trimester. Billing Amount: $230.00 (specialist) or $207.00 (general practitioner)</li>
                    <li>350P: D&C for incomplete or missed abortion. Billing Amount: $177.30</li>
                    <li>Prescribing or administering pharmaceutical agents such as Mifegymiso are included in the visit service and there is no additional billing as of October 2018, <a href='https://www.ehealthsask.ca/services/resources/Resources/billing-bulletin-oct-2018.pdf' target='_blank'>read more</a> (see page 8).</li>
                    <li>Saskatchewan Medical Abortion Fee Guide Schedule for Obstetrics and Gynecology, see ‘P4’, <a target='_blank' href='http://www.sma.sk.ca/kaizen/content/files/p(7).pdf'>click here</a> for 2017.  <a href='https://www.ehealthsask.ca/services/resources/Resources/physician-payment-schedule-oct-2018.pdf' target='_blank'>Click here</a> for 2018 and see \"page 239\".</li>
                </ul>
                
            </div>
            
            <h2 id='YT'>Yukon</h2>
            <div>
                <h3>Physician Regulations</h3>
                <ul>
                    <li> On July 6th, 2018, the Yukon Medical Council published a Mifegymiso® Guideline for physicians licensed to practice in the Yukon. To review the document, <a target='_blank' href='http://www.yukonmedicalcouncil.ca/pdfs/MIFE_Guideline.pdf'>click here</a>.</li>
                </ul>
                
                <h3>Pharmacist Regulations</h3>
                <ul>
                    <li>See information under 'National Regulations'</li>
                </ul>
                
                <h3>Coverage Information</h3>
                <ul>
                    <li>As of October 31st, 2018, the Government of Yukon is now offering universal coverage of Mifegymiso®, to read more, <a href='https://yukon.ca/en/news/government-yukon-decreases-barriers-accessing-medical-abortion-medication' target='_blank'>click here</a></li>
                </ul>
                
                <h3>Shipment/Distributing Information</h3>
                <ul>
                    <li>As of Feb. 24, 2017, Celopharma has shipped mifepristone to clinics in Whitehorse</li>
                </ul>
                <h3>Nurse Practitioner Regulations</h3>
                <ul>
                <li>
                Nurse Practitioners in the Yukon have an extended <a href='http://nperesource.casn.ca/wp-content/uploads/2017/01/NPFoundation.pdf'>scope of practice</a>. In July 2018, the <a href='https://www.canadian-nurse.com/articles/issues/2017/september-october-2017/cna-advocacy-at-the-summer-meeting-of-premiers'>Canadian Nurses Association </a>explicitly stated that NPs in the Yukon are authorized to prescribe Mifegymiso® in their jurisdiction
                </li>
                </ul>
                <h3>Midwife Regulations</h3>
                <ul>
                    <li>See information under 'National Regulations'</li>
                </ul>
                <h3>Tips on local initiatives to support your practice</h3>
                <ul>
                <li>
                What’s out there? Let us know! <a href='mailto:cart.grac@ubc.ca'> Email cart.grac@ubc.ca</a>
                </li>
                </ul>

                <h3>Billing Codes</h3>
                <ul>
                    <li>0101: Complete physical examination, $101.00</li>
                    <li>0100: Simple office visit, $50.70</li>
                    <li>4092: Pregnancy options counseling, $100.00</li>
                    <li>4111: Therapeutic abortion < 12 weeks/surgical, $269.00</li>
                    <li>4112: Therapeutic abortions >12 weeks/surgical, $521.00</li>
                    <li>4116: Medical abortion (includes methotrexate and mifepristone) for physicians and nurse practitioners, $207.70</li>
                    <li>Yukon Drug Formulary, <a href='http://apps.gov.yk.ca/drugs/' target='_blank'>click here</a></li>
                    <li>Yukon Physician Resources, pay schedule, <a href='http://apps.gov.yk.ca/physicianresources' target='_blank'>click here</a></li>
                </ul>
            </div>
        </div>
        
        <script type='text/javascript'>
            $('#accordion').accordion({
                autoHeight: false
            });
        </script>");
    }
    else if($wgLang->getCode() == "fr"){
            $wgOut->addHTML("<div id='accordion'>
            <h2>Règlements nationaux</h2>
            <div>
                <h3>
    Règlement sur les médecins</h3>
                <ul>
                    <li>Un professionnel de la santé n'a plus besoin d'être témoin d'une personne qui prend l'un ou l'autre des médicaments à Mifegymiso, <a href='https://www.caps-cpca.ubc.ca/index.php/Special:LatestNews?pdf=2'>Lire la suite</a></li>
                    <li>Les personnes ne sont pas tenues de présenter un formulaire de consentement signé à un pharmacien lors du remplissage d'un Rx pour Mife, <a href='https://www.caps-cpca.ubc.ca/index.php/Special:LatestNews?pdf=3' >
Lire la suite</a></li>
                    <li>
Le 18 mai 2017, Celopharma, en collaboration avec Santé Canada, a publié une communication visant à clarifier les différentes exigences pour prescrire, commander, stocker et / ou distribuer Mifegymiso®, <a href='{$wgServer}{$wgScriptPath}/data/HPRC_Mifegymiso_{$wgLang->getCode()}-signed.pdf'>
Lire la suite</a></li>
                    <li>
Le 18 mai, Santé Canada a publié une Lettre aux professionnels de la santé pour clarifier les différentes exigences associées à Mifegymiso®, <a href='http://healthycanadians.gc.ca/recall-alert-rappel-avis/hc-sc/2017/63330a-eng.php' target='_blank'>
Lire la suite</a></li>
                    <li>
Le 7 novembre 2017, Santé Canada a annoncé des changements importants aux processus de distribution, de prescription et de distribution de la mifépristone. Pour voir la communication de Santé Canada,<a href='http://healthycanadians.gc.ca/recall-alert-rappel-avis/hc-sc/2017/65030a-eng.php' target='_blank'>click here</a> Pour un résumé développé par l'équipe CAPS,<a href='https://www.caps-cpca.ubc.ca/index.php/Special:LatestNews'>click here</a></li>
                </ul>
                
                <h3>Pharmacist Regulations</h3>
                <ul>
                    <li>
Les personnes ne sont pas tenues de présenter un formulaire de consentement signé à un pharmacien lors du remplissage d'un Rx pour Mife, <a href='https://www.caps-cpca.ubc.ca/index.php/Special:LatestNews?pdf=3' >
Lire la suite</a></li>
                    <li>Santé Canada <a href='https://www.caps-cpca.ubc.ca/AnnokiUploadAuth.php/7/7a/Patients_Mifegymiso_Eng_guide_01_19_17.pdf' target='_blank'>Guide d'information sur le patient</a></li>
                    <li>Le 18 mai 2017, Celopharma, en collaboration avec Santé Canada, a publié une communication visant à clarifier les différentes exigences pour prescrire, commander, stocker et / ou distribuer Mifegymiso®,<a href='{$wgServer}{$wgScriptPath}/data/HPRC_Mifegymiso_{$wgLang->getCode()}-signed.pdf'>
Lire la suite</a></li>
                    <li> Le 7 novembre 2017, Santé Canada a annoncé que tous les pharmaciens au Canada pourraient distribuer Mifegymiso® directement aux patients. <a href='https://www.caps-cpca.ubc.ca/index.php/File:Canadian_CPhA_Mifegymiso_access_coverage_advocacy_KT_110817.pdf'> Cliquez ici </a> pour consulter une excellente ressource mise au point par nos partenaires de l'Association des pharmaciens du Canada </ li>
                </ ul>
                
                <h3> Informations sur la couverture </h3>
                <ul>
                    <li> À compter du 4 mars 2017, les fournisseurs d'assurance privés soucieux de la couverture de Mifegymiso® sont:
                        <ol>
                            <li> Great West Life: couverture ouverte </ li>
                            <li> Croix Bleue Medavie: couverture ouverte </ li>
                            <li> Manuvie: Couverture ouverte </ li>
                            <li> Pacific Blue Cross: En révision; une réponse attendue dans 1 mois </ li>
                            <li> Alberta Blue Cross: En révision; une réponse anticipée en 1 mois. </ li>
                            <li> TELUS: toujours en révision </ li>
                            <li> Greenshield: en revue </ li>
                        </ol>
                    </li>
                    <li> Le 18 avril 2017, le groupe d'experts du Programme commun d'évaluation des médicaments (PCEM) a approuvé la couverture publique de Mifegymiso® au Canada,
Lire la suite de la CDR <a href='https://www.cadth.ca/mifepristone-and-misoprostol-0' target='_blank'> ici </a> et / ou des médias <a href = 'http://www.theglobeandmail.com/news/national/expert-panel-endorses-public-coverage-for-abortion-pill-in-canada/article34757082/%20(April%2020th%202017)' target = '_blank'> ici </a> (19 avril 2017) </li>
                    <li>Click <a href='https://www.cadth.ca/sites/default/files/cdr/complete/SR0502_complete_Mifegymiso-Apr-20-17-e.pdf' target='_blank'>here</a> for the Canadian Agency for Drugs and Technologies in Health (CADTH) Common Drug Review of Mifegymiso, 'Final Recommendations and Reasons' report</li>
                    <li>The NIHB is expected to publicly announce full coverage of Mifegymiso® for First Nations and Inuit women, <a href='https://www.caps-cpca.ubc.ca/index.php/Special:LatestNews?pdf=12'>
Lire la suite</a></li>
                    <li><a href='https://www.caps-cpca.ubc.ca/index.php/File:Canadian_CPhA_Mifegymiso_access_coverage_advocacy_KT_110817.pdf'>Click here</a> for an infographic that shows Mifegymiso® coverage across Canada, as of January, 2018.</li>
                </ul>
                
                <h3>Envoi / Distribution des informations</h3>
                <ul>
                    <li>
L'approbation de Mifegymiso® a révélé les lacunes juridiques et pratiques du processus d'examen des médicaments de Santé Canada.
Lire la suite on this analysis <a href='http://policyoptions.irpp.org/magazines/april-2017/regulatory-risk-mismanagement-and-the-abortion-pill/' target='_blank'>here</a> (April 25th, 2017)</li>
                </ul>
            </div>

            <h2 id='AB'>Alberta</h2>
            <div>
                <h3>Règlement sur les médecins</h3>
                <ul>
                    <li>
Voir les informations sous «Réglementations nationales»</li>
                </ul>
                
                <h3>Règlement sur les pharmaciens</h3>
                <ul>
                    <li>Le 12 juin 2017, l'Alberta College of Pharmacists a publié des lignes directrices pour soutenir la pratique des professionnels de la pharmacie dispensant Mifegymiso®, <a href='https://pharmacists.ab.ca/guidelines-dispensing-mifegymiso' target='_blank'>
Lire la suite</a></li>
                    <li>Dr Nese Yuksel, pharmacien de l'Alberta, a écrit des informations supplémentaires pour les pharmaciens d'AB, <a href='https://secure.campaigner.com/CSB/public/ReadmoreContent.aspx?id=28435696&campaignid=21466857' target='_blank'>
Lire la suite</a></li>
                </ul>
                
                <h3>Information de couverture</h3>
                <ul>
                    <li>Comme indiqué dans les médias, Mifegymiso®, ~ 300 $ / dose (7 octobre 2016) <a href='http://edmontonjournal.com/news/politics/paula-simons-access-to-abortion-drug-one-step-closer-for-alberta-women' target='_blank'>
Lire la suite</a></li>
                    <li>Tel que rapporté dans les médias le 6 mars 2017, la clinique Kensington de Calgary fournit Mifegymiso® gratuitement, <a href='https://www.thestar.com/opinion/commentary/2017/03/06/abortion-pill-rollout-deeply-flawed-mallick.html' target='_blank'>
Lire la suite</a></li>
                    <li>Suite à la liste positive de l'examen d'experts canadiens de Mifegymiso, l'Alberta est devenue la deuxième province (après le Nouveau-Brunswick) à annoncer qu'elle offrira un accès universel au médicament, <a href='https://beta.theglobeandmail.com/news/national/alberta-promises-to-offer-universal-access-to-the-abortion-pill/article34768401/?ref=http://www.theglobeandmail.com&service=mobile' target='_blank'>
Lire la suite from the media</a> (April 20th, 2017)</li>
                    <li>
Le 24 juillet 2017, des représentants du gouvernement de la province de l'Alberta ont annoncé que la couverture de la mifépristone est maintenant pleinement opérationnelle dans leur province, <a href='http://www.timescolonist.com/alberta-latest-province-to-cover-the-cost-of-abortion-pill-mifegymiso-1.21338876' target='_blank'>
Lire la suite</a></li>
                </ul>
                
                <h3>Envoi / Distribution des informations</h3>
                <ul>
                    <li>
En date du 24 février 2017, Celopharma a expédié de la mifépristone à des cliniques à Calgary</li>
                </ul>
            </div>
            
            <h2 id='BC'>
Colombie britannique</h2>
            <div>
                <h3>Règlement sur les médecins</h3>
                <ul>
                    <li>
Les deux <a href='https://www.cpsbc.ca/for-physicians/college-connector/2016-V04-06/01' target='_blank'>Le Collège des médecins et chirurgiens de la Colombie-Britannique </a> et le <a href='http://www.bcpharmacists.org/mifegymiso' target='_blank'> Collège des pharmaciens de la Colombie-Britannique </a> ont fourni des conseils à leur membres sur la distribution de mifepristone </li>
                </ul>
                
                <h3> Règlement sur les pharmaciens </h3>
                <ul>
                    <li> Le <a href='https://www.cpsbc.ca/for-physicians/college-connector/2016-V04-06/01' target='_blank'> Collège des médecins et chirurgiens de la Colombie-Britannique </a> et le <a href='http://www.bcpharmacists.org/mifegymiso' target='_blank'> Collège des pharmaciens de la Colombie-Britannique </a> ont fourni des conseils à leurs membres sur la distribution de la mifépristone </li>
                    <li> Le 2 janvier 2018, PharmaNet a récemment publié un bulletin détaillant comment les pharmacies de la Colombie-Britannique pourront accéder à Mifegymiso subventionné. Pour plus d'informations, <a href='http://www.gov.bc.ca/pharmacarenewsletter' target='_blank'> cliquez ici </a> </li>
                </ul>
                
                <h3>Coverage Information</h3>
                <ul>
                    <li>As of January 15, 2018, the BC Ministry of Health PharmaCare program will provide Mifegymiso® at no charge to BC Residents through BC's community pharmacies, <a href='http://www.vancourier.com/news/free-access-to-abortion-pill-a-game-changer-for-b-c-women-1.23135464' target='_blank'>
Lire la suite</a></li>
                </ul>
                
                <h3>Shipment/Distributing Information</h3>
                <ul>
                    <li>As of January 15, 2018, mifepristone will be distributed by the BC Centre for Disease Control to pharmacies throughout British Columbia, <a href='https://www2.gov.bc.ca/assets/gov/health/health-drug-coverage/pharmacare/newsletters/news18-001.pdf' target='_blank'>
Lire la suite</a></li>
                </ul>
            </div>
            
            <h2 id='MB'>Manitoba</h2>
            <div>
                <h3>Règlement sur les médecins</h3>
                <ul>
                    <li>See information under 'National Regulations'</li>
                </ul>
                
                <h3>Pharmacist Regulations</h3>
                <ul>
                    <li>In June, 2017, the Manitoba College of Pharmacists approved guidelines for the distribution of Mifegymiso® in Manitoba, specifying that pharmacist's in Manitoba cannot dispense Mifegymiso® directly to a patient, <a target='_blank' href='http://www.cphm.ca/uploaded/web/Guidelines/Mifegymiso/Final%20Mifegymiso%20Guideline%20for%20Pharmacists.pdf'>
Lire la suite</a></li>
                    <li>On November 7th, 2017 Health Canada announced that all pharmacists across Canada may dispense Mifegymiso® directly to patients. <a href='https://www.caps-cpca.ubc.ca/index.php/File:Canadian_CPhA_Mifegymiso_access_coverage_advocacy_KT_110817.pdf'>Click here</a> to view an excellent resource developed by our partners at the Canadian Pharmacists Association.</li>
                </ul>
                
                <h3>Coverage Information</h3>
                <ul>
                    <li>As reported in the media on March 9th, 2017, Winnipeg Regional Health Authority has confirmed Mifegymiso® is now available at Health Sciences Centre's Women's Hospital, but patients must pay $350, <a href='http://www.cbc.ca/beta/news/canada/manitoba/abortion-pill-available-at-hsc-in-winnipeg-but-patients-will-pay-1.4016440' target='_blank'>
Lire la suite</a></li>
                    <li>On July 20th, 2017 government officials from the province of Manitoba announced that mifepristone will be covered at select sites, <a href='http://www.cbc.ca/news/canada/manitoba/manitoba-to-cover-cost-of-abortion-pill-mifegymiso-at-approved-centres-1.4214354' target='_blank'>
Lire la suite</a></li>
                    <li>Le 1er juin 2019, le gouvernement manitobain a annoncé son intention d'étendre la gratuité du Mifegymiso® à toutes les femmes de la province. <a href='https://www.translatetheweb.com/?from=&to=fr&ref=SERP&dl=fr&rr=UC&a=https%3a%2f%2fnationalpost.com%2fnews%2fcanada%2fminister-says-manitoba-to-provide-universal-access-to-abortion-pill-mifegymiso' target='_blank'>Lire la suite</a></li>
                </ul>
                
                <h3>Envoi / Distribution des informations</h3>
                <ul>
                    <li>
En date du 24 février 2017, Celopharma a expédié de la mifépristone à des cliniques à Winnipeg</li>
                </ul>
            </div>
            
            <h2 id='NB'>Nouveau-Brunswick</h2>
            <div>
                <h3>Règlement sur les médecins</h3>
                <ul>
                    <li>Voir les informations sous «Réglementations nationales»</li>
                </ul>
                
                <h3>Pharmacist Regulations</h3>
                <ul>
                    <li>Voir les informations sous «Réglementations nationales»</li>
                </ul>
                
                <h3>Information de couverture</h3>
                <ul>
                    <li> Tel que rapporté dans les médias, les plans de santé étudiants couvrant: St Thomas University, quote-part de 5 $ (13 février 2017) <a href = 'https: //www.lifesitenews.com/news/for-just- a-5-co-pay-abortion-drug-will-be-disponible-aux-étudiants-at-st '-om' 'target =' _ blank '>
Lire la suite </a> </li>
                    <li> Tel que rapporté dans les médias, le gouvernement provincial du Nouveau-Brunswick est la première province / territoire au Canada à annoncer une couverture universelle pour la mifépristone (le 4 avril 2017), <a href = 'http: //www.cbc.ca/ nouvelles / canada / new-brunswick / abortion-pill-new-brunswick-1.4054517 'target =' _ blank '>
Lire la suite </a> </ li>
                    <li> Le 7 juillet, le ministre de la Santé du Nouveau-Brunswick a annoncé que le programme provincial d'accès universel à la mifépristone est officiellement opérationnel et accessible aux Néo-Brunswickois, <a href = 'http: //www2.gnb.ca/ content / gnb / fr / news / news_release.2017.07.0952.html 'target =' _ blank'>
Lire la suite </a> ici. </li>
                </ul>
                
                <h3> Informations sur l'expédition / la distribution </h3>
                <ul>
                    <li> Tel que rapporté dans les médias, Mifepristone ne fait pas de percée au Nouveau-Brunswick; seulement 14 médecins se sont inscrits à la formation sur l'avortement médicamenteux (27 février 2017) <a href = 'http: //www.cbc.ca/news/canada/new-brunswick/mifegymiso-canada-health-doctors-1.4000643' target = '_ blank'>
Lire la suite </a> </li>
                </ul>
            </div>
            
            <h2 id='NL'>Newfoundland and Labrador</h2>
            <div>
                <h3>Règlement sur les médecins</h3>
                <ul>
                    <li>Voir les informations sous «Réglementations nationales»</li>
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
                <h3>Règlement sur les médecins</h3>
                <ul>
                    <li>Voir les informations sous «Réglementations nationales»</li>
                </ul>
                
                <h3>Règlement sur les pharmaciens</h3>
                <ul>
                    <li>Voir les informations sous «Réglementations nationales»</li>
                </ul>
                
                <h3>Information de couverture</h3>
                <ul>
                    <li>Voir les informations sous «Réglementations nationales»</li>
                </ul>
                
                <h3>Envoi / Distribution des informations</h3>
                <ul>
                    <li>En date du 28 janvier 2017, le distributeur canadien Celopharma n'a pas déclaré d'expédition à ce jour</li>
                </ul>
            </div>
            
            <h2 id='NS'>Nouvelle-Écosse</h2>
            <div>
                <h3>Règlement sur les médecins</h3>
                <ul>
                    <li>Le Collège des médecins et chirurgiens de la Nouvelle-Écosse est devenu la troisième province après la Colombie-Britannique et l'Ontario à annoncer leur appui à la distribution de Mifegymiso® par les pharmaciens, <a href='https://www.cpsns.ns.ca/DesktopModules/Bring2mind/DMX/Download.aspx?PortalId=0&TabId=129&EntryId=293' target='_blank'>
Lire la suite</a>.</li>
                </ul>
                
                <h3>Règlement sur les pharmaciens</h3>
                <ul>
                    <li>Le Collège des pharmaciens de la Nouvelle-Écosse a publié les lignes directrices suivantes à l'intention des pharmaciens et des techniciens en pharmacie lors de la distribution de Mifegymiso®: <a href='http://www.nspharmacists.ca/wp-content/uploads/2017/06/Guidance_DispensingMifegymiso.pdf' target='_blank'>Practice Guidance: Dispensing Mifegymiso</a>.</li>
                </ul>
                
                <h3>Information de couverture</h3>
                <ul>
                    <li>
Mifegymiso® peut ne pas être couvert par la plupart des régimes d'assurance-médicaments provinciaux, les particuliers devront débourser de 270 $ à 300 $ de leur poche (26 septembre 2016) <a href='http://www.shns.ca/?q=tags/mifegymiso' target='_blank'>
Lire la suite</a></li>
                    <li>
Le 22 septembre 2017, les représentants du gouvernement de la Nouvelle-Écosse ont annoncé la couverture universelle de la mifépristone, <a target='_blank' href='http://www.cbc.ca/news/canada/nova-scotia/nova-scotia-abortion-no-referral-pill-mifegymiso-1.4301943'>
Lire la suite</a></li>
                </ul>
                
                <h3>Envoi / Distribution des informations</h3>
                <ul>
                    <li>Au 28 janvier 2017, le distributeur canadien Celopharma n'a pas déclaré d'expédition à ce jour</li>
                </ul>
            </div>
            
            <h2 id='NU'>Nunavut</h2>
            <div>
                <h3>Règlement sur les médecins</h3>
                <ul>
                    <li>Voir les informations sous «Réglementations nationales»</li>
                </ul>
                
                <h3>Pharmacist Regulations</h3>
                <ul>
                    <li>Voir les informations sous «Réglementations nationales»</li>
                </ul>
                
                <h3>Information de couverture</h3>
                <ul>
                    <li>Voir les informations sous «Réglementations nationales»</li>
                </ul>
                
                <h3>Envoi / Distribution des informations</h3>
                <ul>
                    <li>En date du 28 janvier 2017, le distributeur canadien Celopharma n'a pas déclaré d'expédition à ce jour</li>
                </ul>
            </div>
            
            <h2 id='ON'>Ontario</h2>
            <div>
                <h3>Règlement sur les médecins</h3>
                <ul>
                    <li>Le Collège des médecins et chirurgiens de l'Ontario a publié des directives à l'intention de ses membres, <a href='http://www.cpso.on.ca/Policies-Publications/Positions-Initiatives/Mifegymiso' target='_blank'>click here</a> for details</li>
                </ul>
                
                <h3>Règlement sur les pharmaciens</h3>
                <ul>
                    <li>Le 7 mars 2017, l'Ordre des pharmaciens de l'Ontario a publié des lignes directrices à l'appui de la pratique des professionnels de la pharmacie dispensant Mifegymiso® en Ontario et se joignant à la Colombie-Britannique <a href='http://www.ocpinfo.com/library/practice-related/download/Dispensing_Mifegymiso.pdf' target='_blank'>
Lire la suite</a></li>
                </ul>
                
                <h3>Information de couverture</h3>
                <ul>
                    <li>Le 27 avril, le gouvernement libéral de l'Ontario a annoncé son budget provincial pour 2017 qui comprend la couverture universelle de Mifegymiso®, 
Lire la suite from the media <a href='http://news.nationalpost.com/news/canada/free-abortion-pill-cash-for-boobs-and-babies-top-23-takeaways-from-the-ontario-budget' target='_blank'>here</a></li>
                    <li>le gouvernement de l'Ontario a annoncé que la couverture complète de Mifegymiso® entrera en vigueur le 10 août 2017, <a href='http://www.cbc.ca/news/canada/toronto/abortion-pill-mifegymiso-1.4233611' target='_blank'>
Lire la suite</a></li>
                    <li>Les procédures de facturation des pharmacies, y compris le remboursement et les demandes de remboursement pour Mifegymiso®, <a href='http://www.health.gov.on.ca/en/pro/programs/drugs/opdp_eo/eo_communiq.aspx' target='_blank'>click here</a></li>
                </ul>
                
                <h3>Envoi / Distribution des informations</h3>
                <ul>
                    <li>Distributeur en Ontario via Celopharma ou un grossiste, McKesson <a href='https://www.mckesson.ca/mckesson-pharmaceutical' target='_blank'>
Lire la suite</a></li>
                    <li>En date du 24 février 2017, Celopharma a expédié de la mifépristone à des cliniques à Toronto et à Ottawa.</li>
                    
                    <li>En date du 11 avril 2017, en plus de Toronto et d'Ottawa, Celopharma a expédié de la mifépristone à Kingston</li>
                    
                </ul>
            </div>
            
            <h2 id='PE'>Île-du-Prince-Édouard</h2>
            <div>
                <h3>Règlement sur les médecins</h3>
                <ul>
                    <li>Voir les informations sous «Réglementations nationales»</li>
                </ul>
                
                <h3>Règlement sur les pharmaciens</h3>
                <ul>
                    <li>Voir les informations sous «Réglementations nationales»</li>
                </ul>
                
                <h3>Information de couverture</h3>
                <ul>
                    <li>Tel que rapporté dans les médias, les plans de santé étudiant couvrant: UPEI (couvert à 90%) et Holland College (couvert à 80%) (10 février 2017) <a href='http://www.cbc.ca/news/canada/prince-edward-island/abortion-drug-pill-mifegymiso-pei-doctors-pharmacists-1.3976558' target='_blank'>
Lire la suite info</a></li>
                    <li>PEI is still exploring coverage options. As reported in the media, by mid-June, 22 physicians from PEI had completed the training, and another four had registered for the course, <a href='http://www.cbc.ca/news/canada/prince-edward-island/pei-mifegymiso-1.4225850' target='_blank'>
Lire la suite</a></li>
                </ul>
                
                <h3>Envoi / Distribution des informations</h3>
                <ul>
                    <li>Au 28 janvier 2017, le distributeur canadien Celopharma n'a pas déclaré d'expédition à ce jour</li>
                </ul>
            </div>
            
            <h2 id='QC'>Quebec</h2>
            <div>
                <h3>Règlement sur les médecins</h3>
                <ul>
                    <li>Pour obtenir des renseignements sur les exigences de la province en matière de prescription et de distribution de la mifépristone au Québec,<a href='http://www.cmq.org/nouvelle/en/pilule-abortive-directives-cliniques.aspx' target='_blank'>click here</a>.</li>
                </ul>
                
                <h3>Règlement sur les pharmaciens</h3>
                <ul>
                    <li>Pour obtenir des renseignements sur les exigences de la province en matière de prescription et de distribution de la mifépristone au Québec, <a href='http://www.cmq.org/nouvelle/en/pilule-abortive-directives-cliniques.aspx' target='_blank'>click here</a>.</li>
                </ul>
                
                <h3>Information de couverture</h3>
                <ul>
                    <li>PLa couverture médiatique de Mifegymiso® fait actuellement l'objet d'un processus d'évaluation (6 février 2017) <a href='http://www.cmq.org/nouvelle/en/recommandations-interimaires-bon-usage-mifegymiso.aspx'>
Lire la suite</a></li>
                    <li>OLe 6 juillet, le Québec est devenu la 4e province canadienne à annoncer une couverture complète de Mifegymiso®! To 
Lire la suite, <a href='http://www.msss.gouv.qc.ca/documentation/salle-de-presse/ficheCommunique.php?id=1359' target='_blank'>click here</a></li>
                    <li>Tel que rapporté dans les médias le 15 décembre, la mifépristone est devenue disponible dans la province de <a href='http://montrealgazette.com/news/local-news/abortion-pill-will-be-available-in-quebec-as-of-dec-15' target='_blank'>Quebec</a> - for free!</li>
                </ul>
                
                <h3>Envoi / Distribution des informations</h3>
                <ul>
                    <li>Au 28 janvier 2017, le distributeur canadien Celopharma n'a pas déclaré d'expédition à ce jour</li>
                </ul>
            </div>
            
            <h2 id='SK'>Saskatchewan</h2>
            <div>
                <h3>Règlement sur les médecins</h3>
                <ul>
                    <li>Voir les informations sous «Réglementations nationales»</li>
                </ul>
                
                <h3>
Règlement sur les pharmaciens</h3>
                <ul>
                    <li>L'Ordre des pharmaciens de la Saskatchewan a élaboré des lignes directrices qui stipulent que les professionnels de la pharmacie en Saskatchewan ont le droit de dispenser des médicaments directement aux patients, <a href='https://scp.in1touch.org/document/3774/Mifegymiso_Guidance_FINAL.pdf' target='_blank'>
Lire la suite</a></li>
                </ul>
                
                <h3>Information de couverture</h3>
                <ul>
                    <li>Le 31 août 2017, Mifegymiso a été ajouté à <a href='http://formulary.drugplan.ehealthsask.ca/default.aspx 'target='_blank'> Le Formulaire de la Saskatchewan </a>, ce qui signifie que le coût demandé à un patient variera selon la couverture individuelle et l'admissibilité au moyen de programmes de prestations, <a href='http://www.saskatchewan.ca/government/news-and-media/2017/august/31/mifegymiso-added-to-formulary' target='_blank'>
Lire la suite</a></li>
                </ul>
                
                <h3>Envoi / Distribution des informations</h3>
                <ul>
                    <li>Tel que rapporté dans les médias le 6 mars 2017, Mifegymiso® a été expédié à Saskatoon, <a href='http://globalnews.ca/news/3291520/abortion-pill-mifegymiso-starts-to-arrive-in-saskatchewan/' target='_blank'>
Lire la suite</a></li>
                </ul>
            </div>
            
            <h2 id='YT'>Yukon</h2>
            <div>
                <h3>Règlement sur les médecins</h3>
                <ul>
                    <li>Voir les informations sous «Réglementations nationales»</li>
                </ul>
                
                <h3>Pharmacist Regulations</h3>
                <ul>
                    <li>Voir les informations sous «Réglementations nationales»</li>
                </ul>
                
                <h3>Information de couverture</h3>
                <ul>
                    <li>Voir les informations sous «Réglementations nationales»</li>
                </ul>
                
                <h3>Envoi / Distribution des informations</h3>
                <ul>
                    <li>En date du 24 février 2017, Celopharma a expédié de la mifépristone à des cliniques à Whitehorse</li>
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

}

?>
