/*
Script: TextboxList.js
	Displays a textbox as a combination of boxes an inputs (eg: facebook tokenizer)

	Authors:
		Guillermo Rauch
		
	Note:
		TextboxList is not priceless for commercial use. See <http://devthought.com/projects/jquery/textboxlist/>. 
		Purchase to remove this message.
*/
(function($){
$.GrowingInput = function(element, options){
	
	var value, lastValue, calc, plh='';
	
	options = $.extend({
		min: 0,
		max: null,
		startWidth: 15,
		correction: 5
	}, options);
	
	element = $(element).data('growing', this);
	
	var self = this;
	var init = function(){
		calc = $('<span></span>').css({
			'float': 'left',
			'display': 'inline-block',
			'position': 'absolute',
			'left': -1000
		}).insertAfter(element);
		$.each(['font-size', 'font-family', 'padding-left', 'padding-top', 'padding-bottom', 
		 'padding-right', 'border-left', 'border-right', 'border-top', 'border-bottom', 
		 'word-spacing', 'letter-spacing', 'text-indent', 'text-transform'], function(i, p){				
				calc.css(p, element.css(p));
		});
		plh = element.attr('placeholder') || 1;
		element.blur(resize).keyup(resize).keydown(resize).keypress(resize);
		resize();
	}
	
	var calculate = function(chars){
		calc.text(chars);
		var width = calc.width();
		return (width ? width : options.startWidth) + options.correction;
	}
	
	var resize = function(){
		lastValue = value;
		value = element.val();
		var retValue = value;
		if(retValue.length<plh.length) retValue = plh;		
		if(chk(options.min) && value.length < options.min){
			if(chk(lastValue) && (lastValue.length <= options.min)) return;
			retValue = str_pad(value, options.min, '-');
		} else if(chk(options.max) && value.length > options.max){
			if(chk(lastValue) && (lastValue.length >= options.max)) return;
			retValue = value.substr(0, options.max);
		}
		element.width(calculate(retValue));
		return self;
	}
	
	this.resize = resize;
	init();
}

var chk = function(v){ return !!(v || v === 0); }
var str_repeat = function(str, times){ return new Array(times + 1).join(str); }
var str_pad = function(self, length, str, dir){
	if (self.length >= length) return this;
	str = str || ' ';
	var pad = str_repeat(str, length - self.length).substr(0, length - self.length);
	if (!dir || dir == 'right') return self + pad;
	if (dir == 'left') return pad + self;
	return pad.substr(0, (pad.length / 2).floor()) + self + pad.substr(0, (pad.length / 2).ceil());
}

})(jQuery);

(function($){
var tsss = function(v, arr){
	for(var ii = 0; ii<arr.length; ii++){
		if(arr[ii] == v) return ii
	}
	return -1
}
	
$.TextboxList = function(element, _options){
	
	var original, container, list, current, focused = false, index = [], blurtimer, events = {}, Ronly = 0;
	var options = $.extend(true, {
        prefix: 'textboxlist',
        max: null,
        unique: false,
        uniqueInsensitive: true,
        endEditableBit: true,
        startEditableBit: false,
        hideEditableBits: true,
        inBetweenEditableBits: false,
        keys: {previous: 37, next: 39},
        bitsOptions: {editable: {}, box: {}},
        plugins: {},
        // tip: you can change encode/decode with JSON.stringify and JSON.parse
        encode: function(o){ 
            return $.grep($.map(o, function(v){		
                v = (chk(v[0]) ? v[0] : v[1]);
                return chk(v) ? v.toString().replace(/,/, '') : null;
            }), function(o){ return o != undefined; }).join(','); 
        },
        decode: function(o){ return o.split(','); }
    }, _options);
	
	element = $(element);	
	var self = this;
	var init = function(){		
		original = element.css('display', 'none').focus(focusLast);
		var tmp = element.prop('class');
		if(tmp && 0 <= tmp.indexOf('span')){
		   tmp = ' '+tmp; 
		} else  tmp = '';
		container = $('<div class="'+options.prefix + tmp + '"/>')
			.insertBefore(element)
			.click(function(e){ 
				if ((e.target == list.get(0) || e.target == container.get(0)) && (!focused || (current && current.toElement().get(0) != list.find(':last-child').get(0)))) focusLast(); 			
			});			
		list = $('<ul class="'+ options.prefix +'-bits"/>').appendTo(container);
		for (var name in options.plugins) enablePlugin(name, options.plugins[name]);		
		afterInit();
	}
	self.setmax = function(m){options.max = m}
	self.readonly = function(){
        Ronly = 1;list.find('.textboxlist-bit-editable:last-child').remove();
        list.find('.textboxlist').addClass('readonly');
        list.find('.textboxlist-bit-box-deletebutton').hide()
    }
	var enablePlugin = function(name, options){
		self.plugins[name] = new $.TextboxList[camelCase(capitalize(name))](self, options);
	}
	
	var afterInit = function(){
		if (options.endEditableBit) create('editable', null, {tabIndex: original.tabIndex}).inject(list);
		addEvent('bitAdd', update, true);
		addEvent('bitRemove', update, true);
		$(document).click(function(e){
			if (!focused) return;
			if (e.target.className.indexOf(options.prefix) != -1){				
				if (e.target == $(container).get(0)) return;				
				var parent = $(e.target).parents('div.' + options.prefix);
				if (parent.get(0) == container.get(0)) return;
			}
			blur();
		}).keydown(function(ev){
			if (!focused || !current) return;
			var caret = current.is('editable') ? current.getCaret() : null;
			var value = current.getValue()[1];
			var special = !!$.map(['shift', 'alt', 'meta', 'ctrl'], function(e){ return ev[e]; }).length;
			var custom = special || (current.is('editable') && current.isSelected());
			var evStop = function(){ ev.stopPropagation(); ev.preventDefault(); }
			switch (ev.which){
				case 8:                    
					if (current.is('box')){ 
						evStop();if(Ronly) return
						return current.remove(); 
					}
				case options.keys.previous:
					if (current.is('box') || ((caret == 0 || !value.length) && !custom)){
						evStop();
						focusRelative('prev');
					}
					break;
				case 46:                    
					if (current.is('box')){ 
						evStop();if(Ronly) return
						return current.remove(); 
					}
				case options.keys.next: 
					if (current.is('box') || (caret == value.length && !custom)){
						evStop();
						focusRelative('next');
					}
			}
		});
		setValues(options.decode(original.val()));
	}
	self.hidePlh = function(){
        var x = list.children('.' + options.prefix + '-bit-box').length + 1;
        if(chk(options.max) && 1*options.max<x){
            // list.find('.textboxlist-bit-editable:last-child').hide();return
        }
				var tmp = x<2?options.bitsOptions.plh:'';
				var wd = x<2?'auto':'3px';
        list.find('.textboxlist-bit-editable:last-child').show().find('input').attr('placeholder',tmp).width(wd)//.focus()
  }
	var create = function(klass, value, opt){
		if (klass == 'box'){
			if (chk(options.max) && list.children('.' + options.prefix + '-bit-box').length + 1 > options.max) return false;
			if (options.unique && tsss(uniqueValue(value), index) != -1) return false;		
		}		
		return new $.TextboxListBit(klass, value, self, $.extend(true, options.bitsOptions[klass], opt));
	}
	
	var uniqueValue = function(value){
	 
		return chk(value[0]) ? value[0] : (options.uniqueInsensitive ? value[1].toLowerCase() : value[1]);
	}
	
	var add = function(plain, id, html, afterEl, notfire){
		var b = create('box', [id, plain, html]);
		if (b){
			if (!afterEl || !afterEl.length) afterEl = list.find('.' + options.prefix + '-bit-box').filter(':last');
			b.inject(afterEl.length ? afterEl : list, afterEl.length ? 'after' : 'top', notfire);
		} 
		return self;
	}
	
	var focusRelative = function(dir, to){
		var el = getBit(to && $(to).length ? to : current).toElement();
		var b = getBit(el[dir]());
		if (b) b.focus();
		return self;
	}
	
	var focusLast = function(){
		var lastElement = list.children().filter(':last');
		if (lastElement && getBit(lastElement)) getBit(lastElement).focus();
		return self;
	}
	
	var blur = function(){	
		if (! focused) return self;
		if (current) current.blur();
		focused = false;
		return fireEvent('blur');
	}
	
	var getBit = function(obj){				
		return (obj.type && (obj.type == 'editable' || obj.type == 'box')) ? obj : $(obj).data('textboxlist:bit');
	}
	
	var getValues = function(){
		var values = [];
		list.children().each(function(){
			var bit = getBit(this);
			if (!bit.is('editable')) values.push(bit.getValue());
		});
		return values;
	}
	
	var setValues = function(values){
		if (!values) return;
		var l = values.length - 1;
		$.each(values, function(i, v){
			if (v) add.apply(self, $.isArray(v) ? [v[1], v[0], undefined, undefined, i<l] : [v]);
		});
    self.hidePlh()
	}
	
	var update = function(){
		
		original.val(options.encode(getValues()));
        
	}
	
	var addEvent = function(type, fn){
		if (events[type] == undefined) events[type] = [];
		var exists = false;
		$.each(events[type], function(f){
			if (f === fn){
				exists = true;
				return;
			}
		});
		if (!exists) events[type].push(fn);
		return self;
	}
	
	var fireEvent = function(type, args, delay){
		if (!events || !events[type]) return self;
		$.each(events[type], function(i, fn){		
			(function(){
				args = (args != undefined) ? splat(args) : Array.prototype.slice.call(arguments);
				var returns = function(){
					return fn.apply(self || null, args);
				}
				if (delay) return setTimeout(returns, delay);
				return returns();
			})();
		});
		return self;
	}
	
	var removeEvent = function(type, fn){
		if (events[type]){
			events[type]=[];
			// for (var i = events[type].length; i--; i){
			// 	if (events[type][i] === fn) events[type].splice(i, 1);
			// }
		} 
		return self;
	}
	
	var isDuplicate = function(v){
	 
		return tsss(uniqueValue(v), index);
	}
	
	self.onFocus = function(bit){
		if (current) current.blur();
		clearTimeout(blurtimer);
		current = bit;
		container.addClass(options.prefix + '-focus');		
		if (!focused){
			focused = true;
			fireEvent('focus', bit);self.hidePlh()
		}
	}
	
	self.onAdd = function(bit){
	 
		if (options.unique && bit.is('box')) index.push(uniqueValue(bit.getValue()));
		
		if (bit.is('box')){
			var prior = getBit(bit.toElement().prev());
			if ((prior && prior.is('box') && options.inBetweenEditableBits) || (!prior && options.startEditableBit)){				
				var priorEl = prior && prior.toElement().length ? prior.toElement() : false;
				var b = create('editable').inject(priorEl || list, priorEl ? 'after' : 'top');
                
				if (options.hideEditableBits) b.hide();
			}
		}
	}
	
	self.onRemove = function(bit){
		// if (!focused) return;
		if (options.unique && bit.is('box')){
			var i = isDuplicate(bit.getValue());
			// var i = -1;//index.indexOf(bit.getValue()[0]);
			//if (i != -1) index = index.splice(i + 1, 1);
			if(0<=i) index.splice(i, 1)			
		} 
		var prior = getBit(bit.toElement().prev());
		if (prior && prior.is('editable')) prior.remove();
		focusRelative('next', bit);
	}
	
	self.onBlur = function(bit, all){
		current = null;
		container.removeClass(options.prefix + '-focus');		
		blurtimer = setTimeout(blur, all ? 0 : 200);
	}
	
	self.setOptions = function(opt){
		options = $.extend(true, options, opt);
	}
	
	self.getOptions = function(){
		return options;
	}
	
	self.getContainer = function(){
		return container;
	}
	
	self.isDuplicate = isDuplicate;
	self.addEvent = addEvent;
	self.removeEvent = removeEvent;
	self.fireEvent = fireEvent;
	self.create = create;
	self.add = add;
	self.getValues = getValues;
  self.setValues = setValues;
	self.plugins = [];
  self.clearInput = function(){
    $(element[0]).parent().find('.textboxlist-bit-editable-input').val('')
  }
	self.focusInput = function(){
		setTimeout(function(){
			$(element[0]).parent().find('.textboxlist-bit-editable-input').focus();
			focusLast()
		},100);
	}
  self.reset = function(){
    //$(element[0]).parent().find('.textboxlist-bit-box-deletable').remove();
    list.find('.'+options.prefix + '-bit-box').remove(); index = [];
    self.hidePlh()
  }
	init();
}

$.TextboxListBit = function(type, value, textboxlist, _options){
	
	var element, bit, prefix, typeprefix, close, hidden, focused = false, name = capitalize(type), Ronly = 0,dc = document; 
	var options = $.extend(true, type == 'box' ? {
		deleteButton: true
    } : {
		tabIndex: null,		
		growing: true,
		growingOptions: {},
		stopEnter: true,
		addOnBlur: false,
		addKeys: [13,9]
	}, _options);
	
	this.type = type;
	this.value = value;
	
	var self = this;
	var init = function(){
		prefix = textboxlist.getOptions().prefix + '-bit';
		typeprefix = prefix + '-' + type;
		bit = $('<li/>').addClass(prefix).addClass(typeprefix)
			.data('textboxlist:bit', self)
			.hover(function(){ 
				bit.addClass(prefix + '-hover').addClass(typeprefix + '-hover'); 
			}, function(){
				bit.removeClass(prefix + '-hover').removeClass(typeprefix + '-hover'); 
			});
		if (type == 'box'){
			bit.html(chk(self.value[2]) ? self.value[2] : self.value[1]).click(focus);
			if (options.deleteButton){
				bit.addClass(typeprefix + '-deletable');
				close = $('<a href="#" class="'+ typeprefix +'-deletebutton"/>').click(remove).appendTo(bit);
			}
			bit.children().click(function(e){ e.stopPropagation(); e.preventDefault(); });
		} else {
			element = $('<input maxlength="200" type="text" class=" '+ typeprefix +'-input" placeholder="'+textboxlist.getOptions().bitsOptions.plh+'"/>').val(self.value ? self.value[1] : '').appendTo(bit);
			if (chk(options.tabIndex)) element.tabIndex = options.tabIndex;
			if (options.growing) new $.GrowingInput(element, options.growingOptions);		
			element.focus(function(){ focus(true); }).blur(function(){
				blur(true);

				if (textboxlist.getOptions().bitsOptions.addOnBlur) toBox(); 
			});				
			if (options.addKeys || options.stopEnter){
				element.keydown(function(ev){
					if (!focused) return;
					var evStop = function(){ ev.stopPropagation(); ev.preventDefault(); }
					if (options.stopEnter && ev.which === 13) evStop();
					if (tsss(ev.which, splat(options.addKeys)) != -1){
						evStop();
						toBox();
					}
				});
			}
		}
	}
	
	var inject = function(el, where, notfire){
		switch(where || 'bottom'){
			case 'top': bit.prependTo(el); break;
			case 'bottom': bit.appendTo(el); break;
			case 'before': bit.insertBefore(el); break;			
			case 'after': bit.insertAfter(el); break;						
		}
		textboxlist.onAdd(self);        
    if(!notfire)    
		return fireBitEvent('add');
	}
	
	var focus = function(noReal){
		if (focused) return self;
		show();
		focused = true;
		textboxlist.onFocus(self);
		bit.addClass(prefix + '-focus').addClass(prefix + '-' + type + '-focus');
		fireBitEvent('focus');		
		if (type == 'editable' && !noReal) element.focus();
		return self;
	}
	
	var blur = function(noReal){
		if (!focused) return self;
		focused = false;
		textboxlist.onBlur(self);
		bit.removeClass(prefix + '-focus').removeClass(prefix + '-' + type + '-focus');
		fireBitEvent('blur');
		if (type == 'editable'){
			if (!noReal) element.blur();
			if (hidden && !element.val().length) hide();
		}
		return self;
	}
	
	var remove = function(){
		blur();		
		textboxlist.onRemove(self);
		bit.remove();
    textboxlist.hidePlh();
		return fireBitEvent('remove');
	}
	
	var show = function(){
		bit.css('display', 'block');
		return self;
	}
	
	var hide = function(){
		bit.css('display', 'none');		
		hidden = true;
		return self;
	}
	
	var fireBitEvent = function(type){
    // console.log('fire ', type, textboxlist);
		type = capitalize(type);
		textboxlist.fireEvent('bit' + type, self).fireEvent('bit' + name + type, self);
		return self;
	}
	
    this.is = function(t){
        return type == t;
    }

	this.setValue = function(v){
		if (type == 'editable'){
			element.val(chk(v[0]) ? v[0] : v[1]);
			if (options.growing) element.data('growing').resize();
		} else value = v;
		return self;
	}

 	this.getValue = function(){
		return type == 'editable' ? [null, element.val(), null] : value;
	}
	
	if (type == 'editable'){
		this.getCaret = function(){
 			var el = element.get(0);
			if (el.createTextRange){
		    var r = dc.selection.createRange().duplicate();		
		  	r.moveEnd('character', el.value.length);
		  	if (r.text === '') return el.value.length;
		  	return el.value.lastIndexOf(r.text);
		  } else return el.selectionStart;
		}

		this.getCaretEnd = function(){
 			var el = element.get(0);			
			if (el.createTextRange){
				var r = dc.selection.createRange().duplicate();
				r.moveStart('character', -el.value.length);
				return r.text.length;
			} else return el.selectionEnd;
		}
		
		this.isSelected = function(){
			return focused && (self.getCaret() !== self.getCaretEnd());
		}
		
		var toBox = function(){    
			var value = self.getValue();
			if(!value[1]) return null;
			var b = textboxlist.create('box', value);
			if (b){
				b.inject(bit, 'before');
				self.setValue([null, '', null]);                
				return b;
			}
			return null;
		}
		
		this.toBox = toBox;
	}
	
	this.toElement = function(){
		return bit;
	}
	
	this.focus = focus;
	this.blur = blur;
	this.remove = remove;
	this.inject = inject;
	this.show = show;
	this.hide = hide;
	this.fireBitEvent = fireBitEvent;
	init()
}

var chk = function(v){ return !!(v || v === 0) }
var splat = function(a){ return $.isArray(a) ? a : [a] }
var camelCase = function(str){ return str.replace(/-\D/g, function(match){ return match.charAt(1).toUpperCase()})}
var capitalize = function(str){ return str.replace(/\b[a-z]/g, function(A){ return A.toUpperCase()})}

$.fn.extend({
	
	textboxlist: function(options){
		return this.each(function(){
			new $.TextboxList(this, options);
		});
	}
	
});

})(jQuery);

(function($){
var isIE = 0<navigator.userAgent.indexOf('MSIE');
	
$.TextboxList.Autocomplete = function(textboxlist, _options){	
  var index, prefix, method, container, list, values = [], searchValues = [], results = [], placeholder = false, current, currentInput, hidetimer, doAdd, currentSearch, currentRequest;
	var options = $.extend(true, {
		minLength: 0,
		maxResults: 20,
		insensitive: true,
		highlight: false,
		highlightSelector: null,
		mouseInteraction: true,
		onlyFromValues: false,
		queryRemote: false,
    remote: {
			url: '',
			param: 'search',
			extraParams: {},
			loadPlaceholder: 'Please wait...'
    },
		method: 'standard',
		placeholder: 'Type to search'
	}, _options);
	
	
	var init = function(){
		
		textboxlist.addEvent('bitEditableAdd', setupBit)
			.addEvent('bitEditableFocus', search)
			.addEvent('bitEditableBlur', hide)
			.setOptions({bitsOptions: {editable: {addKeys: false, stopEnter: false}}});
		if (isIE) textboxlist.setOptions({bitsOptions: {editable: {addOnBlur: false}}});
		prefix = textboxlist.getOptions().prefix + '-autocomplete';
		method = $.TextboxList.Autocomplete.Methods[options.method];
		
		container = $('<div class="'+ prefix +'"/>').appendTo(textboxlist.getContainer());
		if (chk(options.placeholder)) placeholder = $('<div class="'+ prefix +'-placeholder"/>').html(options.placeholder).appendTo(container);		
		list = $('<ul class="'+ prefix +'-results"/>').appendTo(container).click(function(ev){
			ev.stopPropagation(); ev.preventDefault();
		});

	}
	var updwidth = function(){
		container.width(textboxlist.getContainer().width())
	}
	var setupBit = function(bit){
		bit.toElement().keydown(navigate).keyup(function(){ search(); });
	}
	
	var search = function(bit){
		if (bit) currentInput = bit;
		if (!options.queryRemote && !values.length) return;
		var txtsearch = $.trim(currentInput.getValue()[1]);
		if (txtsearch.length < options.minLength) showPlaceholder();
		if (txtsearch == currentSearch) return;
		currentSearch = txtsearch;
		list.css('display', 'none');
		//Reach to maximum allowed number of values?
		var x = textboxlist.getOptions().max;
		if(x && x <= textboxlist.getValues().length) return;
		if ((options.queryRemote && txtsearch.length<3) || txtsearch.length < options.minLength) return;
		if (options.queryRemote){
			if (searchValues[txtsearch]){
				values = searchValues[txtsearch];
			} else {
				var data = options.remote.extraParams;
				data[options.remote.param] = txtsearch;
				if (currentRequest) currentRequest.abort();
				currentRequest = $.ajax({
					url: options.remote.url,
					data: data,
					dataType: 'json',
					success: function(r){
						searchValues[txtsearch] = r;
						values = r;
						showResults(txtsearch);
					}
				});
			}
		}
		showResults(txtsearch);
	}
	
	var showResults = function(search){		
		
		var results = method.filter(values, search, options.insensitive, options.maxResults);
		if (textboxlist.getOptions().unique){
			results = $.grep(results, function(v){ return textboxlist.isDuplicate(v) == -1; });		
		}
		hidePlaceholder();
		if (!results.length) return;
		blur();
		list.empty().css('display', 'block');
		$.each(results, function(i, r){ addResult(r, search); });
		if (options.onlyFromValues) focusFirst();
		results = results;updwidth();
	}
	
	var addResult = function(r, searched){
		var element = $('<li class="'+ prefix +'-result"/>').html(r[3] ? r[3] : r[1]).data('textboxlist:auto:value', r);		
		element.appendTo(list);
		if (options.highlight) $(options.highlightSelector ? element.find(options.highlightSelector) : element).each(function(){
			if ($(this).html()) method.highlight($(this), searched, options.insensitive, prefix + '-highlight');
		});
		if (options.mouseInteraction){
			element.css('cursor', 'pointer').hover(function(){ focus(element); }).mousedown(function(ev){
				ev.stopPropagation(); 
				ev.preventDefault();
				clearTimeout(hidetimer);
				doAdd = true;
			}).mouseup(function(){
				if (doAdd){
					addCurrent();
					currentInput.focus();
					search();
					doAdd = false;
				}
			});
			if (!options.onlyFromValues) element.mouseleave(function(){ if (current && (current.get(0) == element.get(0))) blur(); });	
		}
	}
	
	var hide = function(){
		hidetimer = setTimeout(function(){
			hidePlaceholder();
			list.css('display', 'none');
			currentSearch = null;			
		}, isIE ? 150 : 0);
	}
	
	var showPlaceholder = function(){
		if (placeholder) placeholder.css('display', 'block');		
	}
	
	var hidePlaceholder = function(){
		if (placeholder) placeholder.css('display', 'none');
	}
	
	var focus = function(element){
		if (!element || !element.length) return;
		blur();
		current = element.addClass(prefix + '-result-focus');
	}
	
	var blur = function(){
		if (current && current.length){
			current.removeClass(prefix + '-result-focus');
			current = null;            
		}
	}
	
	var focusFirst = function(){
		return focus(list.find(':first'));
	}
	
	var focusRelative = function(dir){
		if (!current || !current.length) return self;
		return focus(current[dir]());
	}
	
	var addCurrent = function(){
		if(!current) return;
		var value = current.data('textboxlist:auto:value');
		var b = textboxlist.create('box', value.slice(0, 3));
		if (b){
			b.autoValue = value;
			if ($.isArray(index)) index.push(value);
			currentInput.setValue([null, '', null]);
			b.inject(currentInput.toElement(), 'before');
      textboxlist.hidePlh();
			hide()
		}
		blur();
		return self;
	}
	
	var navigate = function(ev){
		var evStop = function(){ ev.stopPropagation(); ev.preventDefault(); }
		switch (ev.which){
			case 38:			
				evStop();
				(!options.onlyFromValues && current && current.get(0) === list.find(':first').get(0)) ? blur() : focusRelative('prev');
				break;
			case 40:			
				evStop();
				(current && current.length) ? focusRelative('next') : focusFirst();
				break;
			case 13:
				evStop();
				if (current && current.length) addCurrent();
				else if (!options.onlyFromValues){
					var value = currentInput.getValue();				
					var b = textboxlist.create('box', value);
					if (b){
						b.inject(currentInput.toElement(), 'before');
						currentInput.setValue([null, '', null]);
					}
				}
		}
	}
	
	this.setValues = function(v){
		values = v;
	}
	this.getValues = function(){return values}
	this.addValue = function(v){
		//check exist? delete first
		for(var ii = 0; ii<values.length; ii++){
			if((''+values[ii][0])==(''+v[0])){
				values.splice(ii,1);break;
			}
		}	
		values.push(v)
	}
	
	init()
}
var xrm = function(str){
	if(!str) return '';
	str=str.replace(/\\/gi,'');
    str=str.replace(/à|á|ạ|ả|ã|â|ầ|ấ|ậ|ẩ|ẫ|ă|ằ|ắ|ặ|ẳ|ẵ/gi,"a");
    str=str.replace(/è|é|ẹ|ẻ|ẽ|ê|ề|ế|ệ|ể|ễ/gi,"e");
    str=str.replace(/ì|í|ị|ỉ|ĩ/gi,"i");
    str=str.replace(/ò|ó|ọ|ỏ|õ|ô|ồ|ố|ộ|ổ|ỗ|ơ|ờ|ớ|ợ|ở|ỡ/gi,"o");
    str=str.replace(/ù|ú|ụ|ủ|ũ|ư|ừ|ứ|ự|ử|ữ/gi,"u");
    str=str.replace(/ỳ|ý|ỵ|ỷ|ỹ/gi,"y");
    str=str.replace(/đ/gi,"d");
    return str
}
$.TextboxList.Autocomplete.Methods = {	
	standard: {
		filter: function(values, search, insensitive, max){
			var newvals = [], regexp = new RegExp('\\b' + escapeRegExp(search), insensitive ? 'i' : '');            
			for (var i = 0; i < values.length; i++){
				if (newvals.length === max) break;                
				if (regexp.test(xrm(values[i][1]))) newvals.push(values[i])
			}
			return newvals
		},		
		highlight: function(element, search, insensitive, klass){
			var regex = new RegExp('(<[^>]*>)|(\\b'+ escapeRegExp(search) +')', insensitive ? 'ig' : 'g');
			return element.html(element.html().replace(regex, function(a, b, c){
				return (a.charAt(0) == '<') ? a : '<strong class="'+ klass +'">' + c + '</strong>'
			}))
		}
	}	
}
var chk = function(v){ return !!(v || v === 0) }
var escapeRegExp = function(str){return xrm(str)}
})(jQuery);