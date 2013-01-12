/*
	jQuery Plugin Checkbox Filter
	
	This plugin let you to filter some items sort by categories with checkbox, letting you to display how many remaining items
	will have in the collection.
	
	Author: Julian Xhokaxhiu
	Date: 26/06/2011
	Changelog:
		- 0.1:  initial relase version.
*/
var CheckboxFilter;
(function ($) {
	CheckboxFilter = function(options){
		var placeholder = $(this);
		var settings = $(this).data('chekboxfiltersettings');
		// If settings are not declared, we set the default value for them
		if(!settings){
			settings = {
				elements:{},
				menu:{},
				name:'category'
			}
		};
		// Private methods - can only be called from within this object
		var IntFunz = {
			countID:function(ID){
				var n = 0;
				$.each(settings.elements,function(i,v){
					$.each(v,function(iID,vID){
						if(vID==ID)n++;
					});
				});
				return n;
			},
			refreshCount:function(e){
				var arr = [];
				placeholder.find('input:checked').each(function(i,v){arr.push($(v).val())});
				placeholder.find('li.cat').each(function(i,v){
					var input = $(v).find('input');
					if(placeholder.find('input:checked').length>0){
						var n = 0;
						$.each(settings.elements,function(iElem,vElem){
							var barr = [];
							var isSelfPresent=false;
							$.each(vElem,function(iID,vID){
								if(vID==input.val())isSelfPresent=true;
								$.each(arr,function(iArr,vArr){
									if(barr[iArr]==undefined)barr[iArr]=false;
									if(vID==vArr)barr[iArr]=true;
								});
							});
							barr.push(isSelfPresent);
							var count = true;
							$.each(barr,function(ii,vv){count=(vv&&count)});
							if(count)n++;
						});
						$(v).find('.count').text('('+n+')');
					}else $(v).find('.count').text('('+IntFunz.countID(input.val())+')');
				});
			}
		};
		
		if(typeof(options)=='string'){
			if(IntFunz[options])IntFunz[options].apply(null,Array.prototype.slice.call(arguments,1));
		}else if(options){
			settings = $.extend(settings,options||{});
			$(this).data('chekboxfiltersettings',settings);
			
			// Do this only if the input is not already binded
			if(placeholder.find('ul').length==0){
				var ul = $('<ul/>');
				$.each(settings.menu,function(iCat,vCat){
					var li = $('<li/>').append($('<a/>',{'href':'javascript:;'}).text(iCat).click(function(){$(this).next().toggle(250)}));
					var ulLi = $('<ul/>');
					$.each(vCat,function(iiCat,vvCat){ulLi.append($('<li/>',{'class':'cat'}).append($('<input/>',{'class':'li-filter'+iiCat,'name':settings.name,'type':'checkbox','value':iiCat}).click(IntFunz.refreshCount)).append($('<label/>').text(vvCat)).append($('<span/>',{'class':'count'}).text('('+IntFunz.countID(iiCat)+')')))});
					ul.append(li.append(ulLi.hide()));
				});
				placeholder.append(ul);
			}
		}
	};
	$.fn.extend({
		checkboxfilter:function(){
			var args = arguments;
			this.each(function(){CheckboxFilter.apply(this,args)});
		}
	});
})(jQuery);