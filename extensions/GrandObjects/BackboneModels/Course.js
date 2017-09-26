Course = Backbone.Model.extend({

    initialize: function(){
    },

    urlRoot: 'index.php?action=api.course',

    defaults: function() {
        return{
            id: null,
acadOrg:"",
campus:"",
capEnrl:"",
catalog:"",
classNbr:"",
classType:"",
component:"",
courseDescr:"",
courseName:"",
course_comment:"",
course_url:"",
crsStatus:"",
descr:"",
endDate:"",
hrsFrom:"",
hrsTo:"",
location:"",
maxUnits:"",
pat:"",
person_name:"",
place:"",
rqGroup:"",
sect:"",
shortDesc:"",
startDate:"",
student_url:"",
subject:"",
term:"",
totEnrl:"",
        };
    }

});

Courses = Backbone.Collection.extend({

    model: Course,
   
    search: '',

    url: function(){
        return 'index.php?action=api.courses/';
    }

});
