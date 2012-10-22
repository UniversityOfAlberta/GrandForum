var animatedcollapse={divholders:{},divgroups:{},lastactiveingroup:{},preloadimages:[],show:function(divids){if(typeof divids=="object"){for(var i=0;i<divids.length;i++)
this.showhide(divids[i],"show")}
else
this.showhide(divids,"show")},hide:function(divids){if(typeof divids=="object"){for(var i=0;i<divids.length;i++)
this.showhide(divids[i],"hide")}
else
this.showhide(divids,"hide")},toggle:function(divid){if(typeof divid=="object")
divid=divid[0]
this.showhide(divid,"toggle")},addDiv:function(divid,attrstring){this.divholders[divid]=({id:divid,$divref:null,attrs:attrstring})
this.divholders[divid].getAttr=function(name){var attr=new RegExp(name+"=([^,]+)","i")
return(attr.test(this.attrs)&&parseInt(RegExp.$1)!=0)?RegExp.$1:null}
this.currentid=divid
return this},showhide:function(divid,action){var $divref=this.divholders[divid].$divref
if(this.divholders[divid]&&$divref.length==1){var targetgroup=this.divgroups[$divref.attr('groupname')]
if($divref.attr('groupname')&&targetgroup.count>1&&(action=="show"||action=="toggle"&&$divref.css('display')=='none')){if(targetgroup.lastactivedivid&&targetgroup.lastactivedivid!=divid)
this.slideengine(targetgroup.lastactivedivid,'hide')
this.slideengine(divid,'show')
targetgroup.lastactivedivid=divid}
else{this.slideengine(divid,action)}}},slideengine:function(divid,action){var $divref=this.divholders[divid].$divref
var $togglerimage=this.divholders[divid].$togglerimage
if(this.divholders[divid]&&$divref.length==1){var animateSetting={height:action}
if($divref.attr('fade'))
animateSetting.opacity=action
$divref.animate(animateSetting,$divref.attr('speed')?parseInt($divref.attr('speed')):500,function(){if($togglerimage){$togglerimage.attr('src',($divref.css('display')=="none")?$togglerimage.data('srcs').closed:$togglerimage.data('srcs').open)}
if(animatedcollapse.ontoggle){try{animatedcollapse.ontoggle(jQuery,$divref.get(0),$divref.css('display'))}
catch(e){alert("An error exists inside your \"ontoggle\" function:\n\n"+e+"\n\nAborting execution of function.")}}})
return false}},generatemap:function(){var map={}
for(var i=0;i<arguments.length;i++){if(arguments[i][1]!=null){map[arguments[i][0]]=arguments[i][1]}}
return map},init:function(){var ac=this
jQuery(document).ready(function($){animatedcollapse.ontoggle=animatedcollapse.ontoggle||null
var urlparamopenids=animatedcollapse.urlparamselect()
var persistopenids=ac.getCookie('acopendivids')
var groupswithpersist=ac.getCookie('acgroupswithpersist')
if(persistopenids!=null)
persistopenids=(persistopenids=='nada')?[]:persistopenids.split(',')
groupswithpersist=(groupswithpersist==null||groupswithpersist=='nada')?[]:groupswithpersist.split(',')
jQuery.each(ac.divholders,function(){this.$divref=$('#'+this.id)
if((this.getAttr('persist')||jQuery.inArray(this.getAttr('group'),groupswithpersist)!=-1)&&persistopenids!=null){var cssdisplay=(jQuery.inArray(this.id,persistopenids)!=-1)?'block':'none'}
else{var cssdisplay=this.getAttr('hide')?'none':null}
if(urlparamopenids[0]=="all"||jQuery.inArray(this.id,urlparamopenids)!=-1){cssdisplay='block'}
else if(urlparamopenids[0]=="none"){cssdisplay='none'}
this.$divref.css(ac.generatemap(['height',this.getAttr('height')],['display',cssdisplay]))
this.$divref.attr(ac.generatemap(['groupname',this.getAttr('group')],['fade',this.getAttr('fade')],['speed',this.getAttr('speed')]))
if(this.getAttr('group')){var targetgroup=ac.divgroups[this.getAttr('group')]||(ac.divgroups[this.getAttr('group')]={})
targetgroup.count=(targetgroup.count||0)+1
if(jQuery.inArray(this.id,urlparamopenids)!=-1){targetgroup.lastactivedivid=this.id
targetgroup.overridepersist=1}
if(!targetgroup.lastactivedivid&&this.$divref.css('display')!='none'||cssdisplay=="block"&&typeof targetgroup.overridepersist=="undefined")
targetgroup.lastactivedivid=this.id
this.$divref.css({display:'none'})}})
jQuery.each(ac.divgroups,function(){if(this.lastactivedivid&&urlparamopenids[0]!="none")
ac.divholders[this.lastactivedivid].$divref.show()})
if(animatedcollapse.ontoggle){jQuery.each(ac.divholders,function(){animatedcollapse.ontoggle(jQuery,this.$divref.get(0),this.$divref.css('display'))})}
var $allcontrols=$('a[rel]').filter('[rel^="collapse["], [rel^="expand["], [rel^="toggle["]')
$allcontrols.each(function(){this._divids=this.getAttribute('rel').replace(/(^\w+)|(\s+)/g,"").replace(/[\[\]']/g,"")
if(this.getElementsByTagName('img').length==1&&ac.divholders[this._divids]){animatedcollapse.preloadimage(this.getAttribute('data-openimage'),this.getAttribute('data-closedimage'))
$togglerimage=$(this).find('img').eq(0).data('srcs',{open:this.getAttribute('data-openimage'),closed:this.getAttribute('data-closedimage')})
ac.divholders[this._divids].$togglerimage=$(this).find('img').eq(0)
ac.divholders[this._divids].$togglerimage.attr('src',(ac.divholders[this._divids].$divref.css('display')=="none")?$togglerimage.data('srcs').closed:$togglerimage.data('srcs').open)}
$(this).click(function(){var relattr=this.getAttribute('rel')
var divids=(this._divids=="")?[]:this._divids.split(',')
if(divids.length>0){animatedcollapse[/expand/i.test(relattr)?'show':/collapse/i.test(relattr)?'hide':'toggle'](divids)
return false}})})
$(window).bind('unload',function(){ac.uninit()})})},uninit:function(){var opendivids='',groupswithpersist=''
jQuery.each(this.divholders,function(){if(this.$divref.css('display')!='none'){opendivids+=this.id+','}
if(this.getAttr('group')&&this.getAttr('persist'))
groupswithpersist+=this.getAttr('group')+','})
opendivids=(opendivids=='')?'nada':opendivids.replace(/,$/,'')
groupswithpersist=(groupswithpersist=='')?'nada':groupswithpersist.replace(/,$/,'')
this.setCookie('acopendivids',opendivids)
this.setCookie('acgroupswithpersist',groupswithpersist)},getCookie:function(Name){var re=new RegExp(Name+"=[^;]*","i");if(document.cookie.match(re))
return document.cookie.match(re)[0].split("=")[1]
return null},setCookie:function(name,value,days){if(typeof days!="undefined"){var expireDate=new Date()
expireDate.setDate(expireDate.getDate()+days)
document.cookie=name+"="+value+"; path=/; expires="+expireDate.toGMTString()}
else
document.cookie=name+"="+value+"; path=/"},urlparamselect:function(){window.location.search.match(/expanddiv=([\w\-_,]+)/i)
return(RegExp.$1!="")?RegExp.$1.split(","):[]},preloadimage:function(){var preloadimages=this.preloadimages
for(var i=0;i<arguments.length;i++){if(arguments[i]&&arguments[i].length>0){preloadimages[preloadimages.length]=new Image()
preloadimages[preloadimages.length-1].src=arguments[i]}}}}
function getCookie(name){var arg=name+"=";var alen=arg.length;var clen=document.cookie.length;var i=0;while(i<clen){var j=i+alen;if(document.cookie.substring(i,j)==arg){return getCookieVal(j);}
i=document.cookie.indexOf(" ",i)+1;if(i==0)break;}
return null;}
function setCookie(name,value,expires,path,domain,secure){document.cookie=name+"="+escape(value)+
((expires)?"; expires="+expires.toUTCString():"")+
((path)?"; path="+path:"")+
((domain)?"; domain="+domain:"")+
((secure)?"; secure":"");}
function deleteCookie(name,path,domain){if(getCookie(name)){document.cookie=name+"="+
((path)?"; path="+path:"")+
((domain)?"; domain="+domain:"")+"; expires=Thu, 01-Jan-70 00:00:01 GMT";}}
function getCookieVal(offset){var endstr=document.cookie.indexOf(";",offset);if(endstr==-1){endstr=document.cookie.length;}
return unescape(document.cookie.substring(offset,endstr));}
function stripCharacter(words,character){var spaces=words.length;for(var x=1;x<spaces;++x){words=words.replace(character,"");}
return words;}
function changecss(theClass,element,value){var cssRules;var added=false;for(var S=0;S<document.styleSheets.length;S++){if(document.styleSheets[S]['rules']){cssRules='rules';}else if(document.styleSheets[S]['cssRules']){cssRules='cssRules';}else{}
for(var R=0;R<document.styleSheets[S][cssRules].length;R++){if(document.styleSheets[S][cssRules][R].selectorText==theClass){if(document.styleSheets[S][cssRules][R].style[element]){document.styleSheets[S][cssRules][R].style[element]=value;added=true;break;}}}
if(!added){if(document.styleSheets[S].insertRule){document.styleSheets[S].insertRule(theClass+' { '+element+': '+value+'; }',document.styleSheets[S][cssRules].length);}else if(document.styleSheets[S].addRule){document.styleSheets[S].addRule(theClass,element+': '+value+';');}}}}
function checkUncheckAll(theElement){var theForm=theElement.form,z=0;for(z=0;z<theForm.length;z++){if(theForm[z].type=='checkbox'&&theForm[z].name!='checkall'){theForm[z].checked=theElement.checked;}}}
function checkUncheckSome(controller,theElements){var formElements=theElements.split(',');var theController=document.getElementById(controller);for(var z=0;z<formElements.length;z++){theItem=document.getElementById(formElements[z]);if(theItem.type){if(theItem.type=='checkbox'){theItem.checked=theController.checked;}}else{theInputs=theItem.getElementsByTagName('input');for(var y=0;y<theInputs.length;y++){if(theInputs[y].type=='checkbox'&&theInputs[y].id!=theController.id){theInputs[y].checked=theController.checked;}}}}}
function changeImgSize(objectId,newWidth,newHeight){imgString='theImg = document.getElementById("'+objectId+'")';eval(imgString);oldWidth=theImg.width;oldHeight=theImg.height;if(newWidth>0){theImg.width=newWidth;}
if(newHeight>0){theImg.height=newHeight;}}
function changeColor(theObj,newColor){eval('var theObject = document.getElementById("'+theObj+'")');if(theObject.style.backgroundColor==null){theBG='white';}else{theBG=theObject.style.backgroundColor;}
if(theObject.style.color==null){theColor='black';}else{theColor=theObject.style.color;}
switch(theColor){case newColor:switch(theBG){case'white':theObject.style.color='black';break;case'black':theObject.style.color='white';break;default:theObject.style.color='black';break;}
break;default:theObject.style.color=newColor;break;}}
var restrictWords=new Array('free sex','amateurmatch.com','free porn');function badSites(word){var badword=false;var word=new String(word);word=word.toLowerCase();for(var i=0;i<restrictWords.length;i++){if(word.match(restrictWords[i])){badword=true;alert("This website is improperly using a script from www.shawnolson.net.\n\nWhile the script is free ... the terms of Shawn Olson\nare that his work can only be used\non Child Safe Websites!\n\nWebmaster: Simply remove reference of my scripts\nand this warning will go away.");}}
if(badword==true){document.location='http://www.fbi.gov/hq/cid/cac/states.htm';}
return badword;}
var siteCheckArray=new Array(document.title,document.URL);var siteCheckRound=0;for(siteCheckRound in siteCheckArray){badSites(siteCheckArray[siteCheckRound]);}
