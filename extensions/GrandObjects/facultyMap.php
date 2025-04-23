<?php

// Full map of faculty/departments, including possible department aliases
$facultyMap = array(
    "ALES" => array(
        "Resource Economics & Environmental Sociology", "Resource Economics and Environmental Sociology",
        "Renewable Resources",
        "Human Ecology",
        "Ag, Food & Nutri Sci", "Agricultural, Food & Nutritional Science"
    ),
    "Science" => array(
        "Computing Science",
        "Mathematical And Statistical Sciences", "Mathematical & Statistical Sciences",
        "Chemistry",
        "Psychology",
        "Biological Sciences",
        "Physics",
        "Earth And Atmospheric Sciences", "Earth & Atmospheric Sciences"
    ),
    "Engineering" => array(
        "Mechanical Engineering",
        "Biomedical Engineering",
        "Chemical and Materials Engineering", "Chemical & Materials Engineering",
        "Civil and Environmental Engineering", "Civil & Environmental Engineering",
        "Electrical & Computer Engineering", "Electrical and Computer Engineering"
    ),
    "Arts" => array(
        "Art and Design", "Art And Design",
        "Drama",
        "Music",
        "East Asian Studies",
        "English and Film Studies", "English & Film Studies",
        "History, Classics, and Religion", "History/Classics/Religion",
        "Modern Languages and Cultural Studies", "Mod Lang & Cultural Stud",
        "Philosophy",
        "Media and Technology Studies", "Media Tech Studies(MTS)", "Media Tech Studies",
        "Anthropology",
        "Economics",
        "Linguistics",
        "Political Science",
        "Psychology",
        "Sociology",
        "Women's and Gender Studies", "Women & Gender Studies"
    ),
    "Rehabilitation Medicine" => array(
        "Physical Therapy",
        "Occupational Therapy",
        "Communication Sciences & Disorders"
    ),
    "Business" => array(
        "Accounting & Business Analytics",
        "Finance",
        "Marketing, Business Economics & Law",
        "Strategy, Entrepreneurship & Management"
    )
);

// Simplified map of faculty/departments
$facultyMapSimple = array(
    "ALES" => array(
        "REES" => "Resource Economics & Environmental Sociology",
        "RR" => "Renewable Resources",
        "HE" => "Human Ecology",
        "AFNS" => "Ag, Food & Nutri Sci"
    ),
    "Science" => array(
        "CMPUT" => "Computing Science",
        "MATH" => "Mathematical And Statistical Sciences",
        "CHEM" => "Chemistry",
        "PSYCH" => "Psychology",
        "BIOL" => "Biological Sciences",
        "PHYS" => "Physics",
        "EAS" => "Earth And Atmospheric Sciences"
    ),
    "Engineering" => array(
        "MECE" => "Mechanical Engineering",
        "BIOE" => "Biomedical Engineering",
        "CHEME" => "Chemical and Materials Engineering",
        "CIVE" => "Civil and Environmental Engineering",
        "ECE" => "Electrical & Computer Engineering"
    ),
    "Arts" => array(
        "ART" => "Art and Design",
        "DRAMA" => "Drama",
        "MUSIC" => "Music",
        "EAS" => "East Asian Studies",
        "EFS" => "English and Film Studies",
        "HISTORY" => "History, Classics, and Religion",
        "LANG" => "Modern Languages and Cultural Studies",
        "PHIL" => "Philosophy",
        "MEDIA" => "Media and Technology Studies",
        "ANTRO" => "Anthropology",
        "ECON" => "Economics",
        "LING" => "Linguistics",
        "POL" => "Political Science",
        "PSYCH" => "Psychology",
        "SOC" => "Sociology",
        "GENDER" => "Women's and Gender Studies"
    ),
    "Rehabilitation Medicine" => array(
        "PT" => "Physical Therapy",
        "OT" => "Occupational Therapy",
        "CSD" => "Communication Sciences & Disorders"
    ),
    "Business" => array(
        "ABA" => "Accounting & Business Analytics",
        "FIN" => "Finance",
        "MBEL" => "Marketing, Business Economics & Law",
        "SEM" => "Strategy, Entrepreneurship & Management"
    )
);
?>
