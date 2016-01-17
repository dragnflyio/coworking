(function($){
	'use strict';
var wd = window,dc=document,xUI=null;

if(wd._main_) return;
if($.blockUI) xUI=$.blockUI.defaults;
	if(xUI){
		xUI.overlayCSS.opacity = 0.2;
		xUI.css.backgroundColor='#DFFFDF';
		xUI.css.color='#005F00';
		xUI.css.border = '2px solid #9FCF9F';
		xUI.message=''
}
wd._main_ = 1;
wd.totR = 0;wd.validation = {};
//server UTC+7 to js UTC+7
function svrtojstime(srvtime){
	var tz = new Date().getTimezoneOffset()/60;
	return srvtime + (tz+7)*3600000 
}
function jstosvrtime(loctime){
	var tz = new Date().getTimezoneOffset()/60;
	return loctime - (tz+7)*3600000 
}
function repeat(n, f) {
  for (var i = 0; i < n; i++){
    f(i+1);
  }
}

function genGUID(){
var g = ""; 
for(var i = 0; i < 6; i++) 
g += Math.floor(Math.random() * 0xF).toString(0xF) 
return g; 
}

wd.mr=function (tpl, d, delim){
	if (!delim) tpl = tpl.replace(/<%%/g, '{{}{').replace(/%%>/g, '}}}').replace(/<%/g, '{{').replace(/%>/g, '}}');
	var template = Handlebars.compile(tpl);	
  if(!d) return template({});
	return template(d);
}
wd.arrParseInt=function (v, prefixFilter){
	if(!v || v.pop) return v;
  return $.map(v.split(','), function(n){
		if (isNaN(n)) return n;
		if(prefixFilter){
			if(n.indexOf(prefixFilter) !== 0) return null;
			return parseInt(n.substr(prefixFilter.length))
		}
    return parseInt(n)
  })
}
var mww=function (testFunc, callback, t){
  var max = 500;
  var chk = setInterval(function(){
    max--;
    if(max<0) clearInterval(chk);
    if(testFunc.call()){
      try{
        callback.call();
        clearInterval(chk)
      }catch(e){
        clearInterval(chk)
      }
    }
  },t)
}
wd.mwait=function (testFunc, callback, t){
	return new mww(testFunc, callback, t)  
}
wd.mclone=function (o){
	if(o.pop) return $.extend(true,[],o)
 return $.extend(true,{},o)
}

wd.Geekutil = {
  get: function(idElement){
    return $('#' + idElement).val();
  }
  ,set: function(idElement, v){
    $('#' + idElement).val(v);
  }
  ,NoValue:[-1,' Chọn ']
  ,after: function(times, func){
    if (times <= 0) return func();
    return function(){
      if (--times < 1){
        return func.apply(this, arguments);
      }
    }
  }
}
wd.GMTDate = function(time){
	var d = new Date();
	var offset = d.getTimezoneOffset()*60000;
	if(time) return time + offset;
	return d.getTime() + offset
}

var Lmodal = [];
wd.mModal = function(html, w, lev, zi){
  var $edit = null, $modalDiv = null, that = this,clickDOCclose = true;
  if(!w) w = 'auto';
	var xl = 150;
	if(w) xl = w/2;
	if(!zi) zi = 1001;
	if(lev) zi = zi + lev*2;
  $edit = $("<div class='hoverarea' style='z-index:"+zi+";margin-left:-"+xl+"px'>").width(w).html(html).appendTo('#main-container');
  $modalDiv = $('<div class="modal-backdrop fade in"></div>').css('z-index',zi-1).appendTo('body');  
  $modalDiv.hide();
  $edit.find('.modal-header .close').click(function(){that.hide()});
  that.html = function(v){$edit.html(v)}
  that.hide = function(){
		Lmodal.pop();
    $modalDiv.off();$(dc).off('keyup',esc);
    $modalDiv.hide();
    $edit.removeClass('show');
    if(typeof that.onHide == 'function') that.onHide.call(null,$edit)
  }
	that.only = function(){clickDOCclose = false}
  var esc = function(e){
		if(e.which == 27){
			that.hide();
			if(Lmodal.length) Lmodal[0].onesc()			
		}
	}
	that.onesc = function(){$(dc).on('keyup',esc)}
	that.offesc = function(){$(dc).off('keyup',esc)}
  that.show = function(){
		Lmodal.push(that);
    $edit.css('top', '5%').css('left', '50%');
    $modalDiv.show();
		if(typeof that.onShow == 'function'){setTimeout(function(){that.onShow.call(null,$edit)},0)}
    $edit.addClass('show');
    if(clickDOCclose){
			if(1<Lmodal.length) for(var ii=0;ii<Lmodal.length-1;ii++) Lmodal[ii].offesc();
	 		$(dc).on('keyup',esc);
			$modalDiv.click(function(){
      	that.hide()
    	})
		}    
  }
}

Geekutil.tPop = function(tpl, place, asyn, ctFunc, bindingFunc, aqf, hqf){
  var $html = $('<div class="hoverarea"></div>').appendTo('#main-container'), recreateEl = 1, created = 0, autoClose = 1,contOBJ = null;
	var showing = 0, that = this;
	that.atc = function(b){autoClose = b}
  that.cont = function(){return $html}
	that.only = function(){
		recreateEl = 0;
		//quick fix		
		// if(typeof ctFunc == 'function'){
		// 			contOBJ = ctFunc(self)
		// 		} else contOBJ = ctFunc;
		// 		$html.html(mr(tpl, contOBJ));
	}
  if(asyn) $html.html(waittpl);  
  function fd(e){
	  if(!autoClose || !$(e.target).is(':visible')) return;		
    if(showing && (!$.contains(lastObj, e.target) && e.target != lastObj) && !$.contains($html.get(0), e.target)) that.close()    
  }
	
  var hfunc = function(ns){
    if(typeof hqf == 'function') hqf.call();
    var self = this; contOBJ = null;
    if(typeof ctFunc == 'function'){
    contOBJ = ctFunc(self, function(d){
      if (asyn){
        if(!created || recreateEl) $html.html(mr(tpl, d));
        if(typeof bindingFunc == 'function') bindingFunc.call(null, self, $html, that)
      }
    })} else contOBJ = ctFunc; 
    var $el = $(self);
    var p = $el.offset();
    var w = $el.outerWidth();
    var h = $el.outerHeight();
    if (!asyn){
			if(!created || recreateEl) $html.html(mr(tpl, contOBJ));
      if(typeof bindingFunc == 'function') bindingFunc.call(null, self, $html, that)
    }
    
    var os = $(wd).scrollTop();
    var mtop =  $(wd).height() - $html.height()-5;    
    //bottom
    if(place.p == 'bottom') $html.css({'top':p.top - os +h+1,'left':p.left + w/2-$html.width()/2});
		//bottomleft
    if(place.p == 'bottomleft') $html.css({'top':p.top - os +h+1,'left':p.left + w/2-$html.width()});
    //topleft
    if(place.p == 'topleft') $html.css({'top':Math.min(mtop, p.top - os - $html.height()/10), 'left':p.left - $html.width() - 5});
    //top
    if(place.p == 'top') $html.css({'top':p.top - os - $html.height(),'left':p.left - $html.width()/2 + w/2});
    //center
    if(place.p == 'center') $html.css({'top':p.top - os - $html.height()/2+h/2,'left':p.left - $html.width()/2 + w/2});
		if(!created || recreateEl) $(dc).on('click', fd);
		created = 1;       
		// if($(wd).height() < $html.offset().top+$html.height())                                              
		// $html.offset({top:Math.max(0, mtop)});                   
    if(!ns){showing = 1;$html.addClass('show')}
  }
  var qfunc = function(){
    showing = 0;
    if(recreateEl) $html.empty();
    if(asyn) $html.html(waittpl);    
    $html.removeClass('show');
    $(dc).off('click', fd);
    if(typeof aqf == 'function') aqf.call()
  }
  that.close = function(){
    qfunc()
  }
  var lastObj = null;
  that.hvr = function(obj){    
    $(obj).off();
    $(obj).click(function(e){
      e.preventDefault();      
      if(showing && lastObj == obj){
        qfunc();return
      }
      lastObj = obj;
      hfunc.call(obj)
    })
  }
	that.preload = function(obj){
    hfunc.call(obj,1)
  }
  that.reload = function(){
    hfunc.call(lastObj)
  }
  that.inj = function(func){
    func.call(null, $html, lastObj)
  }   
}
Geekutil.tHover = function(delay, tpl, getctF, asyn){
  var $html = $('<div class="hoverarea"></div>');
  if(asyn) $html.html(waittpl);
  $('#wrap').append($html);
  var showing = 0,that = this,mtime = null,uhvr = null,ctF = getctF;//, intime = 0;
  if(!getctF) ctF = getcontentTT;
  that.clear = function(){
    $html.remove()
  }
  var hfunc = function(){    
    if (!showing){
			var ml,top =  mxy.pageY - $(dc).scrollTop()+2;
      var contOBJ = ctF(this, function(d){
        if (asyn){
          mtime = setTimeout(function(){qfunc()},delay);
          $html.html(mr(tpl, d));
					//switch to top
		      if($(wd).height() < $html.height() + top + 3)
		        top = top - $html.height() - 5;      
		      ml = $(wd).width() - $html.width();
		      $html.css('top', top+5).css('left', Math.min(ml, mxy.pageX+8))
        }
      });
			if(!contOBJ){     
					if(asyn){showing = 1;$html.addClass('show')}
					return
			}
      if (!asyn) {
				$html.html(mr(tpl, contOBJ))				
			}
			//switch to top
	      if($(wd).height() < $html.height() + top + 3)
	        top = top - $html.height() - 5;      
	      ml = $(wd).width() - $html.width();
	      $html.css('top', top+5).css('left', Math.min(ml, mxy.pageX+8))
      showing = 1;
      $html.addClass('show')      
    }
  }
  var qfunc = function(){
    showing = 0;
    if(asyn) $html.html(waittpl);
    $html.removeClass('show')
  }
  var mxy = null, hvrtimer = null;
  that.hvr = function(obj){
    $(obj).off().mouseenter(function(e){
			clearTimeout(hvrtimer);
			hvrtimer = setTimeout(function(){
				mxy = e;
      	clearTimeout(mtime);
	      if(typeof uhvr == 'function') uhvr();
	      if(!asyn) mtime = setTimeout(function(){qfunc()},delay);
	      hfunc.call(obj);
      	uhvr = function(){qfunc.call(obj)}
			}, 400)
    })
  }
  that.close = function(){
    clearTimeout(mtime);qfunc()
  }
  that.ohvr = function(obj){
    that.close();
    $(obj).off()
  }
  var anch = function(obj){
    $(obj).off().mouseenter(function(){
      clearTimeout(mtime)
    }).mouseleave(function(){
      qfunc()
    })
  }
  anch($html)
}
wd.selByText = function ($sel, txt){
  var arrOpt = $sel.find('option');
  for(var ii = 0; ii<arrOpt.length; ii++){
    if(arrOpt[ii].text == txt) return arrOpt[ii]
  }
  return null;
}

var _tpl_msel_pop='', _fcf_msel_pop='';
function xrm(str){
	if(!str) return '';
	
    str=str.replace(/à|á|ạ|ả|ã|â|ầ|ấ|ậ|ẩ|ẫ|ă|ằ|ắ|ặ|ẳ|ẵ/gi,"a");
    str=str.replace(/è|é|ẹ|ẻ|ẽ|ê|ề|ế|ệ|ể|ễ/gi,"e");
    str=str.replace(/ì|í|ị|ỉ|ĩ/gi,"i");
    str=str.replace(/ò|ó|ọ|ỏ|õ|ô|ồ|ố|ộ|ổ|ỗ|ơ|ờ|ớ|ợ|ở|ỡ/gi,"o");
    str=str.replace(/ù|ú|ụ|ủ|ũ|ư|ừ|ứ|ự|ử|ữ/gi,"u");
    str=str.replace(/ỳ|ý|ỵ|ỷ|ỹ/gi,"y");
    str=str.replace(/đ/gi,"d");
    return str
}

function sortABC(arr){
	arr.sort(function(a,b){
		if(xrm(a[1]).toLowerCase() < xrm(b[1]).toLowerCase()) return -1;
		if(xrm(a[1]).toLowerCase() == xrm(b[1]).toLowerCase()) return 0;
		return 1
	})
}

function parseDataSelect(data){
  var retval = {};
  retval.list=[];
  //array of [colid, label];
  for (var ii=0; ii<data.length;ii++){
    retval.list.push({v1:data[ii][0],v2:data[ii][1]});
  }
  return retval
}

function makeTMArray(ids, lkarr, kk, kv){
	var r = [];
	ids = ','+ids+',';
	for(var ii = 0; ii<lkarr.length; ii++){
		if(-1 < ids.indexOf(','+lkarr[ii][kk]+',')) r.push([lkarr[ii][kk], lkarr[ii][kv]])
	}
	return r
}

wd.showPopup=function (popcode, options){
	$('#'+popcode).find('.modal-body').css("overflow-y","auto");
  if (options){
    if(options.label) $('#'+popcode).find('#label'+popcode).html(options.label);
    if(options.content) $('#'+popcode).find('.modal-body').html(options.content)
  }
  $('#'+popcode).modal({keyboard:true})
}
wd.fillSelectOpt=function (id, data, selValue, novalue, lbl){
  var html = '';
  var tmp = Geekutil.NoValue[1];
  if(lbl) Geekutil.NoValue[1] = lbl;
  if (novalue) html = '<option value="' + Geekutil.NoValue[0] + '">' + Geekutil.NoValue[1] + '</option>';
  if (data){
    var ii = 0;
    for (ii = 0; ii<data.length; ii ++){
      if(0 <= (','+selValue+',').indexOf(','+data[ii][0]+',')){
        html += '<option value="' + data[ii][0] + '" selected>'+ data[ii][1]+'</option>'        
      }else{
        html += '<option value="' + data[ii][0] + '">'+ data[ii][1]+'</option>'
      }
    }
  }
  Geekutil.NoValue[1] = tmp;
  $('#'+id).html(html)  
}
wd.fillSelect=function (id, data, selValue){
  fillSelectOpt(id, data, selValue, 1)
}
wd.loadAndFillSelect=function (id, url, selValue, no, lbl){
  if(url){
		if(typeof url=='object'){
			fillSelectOpt(id, url, selValue, no, lbl);
			if(selValue) $('#'+id).change();
			return
		}
    ajaxGet(url, function(r){
      fillSelectOpt(id, r, selValue, no, lbl);			
      if(selValue) $('#'+id).change()      
    })
  }
}
wd.getAppUrl=function (){  
  return ''
}
function addtok(u){	
  var tk = 'tk';
  if(typeof token == 'string') tk = encodeURIComponent(token);
  if(u.indexOf('?')<0) return u+'?_axurl_='+encodeURIComponent(location.href);
  return u+'&_axurl_='+encodeURIComponent(location.href);
}
wd.msgInfo = function(m, e, t){
	$('#msginfo_').remove();
	if('' == m) return;
	var cl = 'msginfo';
	if(e) cl += ' err'; 
	var tpl = '<div id="msginfo_" class="affix '+cl+'">'+m+'</div>';
	var $x = $(tpl).appendTo('body');
	$x.mouseenter(function(){clearTimeout(to);$x.show()}).mouseleave(function(){$x.remove()});
	if(!t) t = 3;
	var to = setTimeout(function(){$x.remove()},1000*t);
}

var xdone = [];

wd.firstload=function(f){if(totR<1){f.call();return} xdone.push(f)}

wd.ajaxGet=function (url, callBack, block){
  var x = $.ajax({
    url:addtok(url),
    type: 'GET',
    // dataType: type,
    beforeSend: function(){
      wd.totR++;
      //Block UI.
      if (block){
				msgInfo('Đang xử lý ...')
        // $(block).block()
      }
    },
    success: function(res){
			if(res && res.rdr) {wd.location.href = res.rdr;return}
      if(res && res.e){
				msgInfo(res.e, 1, res._t)
				return
      }
			
			if(res && res.m){
				msgInfo(res.m, 0, res._t);				
			}
			var t = 0;
			if(res && res._t) t = 1000*res._t;
			if(0<t){
				setTimeout(function(){				
					if (typeof callBack == 'function') callBack(res)
				},t)
			}else{
	      if (typeof callBack == 'function') callBack(res)				
			}			
    },
    error: function(jqXHR, textStatus, errorThrown){
    },
    complete: function(){
      //Unblock UI.
      wd.totR--;
      if(0<xdone.length && wd.totR<1){
				xdone.shift().call(null)
      }
      if (block){
				msgInfo('')
        // $(block).unblock()
      }
    }
  })
}
wd.ajaxPost=function (url, data, callBack, sync){
  ajaxPostBlock(url,data, callBack, null,'', sync);
}
wd.ajaxPostBlock=function (url, data, callBack, block, msgblock, sync){
	var tmp = '', x;
	if(xUI){tmp=xUI.message;if(msgblock) xUI.message = msgblock;}
	if(undefined == sync) sync = false;
  x = $.ajax({
		async:!sync,
    url:addtok(url),
    type: 'POST',
    // dataType: 'json',
    data: data,
    beforeSend: function(){
      wd.totR++;
      //Block UI.
      if (block){
				// msgInfo('Please wait ...')
        $(block).block()
      }
    },
    success: function(res){
			if(res && res.rdr) {wd.location.href = res.rdr;return}
    	if(res && res.e) {
				msgInfo(res.e, 1, res._t)
	      return
	    }
			if(res && res.m){
				msgInfo(res.m, 0, res._t);
			}
			var t = 0;
			if(res && res._t) t = 1000*res._t;
			if(0<t){
				setTimeout(function(){
					if (typeof callBack == 'function') callBack(res)
				},t)
			}else{
				if (typeof callBack == 'function') callBack(res)
			}
      
    },
    error: function(jqXHR, textStatus, errorThrown){
    },
    complete: function(){
      wd.totR--;
			if(0<xdone.length && wd.totR<1){
				xdone.shift().call(null)
      }
      //Unblock UI.
      if (block){
				// msgInfo('')
        $(block).unblock()
      }
			if(xUI) xUI.message=tmp
    }
  });

}

Geekutil.pager = function(id, t, p, callback){
  var perPage = 10, ohtml='{{{opt}}}', prehtml = '{{start}} to {{end}} of {{total}}';
  if(wd.C_D_PT) perPage = 1*C_D_PT;
  var that = this,idlist = id.split(',');
  that.id = idlist[0];
  if(1 < idlist.length) that.id2 = idlist[1];
  //that.callback = callback;
  that.total = 1*t;
  that.page = 1*p;
  var html = "<div class='mpager' id='_p{{id}}'><span></span> "
  +"<i class='glyphicon glyphicon-fast-backward'></i> <i class='glyphicon glyphicon-backward'></i> "
  +"<select class='page'></select> "
  +"<i class='glyphicon glyphicon-forward'></i> <i class='glyphicon glyphicon-fast-forward'></i></div>";
  var html2 = "<div class='mpager' id='_p{{id2}}'><span></span> "
  +"<i class='glyphicon glyphicon-fast-backward'></i> <i class='glyphicon glyphicon-backward'></i> "
  +"<select class='page'></select> "
  +"<i class='glyphicon glyphicon-forward'></i> <i class='glyphicon glyphicon-fast-forward'></i></div>";

  var getInfo = function(total, page){
    if(arguments.length == 2){
      that.total = total;
      that.page = page;
    }
    that.pages = Math.floor((that.total-1)/perPage) + 1;
    that.show = true;
    if (that.pages < 1) that.show = false;
    that.start = Math.min(that.total,(that.page-1) * perPage + 1);
    that.end = Math.min(that.total, that.start + perPage - 1);
  }
  that.opt = function(){
    var tmp = '';
    if (that.pages) repeat(that.pages, function(i){tmp += '<option value="'+i+'">'+ i +'</option>'});
    return tmp;
  }
  that.$el=null;
  that.getPage = function(){
    // var tmp = that.$el.find('.page').val();
    // if (!tmp || isNaN(tmp)) return 1;
    // return parseInt(tmp);
		return datapage
  }
  that.fireload = function(){
		callback.call(null, datapage)
	}
  that.subone = function(v){
    if(!v) v = 1;
    var oldPage = that.page, opgs = that.pages;
    that.total = that.total - v;
    if(that.end<v+that.start) that.page = Math.max(1, that.page - 1);
    if(that.total<1) that.page = 1;
    that.reload(that.total, that.page);
    return (oldPage < opgs) || (oldPage != that.page) || (that.end < that.total)
  }
  that.reload = function(t, p){
    getInfo(t,p);
    that.$el.find('span').html(mr(prehtml, that));
    that.$el.find('select').html(mr(ohtml, that)).val(that.page);
    if(that.id2){
      that.$el2.find('span').html(mr(prehtml, that));
      that.$el2.find('select').html(mr(ohtml, that)).val(that.page);
    }
    datapage = p;
  }
  var refresh = function(){
    getInfo(that.total,that.page);
    that.$el.find('span').html(mr(prehtml, that));
    that.$el.find('select').html(mr(ohtml, that)).val(that.page);
    if(that.id2){
      that.$el2.find('span').html(mr(prehtml, that));
      that.$el2.find('select').html(mr(ohtml, that)).val(that.page);
    }
  }
  getInfo();
  $('#' + id).html(mr(html, that));
  if(that.id2){
    $('#' + that.id2).html(mr(html2, that));
    that.$el2 = $('#_p' + that.id2);
  }
  that.$el = $('#_p' + id);

  that.$el.find('span').html(mr(prehtml, that));
  that.$el.find('select').html(mr(ohtml, that));
  if(that.id2){
    that.$el2.find('span').html(mr(prehtml, that));
    that.$el2.find('select').html(mr(ohtml, that));
  }
  var updatePage = function(p){
    that.$el.find('.page').val(p);
    if(that.$el2) that.$el2.find('.page').val(p);
    that.page = p;
  }
  //Event pager
  var id2sel = '';
  if(that.id2){
    id2sel = ',#_p'+that.id2;
  }
  var datapage = that.page;
  $('#_p'+id + id2sel).find('.glyphicon glyphicon-fast-forward').click(function(e){
    
    if(datapage < that.pages){
      datapage = that.pages;
      updatePage(datapage);
      callback.call(null,datapage);

      refresh();
    }
  });

  $('#_p'+id + id2sel).find('.glyphicon glyphicon-fast-backward').click(function(e){
    
    if(1 < datapage){
      datapage = 1;
      updatePage(datapage);
      callback.call(null,1);

      refresh()
    }
  });
  $('#_p'+id + id2sel).find('.page').change(function(e){
    
    if(datapage != $(this).val()){
      datapage =  parseInt( $(this).val());
      that.page = datapage;
      callback.call(null,datapage);

      refresh()
    }
  });
  $('#_p'+id + id2sel).find('.glyphicon glyphicon-forward').click(function(e){
    
    if(datapage< that.pages){
      datapage = datapage+1;
      updatePage(datapage);
      callback.call(null,datapage);

      refresh()
    }
  });
  $('#_p'+id + id2sel).find('.glyphicon glyphicon-backward').click(function(e){
    
    if(1 < datapage){
      datapage = datapage-1;
      updatePage(datapage);
      callback.call(null,datapage);

      refresh()
    }
  });
}
wd.ajax_upload = function(selector, form, url){
	$(selector).change(function(event){
		var error = 0;
		/* check the file types */
		$.each($(this)[0].files, function(index, eachfile){
			var fileext = eachfile.type.substring(6).toLowerCase();
			if($.inArray(fileext, ['gif','png','jpg','jpeg']) == -1) {
				alert(eachfile.name +" is not a valid image file");
				error=1;
				return false;
			}
			if(eachfile.size > 10000000) {
				alert(eachfile.name +" is bigger than 10MB");
				error=1;
				return false;
			}
		});
		if(!error){
			var data = new FormData(form);

			var opts = {
			    url: url,
			    data: data,
			    cache: false,
			    contentType: false,
			    processData: false,
			    type: 'POST'			    
			};
			if(data.fake) {
			    // Make sure no text encoding stuff is done by xhr
			    opts.xhr = function() { var xhr = jQuery.ajaxSettings.xhr(); xhr.send = xhr.sendAsBinary; return xhr; }
			    opts.contentType = "multipart/form-data; boundary="+data.boundary;
			    opts.data = data.toString();
			}
			$(selector).parent().find('.fileupload-icon').show();
			
			$.ajax(opts).done(function(data){
				console.log(data)
			}).always(function(){
						$(selector).parent().find('.fileupload-icon').hide();
					});
			$(selector).val('');
		}

	});
		
}
function hideAllErr(){
  //Hide all;
  $('form .error').removeClass('error');
  $('form .text-error').remove()
}
wd.beforePost=function (){
  var valid = true;
  hideAllErr();
  var strErr = '';
  if (typeof validation != 'undefined') {
    var result = validateObj(validation);
    for (var key in result) {
      valid = false;
      var controlGroup = $('#' + key).parent();
			if (controlGroup.hasClass('input-group')) controlGroup = controlGroup.parent(); 
			
      controlGroup.addClass('error').append(' <div class="text-error">'+result[key]+'</div>');

      strErr += result[key] + '<br/>';
    }
  }
	if($('#alert').length){
	  if(strErr) {
			$('#alert').show();$(wd).scrollTop($('#alert').position().top)
		}
	  $('#alert').html('<div class="alert-error" style="padding-left:10px">'+strErr+'</div>');
	}
  return valid;
}
wd.showErr = function (id, valobj){
  var result = validateObj(valobj);
  var valid = true;
  $('#' + id +' .form-group').removeClass('error');
  $('#' + id +' .text-error').remove();
  for (var key in result) {
    valid = false;
    var controlGroup = $('#' + key).parent();

    controlGroup.append(' <div class="text-error">'+result[key]+'</div>')
  }
  return valid
}
wd.validateObj=function (valobj){
  var result = {};
  var inputValue = '';
  var ignr = [];
  if (typeof validationIG === 'object') ignr = validationIG;
  if (typeof valobj != 'undefined'){
		$.each(valobj, function(key, vv){
			if (!vv || 0<=ignr.indexOf(key)) return true;
			inputValue = Geekutil.get(key);
			var xValue,value;
			if($.isArray(vv)){
				xValue = vv
			} else xValue = [vv];
			for(var _ii in xValue){
				value = xValue[_ii];
				if (typeof inputValue == 'undefined') return;
				if (typeof value.jsfunction == 'function'){
					var tmp = value.jsfunction.call(null, key, inputValue);
					if (tmp != '' && tmp != undefined ) {
						result[key] = tmp;
					}
				}
				if ((value.minlength && inputValue.length < value.minlength) ||
				(value.maxlength && value.maxlength < inputValue.length) ||
				(undefined != value.minvalue && (getNum(inputValue) < value.minvalue)) ||
				(undefined != value.maxvalue && (value.maxvalue < getNum(inputValue)))) {
					result[key] = value.message;
				} else if (value.pattern) {
					var RegObj = null,x;
					if (typeof value.pattern == 'string'){
						x = value.pattern;
						if('/' == x[0]) RegObj = new RegExp(x.slice(1,-1));
						else RegObj = new RegExp(x)
					} 
					if (false == RegObj.test(inputValue)) {
						if(result[key]) result[key] += '. ';
						result[key] = value.message;
					}
				}
			}
		})
  }
  return result
}

function reloadSelect(idSource, urlSource, target, selValue){
	if($('#'+target).length<1) return;
  var data = null;
  if (typeof myTriggerSource == 'function'){
    data = myTriggerSource.call(null, idSource)
  } else {
    data = {id:Geekutil.get(idSource)}
  }
	
  ajaxPostBlock(urlSource,data,function(r){
    fillSelect(target,r,selValue)
  },$('#'+target).parent())
}

//Text multi
function rmIfexist(values, v){
	for(var ii = 0; ii<values.length; ii++){
			if((''+values[ii][0]) == (''+v[0])){
				values.splice(ii,1);return
			}
	}
}
function arrOfArr(v){
	if(!v || typeof v != 'object') return 0;
	if(v.length < 1) return 0;
	if(typeof v[0] != 'object') return 0;
	return 1
}

var TM = function(id, allowEnterNew, url, values, max, instantS){
  var that = this, rmt = 0;  
  if(!allowEnterNew && '' === url) url = [];   
  if(typeof url == 'string'){
		if(url && '@' == url[0]){
			rmt = 1;url = url.substr(1)
		}
	}
  var obj = new $.TextboxList('#'+id,{
  bitsOptions: {plh:'type to search',addOnBlur:allowEnterNew},max:max,
  unique: true, plugins: {autocomplete: {queryRemote:rmt, onlyFromValues:!allowEnterNew, placeholder:false,remote: {url:url}}}});
  var autocomplete = obj.plugins['autocomplete'];

  var rr = [];
	that.dis = function(){
		// that.offcb();
		obj.readonly();
		var $pr = $('#'+id).parent();
		$pr.find('span i').hide();
		$pr.find('.textboxlist').addClass('readonly')
	}
	that.mobi = function(){
		$('#'+id).parent().find('.textboxlist-bit-editable-input').mobiNum()
	}
	that.TMO = function() {return obj}
	that.setmax = function(x){obj.setmax(x)}
  that.reset = function(){Geekutil.set(id,''); obj.reset();mTrigger && mTrigger.call()}
  that.set = function (v, af){
    var arr = [],va, arOfAr = arrOfArr(v);
		if(arOfAr) af = 1;
    if(v){
      if(af){
				if(arOfAr){
					for(var jj=0; jj<v.length;jj++){
						va = v[jj];
						autocomplete.addValue(va);
						rmIfexist(rr, va);rmIfexist(arr, va);
		        rr.push(va);arr.push(va)
					}
				} else {
					autocomplete.addValue(v);
					rmIfexist(rr, v);
        	rr.push(v);arr.push(v)
				}        
      } else {
        if (typeof v != 'object') v = [''+v];
        for(var ii=0; ii<rr.length; ii++){
          if(-1<v.indexOf(''+rr[ii][0]) || -1<v.indexOf(parseInt(rr[ii][0]))){
            arr.push(rr[ii])
          }
        }
      }
    }
    obj.reset();
    Geekutil.set(id,'');
    if(0 < arr.length) obj.setValues(arr)    
  }
  that.getstr = function(){
    var vw = '';
    $('#'+id).parent().find('.textboxlist-bits .textboxlist-bit-box').each(function(){
      vw += ', ' + this.textContent
    });
    return vw.substr(2)
  }
  that.get = function(){
    return Geekutil.get(id)
  }
	that.getA = function(){return mclone(autocomplete.getValues())}
  that.setA = function(d,v){
    if(d != null && typeof d === 'object'){
      autocomplete.setValues(d);rr=d;
      that.set(v)
    }
  }
	var xIlk = null;
	that.destroy = function(){xIlk && xIlk.remove()}
	var createIS = function(arrData){
		if(instantS){
			xIlk = new createInstLK(arrData, function(v){
			that.set(arrParseInt(v))	
			},'#_s'+id,{label:instantS.L},'poptm'+id, instantS.M)
		}
	}
  if(url){
    if(!rmt && typeof url == 'string')
      ajaxGet(url,function(r){
				var tmp = [], x = 0;
				$.each(r, function(k,v){
					if(v.length < 2)	return false;
					x = 1;
					tmp.push([v[0], v[1]])
				})
				if(x) r = tmp;
        autocomplete.setValues(r);rr=r;
        that.set(values);
				//pop instance search
				createIS(rr)
      })
    if(typeof url === 'object'){
      autocomplete.setValues(url);rr=url;
      that.set(values)
			//pop instance search
			createIS(rr)			
    }
  }else{
		if(values) obj.setValues(values)
	}
  obj.addEvent('blur', function(o){
    !allowEnterNew && obj.clearInput()
  });
  var mTrigger = null, callbackF = null;
  that.trigger = function(idTarget1, url1, value1){
    mTrigger = function(){      
      var max = url1.length;
      for(var ii = 0; ii<max; ii++){
        reloadSelect(id, url1[ii], idTarget1[ii], value1[ii])  
      }
    }
    obj.addEvent('bitBoxAdd', function(o){
      mTrigger.call()      
    });
    obj.addEvent('bitBoxRemove', function(o){
      mTrigger.call()
    })
		// for(var ii = 0; ii<max; ii++){
		// 			if(value1[ii] < 0) reloadSelect(id, url1[ii], idTarget1[ii], value1[ii])  
		// 		}
    if(typeof url === 'object' && null != values && values.length) mTrigger()
  }  
  that.callback = function(func){
		callbackF = func;
    obj.addEvent('bitBoxAdd', function(o){
      func.call(o,0)
    });
    obj.addEvent('bitBoxRemove', function(o){
      func.call(o,1)
    })
  }
  that.offcb = function(){
    obj.removeEvent('bitBoxAdd');
    obj.removeEvent('bitBoxRemove')
  }
  $('#_r' + id).click(function(e){
    Geekutil.set(id,'');
    obj.reset();
		callbackF && callbackF.call(null, 1);
    mTrigger && mTrigger.call()
  })	
}
Geekutil.TM = function(id, allowEnterNew, url, values, max, instantS){
		addjsifnot('tm/TextboxList.js',$.TextboxList);addcssifnot('tm/TextboxList.css');
		return new TM(id, allowEnterNew, url, values, max, instantS)
}
//nic wrapper
wd.nicW = function(id){
	addjsifnot('nicedit/nicEdit.js',wd.nicEditor);
	var that = this, nic = null;	
	that.get = function(){
		return nic.nicInstances[0].getContent()
	}
	that.set = function(v){
		nic.nicInstances[0].setContent(v);return that
	}
	that.enb = function(v){
		nic.nicInstances[0].disable(''+!!v);return that
	}
	that.add = function(){
		if(nic) return;
		nic =  new nicEditor({iconsPath:getAppUrl()+'/js/admin/nicedit/nicEditorIcons.gif', fullPanel:true}).panelInstance(id, {hasPanel:true})
	}
	that.rem = function(){
		if(!nic) return;
		nic.removeInstance(id);
		nic = null;
	}
	that.focus = function(){$(nic.nicInstances[0].getElm()).focus(); return that}
	that.ins = function(v, html){
		nic.nicInstances[0].restoreRng();
		$(nic.nicInstances[0].getElm()).focus();
		if (html) return nic.nicInstances[0].nicCommand('insertHTML',v);
		nic.nicInstances[0].nicCommand('insertText',v);return that
	}
	that.add()
}
//Date helper
Geekutil.DATE_SEPERATOR = '/';
Geekutil.DEC = 0;
function setUpCalValue(idCal, cal){
  var tmp = new Date(cal.selection.print('%Y/%m/%d %H:%M'));
  $('#' + idCal).val(tmp.getTime()/1000);
}
wd.clearCal=function (idCal){
  $('#' + idCal).val('');
  $('#' + idCal + '_h' ).val('');
  $('#' + idCal + '_d' ).val('');
}
var calFma = 'd/m/Y';
calFma = calFma.replace('F','m').replace('M','m').replace('j','d').replace('y','Y');
var yidx = calFma.indexOf('Y')/2;
var didx = calFma.indexOf('d')/2;
var midx = calFma.indexOf('m')/2;
calFma = calFma.replace('d','%d').replace('m','%m').replace('Y','%Y').replace(/\//g,Geekutil.DATE_SEPERATOR);

function hideCal(idCal, cal){
  $('#' + idCal + '_h' ).val(cal.selection.print("%l:%M"));
  $('#' + idCal + '_d' ).val(cal.selection.print(calFma));
  $('#' + idCal + '_p' ).html(Geekutil.AP[cal.selection.print("%p")]);
  setUpCalValue(idCal, cal);
}
function setCalTime(idCal, cal){
  //get time
    var tmp = $('#' + idCal +'_h').val().replace(/ /g, '').split(':');
    var timeNumber = 0;
    if(1 == tmp.length){
      if(tmp[0]) {
        if($('#' + idCal + '_p').html() == Geekutil.AP.PM){
          timeNumber = parseInt(tmp[0]) + 1200
        } else {
          timeNumber = parseInt(tmp[0])
        }
      } else {
        var d = new Date();
        timeNumber = d.getHours()*100 + d.getMinutes()
      }
    }
    if(2 == tmp.length){
      if($('#' + idCal + '_p').html() == Geekutil.AP.PM){
        timeNumber = parseInt(tmp[0])*100 + parseInt(tmp[1]) + 1200
      } else {
        timeNumber = parseInt(tmp[0])*100 + parseInt(tmp[1])
      }
    }
  cal.setTime(timeNumber)
}
function parseDateFromStr(day, hour, ampm){
  var rv = new Date();
  rv.setMilliseconds(0);
  if (day){
    var tmp1 = day.split(Geekutil.DATE_SEPERATOR);
    if (3 == tmp1.length){
      rv.setFullYear(tmp1[yidx], (tmp1[midx] - 1), tmp1[didx])
    }
  }
  if (hour){
    var tmp2 = hour.replace(/[^\d\s:]/g,'');
    tmp2 = tmp2.split(/:|\s/);
    if(tmp2.length){
      if(isNaN(tmp2[0])) tmp2[0] = rv.getHours();
      tmp2[0] = Math.min(tmp2[0], 23);
      if (tmp2[0]<12 && ampm == Geekutil.AP.PM){
        tmp2[0] = parseInt(tmp2[0]) + 12
      }
      if (1 == tmp2.length){
        rv.setHours(tmp2[0], 0)
      }
      if (1 < tmp2.length){
        if(isNaN(tmp2[1])) tmp2[1] = rv.getMinutes();
        tmp2[1] = Math.min(tmp2[1],59);
        rv.setHours(tmp2[0], tmp2[1])
      }
    }
  }
  if(isNaN(rv.getTime())) return new Date().getTime();
  return rv.getTime()
}
function parseUpdateCal(idCal){
  if(!Geekutil.get(idCal+'_d')){
    clearCal(idCal);return
  }
  var dt = parseDateFromStr(Geekutil.get(idCal+'_d'), Geekutil.get(idCal + '_h'),$('#' + idCal +'_p').html());

  Geekutil.set(idCal, dt/1000);
  var dtObj = new Date(dt);
  Geekutil.set(idCal + '_h', getTimeStr(dtObj));
  Geekutil.set(idCal + '_d', getCalDatestr(dtObj));//getDateStr(dtObj)
}
function addjscal() {
	if(!wd.Calendar){
		addjsifnot('cal/jscal2.js');addjsifnot('cal/en.js');
	}
	addcssifnot('cal/jscal2.css');
}

function getTimeStr(dtObj){
 return ('0'+(12<dtObj.getHours()?(dtObj.getHours() - 12):dtObj.getHours())).substr(-2) + ':' + ('0'+dtObj.getMinutes()).substr(-2)
}
Geekutil.AP = {AM:'AM',PM:'PM'};
var arr_sm = {'01':'Jan', '02':'Feb', '03':'Mar', '04':'Apr', '05':'May', '06':'Jun', '07':'Jul', '08':'Aug', '09':'Sep', '10':'Oct', '11':'Nov', '12':'Dec'},
arr_fm = {'01':'January', '02':'February', '03':'March', '04':'April', '05':'May', '06':'June', '07':'July', '08':'August', '09':'September', '10':'October', '11':'November', '12':'December'},
js2calformat = {'d':'d','j':'e', 'F':'B', 'M': 'b', 'm':'m', 'y':'y', 'Y':'Y'};
var C_D_FMA = 'd/m/Y';
var calformat = C_D_FMA.split('/');
calformat[0] = '%'+ js2calformat[calformat[0]];
calformat[1] = '%'+ js2calformat[calformat[1]];
calformat[2] = '%'+ js2calformat[calformat[2]];
calformat = calformat.join(Geekutil.DATE_SEPERATOR);
wd.getDateStr=function (dtObj){
	addjscal();
  return getDispDate(getCalDatestr(dtObj))
	// var tmp = calFma.replace('%Y', dtObj.getFullYear());
	// tmp = tmp.replace('%d', ('0'+dtObj.getDate()).substr(-2));
	// tmp = tmp.replace('%m', ('0'+(dtObj.getMonth() + 1)).substr(-2));
	// return tmp;
}
function getCalDatestr(dtObj) {
	return Calendar.printDate(dtObj,calFma)
}
function getDispDate(editdate) {
	var dinh_dang_ngay = C_D_FMA.split('/');
		  var pM = dinh_dang_ngay.indexOf('M');
		  var pF = dinh_dang_ngay.indexOf('F');
		  var pJ = dinh_dang_ngay.indexOf('j');
		  var $new = editdate;
		  if (!$new) return '';
		  if(pM>=0){
				var arr_m = arr_sm;
				var arr_org = editdate.split(Geekutil.DATE_SEPERATOR);
				arr_org[pM] = arr_m[arr_org[pM]];
				$new = arr_org.join(Geekutil.DATE_SEPERATOR)
		  }
	  	if(pF>=0){
				var arr_m = arr_fm;
				var arr_org = editdate.split(Geekutil.DATE_SEPERATOR);
				arr_org[pF] = arr_m[arr_org[pF]];
				$new = arr_org.join(Geekutil.DATE_SEPERATOR)
		  }
		if(pJ>-1){
			var arr_org = $new.split(Geekutil.DATE_SEPERATOR);
			arr_org[pJ] = ""+(1*arr_org[pJ]);
			$new = arr_org.join(Geekutil.DATE_SEPERATOR)
		}
		var pN = dinh_dang_ngay.indexOf('y')
		if(pN>0){
			var arr_org = $new.split(Geekutil.DATE_SEPERATOR)
			arr_org[pN] = arr_org[pN].slice(2)
			$new = arr_org.join(Geekutil.DATE_SEPERATOR)
		} 
    return $new
}
Geekutil.Cal = function(idCal, notime, pt, readonly){
	addjscal();
  var self = this;	
  self.getstr = function(){
    if (notime){
	  	
			return getDispDate($('#' + idCal + '_d').val());
      //return $new
    }else{
      if(!Geekutil.get(idCal+'_d')) return '';
	  	
      return getDispDate($('#' + idCal + '_d').val()) +' '+$('#' + idCal + '_h').val()+' '+$('#' + idCal + '_p').html()
    }
  }
	var drSel = function(){
		$('#' + idCal + '_i').addClass('invisible').css('width',1);
		$('#' + idCal + '_d').on('click', function(){cal.popup(idCal + '_i')})
	}
  self.get = function(){return $('#' + idCal).val()}
  var oldst = '';
  var cal = Calendar.setup({
    onSelect: function(){
      this.hide();
      hideCal(idCal, cal)
    },
    onChange: function(e,b){    
      if(oldst != self.getstr()){        
        oldst = self.getstr();        
        if(typeof self.change == 'function') self.change.call(null,b)
      }
    },
    showTime: 12,
    animation: false,
    weekNumbers: true
  });

  self.resetOld = function(){oldst = self.getstr()}
	if(!notime){
		drSel()
	}else cal.args.showTime = false;
	self.tsel=function(){
		drSel()
	}
  self.set = function(pt){
  	if(pt){
      var dt = new Date(pt * 1000);
      $('#' + idCal + '_d').val(getCalDatestr(dt));
      if (dt.getHours()>11) {
        $('#' + idCal + '_p').html(Geekutil.AP.PM);
        $('#' + idCal + '_h').val(getTimeStr(dt));
      }else{
        $('#' + idCal + '_p').html(Geekutil.AP.AM);
        $('#' + idCal + '_h').val(dt.getHours() + ':' + ('0'+dt.getMinutes()).substr(-2));
      }
      //Initial default value
      $('#' + idCal).val(pt)    
    } else if (!notime){			
      var dt = new Date();
      if (dt.getHours()>11) {
        $('#' + idCal + '_p').html(Geekutil.AP.PM);
      } else $('#' + idCal + '_p').html(Geekutil.AP.AM);
    }  
  	oldst = self.getstr()
  }
  self.set(pt);
  self.dis = function(d){
    if(d<1) clearCal(idCal);
    d&&self.set(d);
		$('#' + idCal + '_i,#' + idCal + '_r').hide();
		if($('#' + idCal + '_p').is(':visible') && $('#' + idCal + '_p').length){
			$('<span>'+$('#' + idCal + '_p').text()+'</span>').insertAfter('#' + idCal + '_p')
		}
    $('#' + idCal + '_d,#' + idCal + '_h').prop('disabled',1)
  }
  self.enb = function(){
    clearCal(idCal);
		$('#' + idCal + '_i,#' + idCal + '_r').show();
    $('#' + idCal + '_d').prop('disabled',0)
  }
  self.fors = function(){
    $('#' + idCal + '_i').parent().addClass('invisible').css('width',1);
    $('#' + idCal + '_d').on('click', function(e){
      cal.popup(idCal + '_i')
    })    
  }
	self.clear = function(){
		clearCal(idCal);
		if(oldst && typeof self.change == 'function') self.change.call(null,'');
		oldst = ''
	}
	
	cal.setLanguage('en');
	
  if (!readonly) {
  
  cal.manageFields(idCal + '_i', idCal +'_d', calFma);
  
  if (!notime) {
    $('#' + idCal + '_i').click(function(e){      
      setCalTime(idCal, cal);    
      //Show date picker
      // cal.popup(idCal + '_i');      
    });
    $('#' + idCal +'_p').click(function(e){
      if($(this).html() == Geekutil.AP.AM) {
        $(this).html(Geekutil.AP.PM);
      } else $(this).html(Geekutil.AP.AM);      
      setCalTime(idCal, cal);
      parseUpdateCal(idCal);
      e.preventDefault()
    })
  }
  $('#' + idCal + '_r').click(function(e){
    self.clear()    
  });
  $('#' + idCal + '_d').change(function(e){
    parseUpdateCal(idCal);
    if(oldst != self.getstr()){
      oldst = self.getstr();
      var b = null, c = $('#'+idCal).val();
      if(c) b = new Date(c*1000);
      if(typeof self.change == 'function') self.change.call(null,b)
    }    
  });
  $('#' + idCal + '_h').change(function(e){
      parseUpdateCal(idCal)
    })
  }
}
function cakWeek(cak1, cak2){
  var d = new Date();
  var day = d.getDay();
  var diff = d.getDate() - day + (day == 0 ? -6:1); 
  var x = d.setDate(diff);
  cak1.dis(x/1000);
  cak2.dis(86400*6+x/1000)
}
function cakDay(cak1, cak2, prev){
  var prevtime = 0,d = new Date();
  if (prev != undefined) prevtime = (prev - 1)*86400;
  cak1.dis(d.getTime()/1000 - prevtime);
  cak2.dis(d.getTime()/1000)
}
function cakMonth(cak1, cak2){
  var d = new Date();
  cak1.dis(d.setDate(1)/1000);
  cak2.dis((d.setMonth(d.getMonth()+1)-86400000)/1000)
}
function cakYear(cak1, cak2){
  var d = new Date();
  cak1.dis(new Date(d.getFullYear(),0,1).getTime()/1000);
  cak2.dis(new Date(d.getFullYear(),11,31).getTime()/1000)
}
function cakQuarter(cak1, cak2){
  var d = new Date();
  var q = Math.ceil((d.getMonth()+1)/3);
  var sm = (q-1)*3;
  var em = sm + 2;
  var eday = 31;
  if(em == 5 || em == 8) eday = 30;
  cak1.dis((new Date(d.getFullYear(), sm, 1)).getTime()/1000);
  cak2.dis((new Date(d.getFullYear(), em, eday)).getTime()/1000)
}
wd.sCak=function(n){
  var cak1 = wd['cakf'+n];
  var cak2 = wd['cakt'+n];
  var o = '';
  $('#'+n).on('keyup change', function(){
    var v = this.value;
    if(o != v){
      o = v;
      if('0' == v){
        cak1.enb();cak2.enb();
        cak1.fors();cak2.fors()
      }
      if('currD' == v) cakDay(cak1, cak2);
			if('currD7' == v) cakDay(cak1, cak2, 7);
			if('currD30' == v) cakDay(cak1, cak2, 30);
      if('currW' == v) cakWeek(cak1, cak2);
      if('currM' == v) cakMonth(cak1, cak2);
      if('currQ' == v) cakQuarter(cak1, cak2);
      if('currY' == v) cakYear(cak1, cak2)
    }
  })
  $('#'+n).change()
}
wd.isValidMail=function (v){
  if(!v) return 1;
  var valMx = /^[\w\.-]*@[\da-zA-Z_]+?([\._-][\da-zA-Z]{1,})+$/;
  var arrv = v.split(/,|\s/);
  for(var ii = 0; ii<arrv.length; ii++){
    if (arrv[ii] && (false == valMx.test(arrv[ii]))){
      return 0
    }
  }
  return 1
}
var dauthapphan = '.', phannhom=',';
var tpcode = (','==dauthapphan)?',188':',110,190';
//format
wd.formatNum=function (v,t){
  if(''===v||null===v||undefined===v) return '';
 if(typeof v == 'number') v = v.toFixed(Geekutil.DEC);
  // var tmp = Geekutil.DEC;
  // if(undefined != t) Geekutil.DEC = t;
  // v = getNum(v);
	// Geekutil.DEC = tmp;
  var s = phannhom;
  v = ''+v;
  v = v.replace('--','-');
  var arr = v.split('.');  
  var fp = arr[0].replace(/\B(?=(\d{3})+(?!\d))/g, s);  
  if(1<arr.length){
		if(Geekutil.DEC<1) return fp;
		return fp+dauthapphan+arr[1].substr(0,Geekutil.DEC);
	}
  return fp
}
var sepReg = new RegExp(phannhom.replace('.','\\.'),'g');
var dauthapphanRs = dauthapphan.replace('.','\\.');
var pdecReg = new RegExp('^(-?\\d+'+dauthapphanRs+'?\\d*|' + dauthapphanRs+'\\d+)$');
wd.getNum=function (v, str){
  if(!v) return 0;v=''+v;
	v = v.replace('--','-');
  v = v.replace(sepReg,'');//ths sep
  v = v.replace(dauthapphan,'.');//dec delim
  if(isNaN(v)) return 0;
  if(str) return v;
  return (1*v).toFixed(Geekutil.DEC)*1
}
wd.mchange=function(sel, cb, bf){
	var $el = $(sel), old='';
	var x = $el.val(), nv = null;
	$el.focus(function(){old = $el.val()})
	$el.blur(function(){
		nv = $el.val();
		if(nv == old) return;
		if(bf){
			var ck = bf.call(null,x,nv);
			if(!ck){$el.focus().val(x);return}
		}		
		if(nv != x){cb.call(null, nv); x=nv}
	})
}
$.fn.decOnly = function(max, sign){
  return this.each(function(){
    var hsg = 0, hdec = 0,pst=0, $el=$(this), key = 0;
    var old = this.value;
		function dpt(){
			if(key) return;
			
			var t = $el.val();
			t=t.replace(/\D/g,'');
			$el.val(formatNum(t))
		}
		function dfc(){
			hsg = /-/.test(this.value);
			hdec = -1<this.value.indexOf(dauthapphan);
      old = this.value
		}
		function dku(e){
			key = 0;
			if(old != this.value){
				hsg = /-/.test(this.value);hdec = -1<this.value.indexOf(dauthapphan);
				if('-'===this.value||''===this.value) return;
				if(max && max<getNum(this.value)) {
					this.value=formatNum(''+max,0);
					old = this.value;return
				}
        var tmp = formatNum(getNum(this.value, 1));        				
        if(tmp != this.value){
          var x = this.value.length - this.selectionStart;					
          this.value = tmp;          
          if(this.setSelectionRange)
          this.setSelectionRange(tmp.length - x, tmp.length - x)
        }
        old = this.value        
      }
		}
		function dkd(e){
			pst = 0;key = 1;
			// Allow: backspace, delete, tab, escape, dot, comma, and enter
      if ((!hsg && sign && (e.which == 189 || e.which == 109 || e.which == 173)) || e.which == 46 || e.which == 8 || e.which == 9 || e.which == 27 ||  (!hdec&&tpcode.indexOf(e.which)) || e.which == 13 || 
          // Allow: meta
         (e.metaKey && e.which == 65) || 
					// Allow paste
         ((e.ctrlKey || e.metaKey) && (e.which == 86 || e.which == 88)) || 
          // Allow: home, end, left, right
         (e.which >= 35 && e.which <= 39)){
	    	if((e.ctrlKey || e.metaKey) && (e.which == 86 || e.which == 88)) pst = 1;
        return
      } else {
        // Ensure that it is a number and stop the keypress
        if (e.shiftKey || (e.which < 48 || e.which > 57) && (e.which < 96 || e.which > 105)) {
          e.preventDefault()
        }
        tmp = this.value
      }
		}
		$el.off('focus',$el.data('mint_f'));$el.off('keyup',$el.data('mint_fku'));$el.off('keydown',$el.data('mint_fkd'));$el.off('input propertychange', $el.data('mint_p'));
		if ($el.is('[readonly]')) return true;
    $el.on('focus', dfc);	$el.on('keyup', dku); $el.on('keydown', dkd);
		$el.on('input propertychange', dpt);$el.data('mint_p',dpt);
  	$el.data('mint_f',dfc);$el.data('mint_fkd',dkd);$el.data('mint_fku',dku)
  })
}

$.fn.intOnly = function(max,sign){
  return this.each(function(){    
	  var old = this.value, hsg = 0, pst=0, $el = $(this), key = 0;
		function dpt(){
			if(key) return;
			var t = $el.val();
			t=t.replace(/\D/g,'');
			$el.val(formatNum(t))
		}
		function dku(e){
			key = 0;
			if(old != this.value){
				hsg = /-/.test(this.value);
				if('-'===this.value||''===this.value) return;
				if(max && max<getNum(this.value)) {
					this.value=formatNum(''+max,0);
					old = this.value;return
				}
        var tmp = formatNum(getNum(this.value, 1),0);        				
        if(tmp != this.value){
          var x = this.value.length - this.selectionStart;					
          this.value = tmp;          
          if(this.setSelectionRange)
          this.setSelectionRange(tmp.length - x, tmp.length - x)
        }
        old = this.value        
      }
		}
		function dfc(){
			hsg = /-/.test(this.value);      
      old = this.value
		}
		function dkd(e){
			pst=0;key=1;
			// Allow: minus backspace, delete, tab, escape, and enter
      if ((!hsg && sign && (e.which==189 || e.which==109 || e.which == 173)) || e.which == 46 || e.which == 8 || e.which == 9 || e.which == 27 || e.which == 13 || 
          // Allow: meta
         ((e.ctrlKey || e.metaKey) && e.which == 65) || 
					// Allow paste
         ((e.ctrlKey || e.metaKey) && (e.which == 86 || e.which == 88)) || 
          // Allow: home, end, left, right
         (e.which >= 35 && e.which <= 39)){    
				if((e.ctrlKey || e.metaKey) && (e.which == 86 || e.which == 88)) pst = 1;
        return
      } else {
        // Ensure that it is a number and stop the keypress
        if (e.shiftKey || (e.which < 48 || e.which > 57) && (e.which < 96 || e.which > 105)) {
          e.preventDefault()
        }        
      }
		}
    $el.off('focus',$el.data('mint_f'));$el.off('keyup',$el.data('mint_fku'));$el.off('keydown',$el.data('mint_fkd'));$el.off('input propertychange', $el.data('mint_p'));
		if ($el.is('[readonly]')) return true;
		$el.on('focus',dfc);$el.on('keyup',dku);$el.on('keydown',dkd);
		$el.on('input propertychange', dpt);$el.data('mint_p',dpt);
		$el.data('mint_f',dfc);$el.data('mint_fkd',dkd);$el.data('mint_fku',dku)
  })
}
wd.cprDateNotime=function (t1,t2){
  //return t1-t2
  var d1 = new Date(t1*1000);
  var d2 = new Date(t2*1000);
  d1.setHours(0,0,0,0);d2.setHours(0,0,0,0);
  return d1 - d2
}
wd.getParam=function(n){
	return decodeURIComponent((new RegExp('[?|&]' + n + '=' + '([^&;]+?)(&|#|;|$)').exec(location.search)||[,""])[1].replace(/\+/g, '%20'))||''
}


function divideContCol(arr){
	var r = {}, col = 2;
	var l = arr.length;
	var i = 0;
	r['r1'] = [];r['r2']=[];
	while (i<l){
		var tmp = {'k':arr[i][0],'v':arr[i][1]};
		if(i%2 == 0) r['r1'].push(tmp)
		if(i%2 == 1) r['r2'].push(tmp)
		i++
	}
	return r
}

function jsSearch(d, txt){	
	txt = txt.replace(/\\/g,'');
	var reg = new RegExp('\\b' + xrm(txt),'i'); 
	var r = [];
	for(var ii = 0; ii<d.length; ii++){
		if(reg.test(xrm(d[ii][1]))) r.push(d[ii])
	}
	return r
}
function addjsifnot(v,chk){
	// var x = $('script[src*="'+v+'"]').length;
	if(chk) return;
	$.ajax({
    url:getAppUrl()+'/js/'+v,
		dataType:'script',
    async:false
	})
	// $('<script src="'+getAppUrl()+'/themes/common/'+v+'"></script>').appendTo('head')
}
function addcssifnot(v,css){
	var x = $('link[href*="'+v+'"]').length;
	if(x) return;
	$('<link rel="stylesheet" type="text/css" href="'+getAppUrl()+'/js/'+v+'"/>').appendTo('head')
}

var _tpl_pop_CM = '';
var createInstLK = function(d, callback, selector, options, popcode, isMulti){
	var xmodal = null, $pel = null;
	var that = this;
	var tpl = '{{#r}}<div class="form-group"><div class="controls"><label class="checkbox"><input type="checkbox" value="{{k}}">{{v}}</label></div></div>{{/r}}';
	if(!isMulti) tpl = '{{#r}}<div class="form-group"><div class="controls"><label class="radio"><input type="radio" name="opt" value="{{k}}">{{v}}</label></div></div>{{/r}}';
	if(!popcode) popcode = genGUID();
	that.show = function(){
		xmodal.show()
	}
	that.hide = function(){
		xmodal.hide()
	}
	that.destroy = function(){$pel.remove()}
	that.fillD = function(ct){
		var hash = divideContCol(ct);
		var x = {};
		x.r = hash.r1;
		$('#r1'+popcode).html(mr(tpl, x));
		x.r = hash.r2;
		$('#r2'+popcode).html(mr(tpl, x));
		$('#'+popcode+' .radio input').click(function(){$('#Y'+popcode).click()})
	}
	function atpl(t){				
		xmodal = new mModal(mr(t,{id:popcode},1), 500);
		$pel = $('#'+popcode);
		//fillcont		
		that.fillD(d);
		//opt
		if(options) $('#label'+popcode).html(options.label)
		//events
		$('#Y'+popcode).click(function(){			
			var tmp = '';
      if(isMulti){
        $pel.find(':checked').each(function(idx){
          tmp += ','+this.value
        });
        tmp = tmp.substr(1)
      } else tmp = $pel.find('input:radio[name=opt]:checked').val();
			if(!tmp){
        alert('Chưa chọn '+(options?' '+options.label:''));return
      }			
      callback.call(this,tmp);
			that.hide()
		})
		$('#N'+popcode).click(function(){that.hide()})
		//search
		$pel.find('.glyphicon-refresh').click(function(){
			$pel.find('.searchenter').val('')
			that.fillD(d)
		})
		$pel.find('.searchenter').keypress(function(e){
			if(13 == e.which) e.preventDefault()
		})
		$pel.find('.searchenter').keyup(function(e){			
			that.fillD(jsSearch(d, this.value))
		})
	 }
	if(_tpl_pop_CM){
		atpl(_tpl_pop_CM)
	}else
		ajaxGet(getAppUrl()+'/themes/admin/tpl/popupCM_Lk.html',function(t){_tpl_pop_CM = t;atpl(t)})
	$(selector).off();
	$(selector).click(function(e){ e.preventDefault();that.show()})
}
wd.serializeFO = function(sel){
  var o = {},key;
  var a = $(sel).serializeArray();
  $.each(a, function() {
		key = this.name.replace(/\[\]/g,'');
    if (o[key] !== undefined) {
      if (!o[key].push) {
        o[key] = [o[key]]
      }
      o[key].push(this.value)
    } else {
      o[key] = this.value
    }
  });
  return o
}
wd.modalWin = function(u, name){
	if(!name) name='_blank';
	if (wd.showModalDialog) {
	wd.showModalDialog(u,name,"dialogWidth:1000px;dialogHeight:997px")
	} else {
	wd.open(u,name,'height=997px,width=1000px,toolbar=no,directories=no,status=no,menubar=no,scrollbars=no,resizable=yes,modal=yes')
	}
}

$.fn.mobiNum = function(){
	return this.each(function(){
		 var $el = $(this), pst = 0;
		function dpt(){
			var t = $el.val();
			t=t.replace(/\D/g,'').substr(0,11);
			$el.val()
		}
		function dku(e){
			if(pst){
				var t = $el.val();
				t=t.replace(/\D/g,'');
				$el.val(getPhone(t)) 
			}
		}
		function dkd(e){
			pst = 0;
		  // Allow: minus backspace, delete, tab, escape, and enter
		  if (e.which == 46 || e.which == 8 || e.which == 9 || e.which == 27 || e.which == 13 || 
			  // Allow: meta
			 ((e.ctrlKey || e.metaKey) && e.which == 65) || 
					 //Allow paste
			 ((e.ctrlKey || e.metaKey) && (e.which == 86 || e.which == 88)) || 
			  // Allow: home, end, left, right
			 (e.which >= 35 && e.which <= 39)){
					if((e.ctrlKey || e.metaKey) && (e.which == 86 || e.which == 88)) pst = 1;
					return
		  } else {
				// Ensure that it is a number and stop the keypress                   
				if (e.shiftKey || (e.which < 48 || e.which > 57) && (e.which < 96 || e.which > 105) || 10<$el.val().replace(/\D/g,'').length) {
					e.preventDefault()
				}
		  }
		}	
		$el.off('keydown', $el.data('m_mobi',dkd));
		$el.off('keyup', $el.data('m_mobi_u',dku));
		$el.off('input propertychange', $el.data('m_mobi_p'));
		$el.on('keydown', dkd);$el.data('m_mobi',dkd);
		$el.on('keyup', dku);$el.data('m_mobi_u',dku);
		$el.on('input propertychange', dpt);$el.data('m_mobi_p',dpt);
	})
}

})(jQuery)