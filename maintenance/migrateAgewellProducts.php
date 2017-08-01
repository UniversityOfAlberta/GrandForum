<?php
    
    require_once('commandLine.inc');

    function updateProducts($fromCategory, $fromType, $toCategory, $toType){
        DBFunctions::update('grand_products',
                            array('category' => $toCategory,
                                  'type' => $toType),
                            array('category' => EQ($fromCategory),
                                  'type' => EQ($fromType)));
    }
    
    // Acivity
    updateProducts('Activity', 'Event Organization', 'KTEE - Knowledge Mobilization', 'Event Organization');
    updateProducts('Activity', 'Conference Attendance', 'Scientific Excellence - Advancing Knowledge', 'Conference Attendance');
    updateProducts('Activity', 'Student Volunteering', 'HQP Training', 'Student Volunteering');
    updateProducts('Activity', 'Ethics Application', 'Scientific Excellence - Leadership', 'Ethics Application');
    updateProducts('Activity', 'Work on Committee', 'Scientific Excellence - Leadership', 'Work on Committee');
    updateProducts('Activity', 'Leadership Position', 'Scientific Excellence - Leadership', 'Leadership Position');
    updateProducts('Activity', 'Meeting', 'Networking and Partnerships', 'Project Meeting');
    updateProducts('Activity', 'Blog', 'KTEE - Knowledge Mobilization', 'Blog');
    updateProducts('Activity', 'Start-Up', 'KTEE - Commercialization', 'Start-Up');
    updateProducts('Activity', 'Internship', 'HQP Training', 'Internship');
    updateProducts('Activity', 'Student Exchange', 'HQP Training', 'Student Exchange');
    updateProducts('Activity', 'Summer Institute Attendance', 'HQP Training', 'Summer Institute Attendance');
    updateProducts('Activity', 'Misc', 'Networking and Partnerships', 'Misc');
    
    // Publication
    updateProducts('Publication', 'Bachelors Thesis', 'HQP Training', 'Bachelors Thesis');
    updateProducts('Publication', 'Book', 'Scientific Excellence - Advancing Knowledge', 'Book');
    updateProducts('Publication', 'Book Chapter', 'Scientific Excellence - Advancing Knowledge', 'Book Chapter');
    updateProducts('Publication', 'Book Review', 'Scientific Excellence - Advancing Knowledge', 'Book Review');
    updateProducts('Publication', 'Collections Paper', 'Scientific Excellence - Advancing Knowledge', 'Collections Paper');
    updateProducts('Publication', 'Edited Book', 'Scientific Excellence - Advancing Knowledge', 'Edited Book');
    updateProducts('Publication', 'Journal Abstract', 'Scientific Excellence - Advancing Knowledge', 'Journal Abstract');
    updateProducts('Publication', 'Journal Paper', 'Scientific Excellence - Advancing Knowledge', 'Journal Paper');
    updateProducts('Publication', 'Magazine/Newspaper Article', 'KTEE - Knowledge Mobilization', 'Magazine/Newspaper Article');
    updateProducts('Publication', 'Masters Thesis', 'HQP Training', 'Masters Thesis');
    updateProducts('Publication', 'Masters Dissertation', 'HQP Training', 'Masters Dissertation');
    updateProducts('Publication', 'Conference Paper', 'Scientific Excellence - Advancing Knowledge', 'Conference Paper');
    updateProducts('Publication', 'Conference Abstract', 'Scientific Excellence - Advancing Knowledge', 'Conference Abstract');
    updateProducts('Publication', 'PhD Thesis', 'HQP Training', 'PhD Thesis');
    updateProducts('Publication', 'PhD Dissertation', 'HQP Training', 'PhD Dissertation');
    updateProducts('Publication', 'Proceedings Paper', 'Scientific Excellence - Advancing Knowledge', 'Proceedings Paper');
    updateProducts('Publication', 'Tech Report', 'Scientific Excellence - Advancing Knowledge', 'Tech Report');
    updateProducts('Publication', 'White Paper', 'Scientific Excellence - Advancing Knowledge', 'White Paper');
    updateProducts('Publication', 'Manual', 'Scientific Excellence - Advancing Knowledge', 'Manual');
    updateProducts('Publication', 'Scoping Review', 'Scientific Excellence - Advancing Knowledge', 'Scoping Review');
    updateProducts('Publication', 'Misc', 'Scientific Excellence - Advancing Knowledge', 'Misc');
    
    // Presentation
    updateProducts('Presentation', 'Seminar Presentation', 'Scientific Excellence - Advancing Knowledge', 'Seminar Presentation');
    updateProducts('Presentation', 'Poster', 'Scientific Excellence - Advancing Knowledge', 'Poster');
    updateProducts('Presentation', 'Invited Presentation', 'Scientific Excellence - Leadership', 'Invited Presentation');
    updateProducts('Presentation', 'Print Media Interview', 'KTEE - Knowledge Mobilization', 'Print Media Interview');
    updateProducts('Presentation', 'Radio Interview', 'KTEE - Knowledge Mobilization', 'Radio Interview');
    updateProducts('Presentation', 'TV Interview', 'KTEE - Knowledge Mobilization', 'TV Interview');
    updateProducts('Presentation', 'Digital News Interview', 'KTEE - Knowledge Mobilization', 'Digital News Interview');
    updateProducts('Presentation', 'Workshop Presentation', 'Scientific Excellence - Advancing Knowledge', 'Workshop Presentation');
    updateProducts('Presentation', 'Misc', 'KTEE - Knowledge Mobilization', 'Misc');
    
    // Product
    updateProducts('Product', 'Policy Brief', 'KTEE - Knowledge Mobilization', 'Policy Brief');
    
    // IP Management
    updateProducts('IP Management', 'License Agreement', 'KTEE - Commercialization', 'License Agreement');
    updateProducts('IP Management', 'Report of Invention', 'KTEE - Commercialization', 'Report of Invention');
    updateProducts('IP Management', 'IP Disclosure', 'KTEE - Commercialization', 'IP Disclosure');
    updateProducts('IP Management', 'Provisional Patent', 'KTEE - Commercialization', 'Provisional Patent');
    updateProducts('IP Management', 'Patent Cooperation Treaty (PCT)', 'KTEE - Commercialization', 'Patent Cooperation Treaty (PCT)');
    updateProducts('IP Management', 'Copyright', 'KTEE - Commercialization', 'Copyright');
    updateProducts('IP Management', 'Patent', 'KTEE - Commercialization', 'Patent');
    updateProducts('IP Management', 'Trademark', 'KTEE - Commercialization', 'Trademark');
    updateProducts('IP Management', 'Misc', 'KTEE - Commercialization', 'Misc');
    
    // Award
    updateProducts('Award', 'Award', 'Scientific Excellence - Leadership', 'Award');
    updateProducts('Award', 'Misc', 'Scientific Excellence - Leadership', 'Award');

?>
