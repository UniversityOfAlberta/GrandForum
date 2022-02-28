CategoryButtonsView = Backbone.View.extend({
    parent: null,
    template: _.template($('#category_buttons_template').html()),
    
    initialize: function(options){
	    this.parent = options.parent;
    },

    events: {
    },


    getCategoryJSON: function(){

	var cat_json = 
		    [
    {
        "id": "Activity",
        "text": "Activity",
        "class": "testing",
        "description": "Explore ways to get physically active in your community!",
        "children": [
            {
                "text": "Sports",
                "description": "Choose from a variety of individual and team sports",
                "children": [
                    {
                        "text": "Indoor",
                        "value": 90,
                        "description": "Pickleball, bowling, volleyball, and more"
                    },
                    {
                        "text": "Winter",
                        "value": 90,
                        "description": "Skating, curling, and more"
                    },
                    {
                        "text": "Water",
                        "value": 90,
                        "description": "Canoeing, rowing, and more"
                    },
                    {
                        "text": "Field",
                        "value": 90,
                        "description": "Tennis, lawn-bowling, soccer, and more"
                    }
                ]
            },
            {
                "text": "Exercise",
                "value": 113,
                "description": " Improve your strength, balance, and endurance with these programs",
                "children": [
                    {
                        "text": "Biking",
                        "value": 90,
                        "description": "Join a group in your community for your next road or trail ride"
                    },
                    {
                        "text": "Dance",
                        "value": 90,
                        "description": "Try a new style of dance, whether you’re a beginner or expert"
                    },
                    {
                        "text": "Movement and Mindfulness",
                        "value": 90,
                        "description": "Enjoy classes for all mobility levels such as Yoga and Tai-Chi"
                    },
                    {
                        "text": "Fitness",
                        "value": 90,
                        "description": "Choose from a variety of programs such as Pilates and aerobics for a fun way to move"
                    },
                    {
                        "text": "Horseback Riding",
                        "value": 90,
                        "description": "Find recreational and therapeutic options for all levels"
                    },
                    {
                        "text": "Recreation Facilities",
                        "value": 90,
                        "description": "Visit a gym or community centre near you"
                    },
                    {
                        "text": "Walking/Running",
                        "value": 90,
                        "description": "Join a group in your community for your next walk or run"
                    },
                    {
                        "text": "Swimming",
                        "value": 90,
                        "description": "Attend aquatic classes or enjoy a community pool"
                    }
                ]
            },
            {
                "text": "Gardening",
                "value": 78,
                "description": "Join a gardening club or find your nearest community garden"
            },
            {
                "text": "Trails",
                "value": 78,
                "description": "Take a walk or a hike at one of the many conservation areas or trails in KFL&A"
            },
            {
                "text": "Sleep",
                "value": 78,
                "description": "Find supports to improve your sleep"
            }
        ]
    },
    {
        "text": "Interact",
        "description": "Explore opportunities to meet new people in your community!",
        "children": [
            {
                "text": "Crafting",
                "value": 88,
                "description": "Join a group to learn or practice your knitting, quilting, crocheting, or other crafts"
            },
            {
                "text": "Library",
                "value": 88,
                "description": "Find your local library to attend a workshop, event, or borrow a resource "
            },
            {
                "text": "Recreation",
                "value": 88,
                "description": "Join a club or group to meet people that share your interests ",
                "children": [
                    {
                        "text": "Games",
                        "value": 88,
                        "description": "Play board games or cards "
                    },
                    {
                        "text": "Education",
                        "value": 88,
                        "description": "Take a class to learn something new"
                    },
                    {
                        "text": "Advocacy",
                        "value": 88,
                        "description": "Join to promote a good cause "
                    },
                    {
                        "text": "Culture",
                        "value": 88,
                        "description": "Connect with cultural clubs and centres in KFL&A"
                    },
                    {
                        "text": "Historical",
                        "value": 88,
                        "description": "Learn more about historical events and KFL&A heritage"
                    },
                    {
                        "text": "Hobby",
                        "value": 88,
                        "description": "Gather with your peers to celebrate a specific interest"
                    },
                    {
                        "text": "Social",
                        "value": 88,
                        "description": "Join a club or association focused on meeting new friends"
                    }
                ]
            },
            {
                "text": "Religious Centres",
                "value": 88,
                "description": "Join a local chapter of your religious affiliation"
            },
            {
                "text": "Support Groups/Services",
                "value": 88,
                "description": "Find support in your community to meet your specific needs"
            },
            {
                "text": "Arts",
                "value": 88,
                "description": "Opportunities to create through literature, music, and visual or performing arts",
                "children": [
                    {
                        "text": "Music",
                        "value": 88,
                        "description": "Join a singing group, play your favourite instrument or learn something new"
                    },
                    {
                        "text": "Performing",
                        "value": 88,
                        "description": "Become a part of a local theatre production or participate in acting workshops"
                    },
                    {
                        "text": "Visual",
                        "value": 88,
                        "description": "Create through drawing, painting, pottery and other mediums"
                    },
                    {
                        "text": "Literature",
                        "value": 88,
                        "description": "Join a writing group to develop or advance your literary skills"
                    }
                ]
            },
            {
                "text": "Volunteering",
                "value": 88,
                "description": "Meet new people while contributing to your community"
            }
        ]
    },
    {
        "text": "Vaccination/Optimize Medication",
        "value": 88,
        "description": "Find a location near you to get vaccinated or review   your medications",
        "children": [
            {
                "text": "Family Medicine",
                "value": 22,
                "description": "Find a primary care provider"
            },
            {
                "text": "Clinic",
                "value": 38,
                "description": "Healthcare clinics that accept walk-ins"
            },
            {
                "text": "Pharmacy",
                "value": 38,
                "description": "Local pharmacies that provide vaccinations and medication reviews"
            },
            {
                "text": "Education",
                "value": 38,
                "description": "Public health resources"
            }
        ]
    },
    {
        "text": "Diet and Nutrition",
        "description": "Local supports and services to help create or maintain a balanced diet",
        "children": [
            {
                "text": "Delivery",
                "value": 92,
                "description": "Community-based meal programs that offer home delivery"
            },
            {
                "text": "Dietitian Services",
                "value": 92,
                "description": "Find a Registered Dietitian in your area"
            },
            {
                "text": "Food Banks and Stands",
                "value": 92,
                "description": "Free or subsidized food"
            },
            {
                "text": "Fresh Food Boxes",
                "value": 92,
                "description": "Seasonal boxes of fruit and vegetables at reduced prices"
            },
            {
                "text": "Meal Programs",
                "value": 92,
                "description": "Hot meals provided in a congregate setting or to take-home"
            },
            {
                "text": "Dental",
                "value": 92,
                "description": "Oral health services and financial assistance programs"
            },
            {
                "text": "Classes",
                "value": 92,
                "description": "Cooking and nutrition classes"
            }
        ]
    },
    {
        "text": "Education and Employment",
        "description": "Opportunities for continued learning and assistance searching for employment",
        "children": [
            {
                "text": "Continued Education",
                "value": 42,
                "description": "Opportunities for further education"
            },
            {
                "text": "Literacy/ESL",
                "value": 28,
                "description": "Programs and classes to help improve literacy skills and learn the English language"
            },
            {
                "text": "Employment",
                "value": 42,
                "description": "Programs and services to assist with a job search"
            }
        ]
    },
    {
        "text": "Financial/Legal Assistance",
        "description": "Services that provide support for managing money, and assist with legal matters",
        "children": [
            {
                "text": "Credit Counselling",
                "value": 121,
                "description": "Services to help manage debts"
            },
            {
                "text": "Income Tax",
                "value": 100,
                "description": "Volunteer services that assist individuals with income tax returns"
            },
            {
                "text": "Legal Services",
                "value": 42,
                "description": "Subsidized legal advice and education on matters such as estates, wills, and advanced care planning"
            },
            {
                "text": "Social Services",
                "value": 42,
                "description": "Community programs providing financial support and help with financial assistance applications"
            }
        ]
    },
    {
        "text": "Chronic Conditions",
        "description": "Programs and services that help you manage living with a specific ongoing health issue",
        "children": [
            {
                "text": "Cancer",
                "value": 121,
                "description": "Services for people living with cancer and their care partners"
            },
            {
                "text": "Chronic Pain",
                "value": 100,
                "description": "Services for people living with chronic pain and their care partners"
            },
            {
                "text": "Dementia",
                "value": 42,
                "description": "Services for people living with a type of dementia and their care partners"
            },
            {
                "text": "Diabetes",
                "value": 42,
                "description": "Services for people living with diabetes and their care partners"
            },
            {
                "text": "Other Chronic Conditions",
                "value": 42,
                "description": "Services for people living with other chronic conditions and their care partners"
            }
        ]
    },
    {
        "text": "Mental Health",
        "description": "Supportive services and programs for people who are living with a mental health disorder and their care partners",
        "children": [
            {
                "text": "Addiction",
                "value": 121,
                "description": "Support groups, counselling, treatment clinics and other services for individuals living with a substance use disorder"
            },
            {
                "text": "Clinics",
                "value": 100,
                "description": "Variety of services to support individuals living with a mental health disorder"
            },
            {
                "text": "Counselling",
                "value": 42,
                "description": "Individual and group counselling with options for specific populations"
            },
            {
                "text": "Support Groups",
                "value": 42,
                "description": "Regular meetings with peers who share a common healthcare need or lived experience"
            }
        ]
    },
    {
        "text": "Housing",
        "description": "Opportunities and services to assist with finding appropriate housing for your situation",
        "children": [
            {
                "text": "Disability Support",
                "value": 121,
                "description": "Residences and support for people living with a disability"
            },
            {
                "text": "Emergency Housing",
                "value": 100,
                "description": "Shelter services for those in need"
            },
            {
                "text": "Geared to Income",
                "value": 200,
                "description": "Rental units priced based on income"
            },
            {
                "text": "Housing for Older Adults",
                "value": 200,
                "description": "Services for people living with diabetes and their care partners"
            },
            {
                "text": "Housing Support",
                "value": 200,
                "description": "Housing related financial assistance programs and services "
            },
            {
                "text": "Nursing Home",
                "value": 200,
                "description": "Residency for those who require some care"
            }
        ]
    },
    {
        "text": "Home and Care Partners",
        "description": "Programs and services that help you manage living with a specific ongoing health issue",
        "children": [
            {
                "text": "Care Partners Support/Respite",
                "value": 121,
                "description": "Relief from caregiving responsibilities and other supports for care partners"
            },
            {
                "text": "Help at Home",
                "value": 100,
                "description": "Services provided in the home including housekeeping, meal preparation, basic personal care, companionship, and more"
            },
            {
                "text": "Homecare",
                "value": 42,
                "description": "Medical services delivered in the home"
            },
            {
                "text": "Palliative Care/Hospice",
                "value": 42,
                "description": "End of life care and support for care partners"
            }
        ]
    },
    {
        "text": "Healthcare Services",
        "description": "Programs and services that help you manage living with a specific ongoing health issue",
        "children": [
            {
                "text": "Clinics",
                "value": 121,
                "description": "Visit specific healthcare clinics including seniors day rehabilitation, stroke rehabilitation, breast assessments, and more"
            },
            {
                "text": "Hospitals",
                "value": 100,
                "description": "Locate the nearest hospital to you"
            },
            {
                "text": "KFL&A Public Health",
                "value": 42,
                "description": "Connect with the resources and services offered through your local health unit"
            }
        ]
    },
    {
        "text": "Disability Services",
        "description": "Program and services that help you manage living with a specific ongoing health issue",
        "children": [
            {
                "text": "Developmental Disability Services",
                "value": 121,
                "description": "Supports and services for individuals living with a developmental disability"
            },
            {
                "text": "Physical Disability Services",
                "value": 100,
                "description": "Supports and services for individuals living with a physical disability"
            },
            {
                "text": "Homecare",
                "value": 42,
                "description": "Services including housekeeping, respite, and nursing delivered in the home for individuals living with a disability"
            },
            {
                "text": "Community Support",
                "value": 42,
                "description": "Assistance with financial assistance applications, family support, employment training, and more"
            }
        ]
    },
    {
        "text": "Transportation",
        "description": "Program and services that help you manage living with a specific ongoing health issue",
        "children": [
            {
                "text": "Driving Assessment",
                "value": 121,
                "description": "Centres that offer driving lessons and continued assessments"
            },
            {
                "text": "Driving Program",
                "value": 100,
                "description": "Volunteer drivers available for grocery shopping, medical appointments, and other daily needs"
            },
            {
                "text": "Medical Transport",
                "value": 42,
                "description": "Non-emergency patient transfer services"
            },
            {
                "text": "Public Transit",
                "value": 42,
                "description": "Local bus service and non-emergency medical transfers"
            }
        ]
    }
];
	    return cat_json;
    },

    render: function(){
        this.el.innerHTML = this.template();
        return this.$el;
    }



});
