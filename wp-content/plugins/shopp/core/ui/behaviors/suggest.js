/*
 * suggest.js - Shopp search suggestion library
 * Copyright ?? 2011 by Ingenesis Limited, Copyright 2007 by Mark Jaquith and Alexander Dick
 * Licensed under the GPLv3 {@see license.txt}
 */
(function(a){a.suggest=function(p,g){var o,c,f,n,d,r,q;o=a(p);input=o.is("input")?o:o.find("input");c=a(input).attr("autocomplete","off");f=a("<ul/>").appendTo(document);n=false;d=0;r=[];q=0;f.addClass(g.resultsClass).appendTo("body");j();a(window).load(j).resize(j);c.blur(function(){setTimeout(function(){f.hide()},200)});if(g.showOnFocus){c.focus(function(){j();i();if(g.autoSuggest){s()}})}if(a.browser.msie){try{f.bgiframe()}catch(u){}}if(a.browser.mozilla){c.keypress(m)}else{c.keydown(m)}function j(){var e=o.offset();f.css({top:(e.top+o.outerHeight()+g.yoffset)+"px",left:e.left+"px"})}function m(y){if((/27$|38$|40$/.test(y.keyCode)&&f.is(":visible"))||(/^13$|^9$/.test(y.keyCode)&&w())){if(y.preventDefault){y.preventDefault()}if(y.stopPropagation){y.stopPropagation()}y.cancelBubble=true;y.returnValue=false;switch(y.keyCode){case 38:k();break;case 40:v();break;case 9:case 13:t();break;case 27:f.hide();break}}else{if(c.val().length!=d){if(n){clearTimeout(n)}n=setTimeout(l,g.delay);d=c.val().length}}}function l(){var z=a.trim(c.val()),y,e;if(z.length==0&&g.autoSuggest){z=g.autoSuggest}if(g.multiple){y=z.lastIndexOf(g.multipleSep);if(y!=-1){z=a.trim(z.substr(y+g.multipleSep.length))}}if(z.length>=g.minchars){cached=x(z);if(cached){i(cached.items)}else{a.get(g.source,{q:z},function(A){f.hide();if("json"==g.format){e=a.parseJSON(A)}else{e=b(A,z)}i(e);h(z,e,A.length)})}}else{f.hide()}}function s(){var e=false;if(e){clearTimeout(e)}e=setTimeout(l,3000);c.blur(function(){clearTimeout(e)})}function x(y){var e;for(e=0;e<r.length;e++){if(r[e]["q"]==y){r.unshift(r.splice(e,1)[0]);return r[0]}}return false}function h(A,e,y){var z;while(r.length&&(q+y>g.maxCacheSize)){z=r.pop();q-=z.size}r.push({q:A,size:y,items:e});q+=y}function i(e){var z="",y;if(!e){if(g.label){f.html("<li>"+g.label+"</li>").show()}return}if(!e.length){if(g.label){f.html("<li>"+g.label+"</li>").show()}return}j();if("json"==g.format){for(y=0;y<e.length;y++){z+='<li alt="'+e[y].id+'">'+e[y].name+"</li>"}}else{for(y=0;y<e.length;y++){z+="<li>"+e[y]+"</li>"}}f.html(z).show();f.children("li").mouseover(function(){f.children("li").removeClass(g.selectClass);a(this).addClass(g.selectClass)}).click(function(A){A.preventDefault();A.stopPropagation();t()});if(g.autoSelect){v()}}function b(e,B){var y=[],C=e.split(g.delimiter),A,z;for(A=0;A<C.length;A++){z=a.trim(C[A]);if(z){z=z.replace(new RegExp(B,"ig"),function(D){return'<span class="'+g.matchClass+'">'+D+"</span>"});y[y.length]=z}}return y}function w(){var e;if(!f.is(":visible")){return false}e=f.children("li."+g.selectClass);if(!e.length){e=false}return e}function t(){$currentResult=w();if($currentResult){if(g.multiple){if(c.val().indexOf(g.multipleSep)!=-1){$currentVal=c.val().substr(0,(c.val().lastIndexOf(g.multipleSep)+g.multipleSep.length))}else{$currentVal=""}c.val($currentVal+$currentResult.text()+g.multipleSep);c.focus()}else{c.val($currentResult.text()).attr("alt",$currentResult.attr("alt"))}f.hide();if(g.onSelect){g.onSelect.apply(c[0])}}}function v(){$currentResult=w();if($currentResult){$currentResult.removeClass(g.selectClass).next().addClass(g.selectClass)}else{f.children("li:first-child").addClass(g.selectClass)}}function k(){var e=w();if(e){e.removeClass(g.selectClass).prev().addClass(g.selectClass)}else{f.children("li:last-child").addClass(g.selectClass)}}};a.fn.suggest=function(c,b){if(!c){return}b=b||{};b.multiple=b.multiple||false;b.multipleSep=b.multipleSep||", ";b.showOnFocus=b.showOnFocus||false;b.source=c;b.yoffset=b.yoffset||0;b.delay=b.delay||100;b.autoDelay=b.autoDelay||3000;b.autoQuery=b.autoQuery||false;b.resultsClass=b.resultsClass||"suggest-results";b.selectClass=b.selectClass||"suggest-select";b.matchClass=b.matchClass||"suggest-match";b.minchars=b.minchars||2;b.delimiter=b.delimiter||"\n";b.format=b.format||"string";b.label=b.label||false;b.onSelect=b.onSelect||false;b.autoSelect=b.autoSelect||false;b.maxCacheSize=b.maxCacheSize||65536;this.each(function(){new a.suggest(this,b)});return this}})(jQuery);