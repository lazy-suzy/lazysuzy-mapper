
	function ce( t ) { return document.createElement( t ); }

	function ge( t ) { return document.getElementById( t ); }

	function insertAfter(node, referenceNode) {

	  referenceNode.parentNode.insertBefore(node, referenceNode.nextSibling);

	}

	function isdefined( variable) {

		return (typeof(variable) == "undefined")?  false: true;

	}

	function isnumber( variable) {

		return (typeof(variable) == "number")?  false: true;

	}

	function detect() {

		var agent 	= navigator.userAgent.toLowerCase();

		// detect platform

		this.isMac		= (agent.indexOf('mac') != -1);

		this.isWin		= (agent.indexOf('win') != -1);

		this.isWin2k	= (this.isWin && (

				agent.indexOf('nt 5') != -1));

		this.isWinSP2	= (this.isWin && (

				agent.indexOf('xp') != -1 || 

				agent.indexOf('sv1') != -1));

		this.isOther	= (

				agent.indexOf('unix') != -1 || 

				agent.indexOf('sunos') != -1 || 

				agent.indexOf('bsd') != -1 ||

				agent.indexOf('x11') != -1 || 

				agent.indexOf('linux') != -1);

		

		// detect browser

		this.isSafari	= (agent.indexOf('safari') != -1);

		this.isSafari2 = (this.isSafari && (parseFloat(agent.substring(agent.indexOf("applewebkit/")+"applewebkit/".length,agent.length).substring(0,agent.substring(agent.indexOf("applewebkit/")+"applewebkit/".length,agent.length).indexOf(' '))) >=  300));

		this.isOpera	= (agent.indexOf('opera') != -1);

		this.isNN		= (agent.indexOf('netscape') != -1);

		this.isIE		= (agent.indexOf('msie') != -1);

		this.isFirefox	= (agent.indexOf('firefox') != -1);

		

		// itunes compabibility

		this.isiTunesOK	= this.isMac || this.isWin2k;

		

		this.getClientWidth = function() {

			return(window.innerWidth||

				  (document.documentElement && document.documentElement.clientWidth)||

				  (document.body && document.body.clientWidth)||

				  0);

		}

	}

	browser = new detect();	

	browser.getClientHeight = function() {

		return(window.innerHeight||

			  (document.documentElement && document.documentElement.clientHeight)||

			  (document.body && document.body.clientHeight)||

			  0);

	}

	browser.getPageScrollTop = function() {

		return(document.documentElement && document.documentElement.scrollTop)||

			  (document.body && document.body.scrollTop)||

			  0;

	}

	browser.getPageScrollLeft = function() {

		return(document.documentElement && document.documentElement.scrollLeft)||

			  (document.body && document.body.scrollLeft)||

			  0;

	}

	browser.getPageSize = function() {

		var xScroll,yScroll;

		if(window.innerHeight && window.scrollMaxY)	{

			xScroll = document.body.scrollWidth;

			yScroll = window.innerHeight + window.scrollMaxY;

		}	else if(document.body.scrollHeight > document.body.offsetHeight)	{

			xScroll = document.body.scrollWidth;

			yScroll = document.body.scrollHeight;

		}	else	{

			xScroll = document.body.offsetWidth;

			yScroll = document.body.offsetHeight;

		}

		

		var windowWidth,windowHeight;

		if(self.innerHeight)	{

			windowWidth = self.innerWidth;

			windowHeight = self.innerHeight;

		} else if(document.documentElement && document.documentElement.clientHeight) {

			windowWidth  = document.documentElement.clientWidth;

			windowHeight = document.documentElement.clientHeight;

		} else if(document.body) {

			windowWidth  = document.body.clientWidth;

			windowHeight = document.body.clientHeight;

		}

		var pageHeight,pageWidth;

		

		if(yScroll < windowHeight) pageHeight = windowHeight;

		else pageHeight = yScroll;

		

		if(xScroll < windowWidth) pageWidth = windowWidth;

		else pageWidth = xScroll;

		

		scrollleft = browser.getPageScrollLeft();

		scrolltop = browser.getPageScrollTop();

		return { pageWidth:pageWidth, pageHeight:pageHeight, 

				 windowWidth:windowWidth, windowHeight:windowHeight, 

				 scrollLeft:scrollleft, scrollTop:scrolltop};

	}

	browser.getPosition = function( element ) 	{

		var l = t = 0;

		while( element != null ) {

			l += element.offsetLeft;

			t += element.offsetTop;

			element = element.offsetParent

		}

		return {left:l, top:t};

	};
	

	//	Drag

	function drag(o)	{		

		var src = o.src || '';

		var identy = o.identy || '';

		if( !src ) return;

		if( identy == '' ) return;

		var draggable = o.draggable || src;

		

		var draginit = function(e) {

			var htype = '-moz-grabbing';

			if (e == null) { e = window.event; htype = 'move';} 

			var target = e.target != null ? e.target : e.srcElement;

			 target = (target.nodeType == 1 || target.nodeType == 9) ? target : target.parentNode;

			//cursor = target.style.cursor;

			if ( target.className == identy || target.id == identy ) {

				//target.style.cursor = htype;

				target = draggable;

				dragging = true;

				dragXoffset = e.clientX - parseInt(draggable.style.left);

				dragYoffset = e.clientY - parseInt(draggable.style.top);

				src.onmousemove = function(e) {

					if (e == null) { e = window.event } 

					  if (e.button <=1 && dragging ) {

						 draggable.style.left = e.clientX - dragXoffset+'px';

						 draggable.style.top = e.clientY - dragYoffset+'px';

						 return false;

					  }

				}

				src.onmouseup = function() {

					src.onmousemove = null;

					src.onmouseup = null;

					//src.style.cursor = 'move';

					dragging = false;

				}

				return false;

			}

		}

		

		if(browser.isIE) src.attachEvent("onmousedown", draginit);

		else src.addEventListener("mousedown", draginit, false);

	}	

	/*

	 *	Captionier popup

	 */

	

	var popupwin  = null;

	var s = d = t = 0;

	var target,evt_type;

	function popup( o ) {

		o = o || {};	

		if ( o.event == null )  var e = window.event; else var e = o.event;

		//var target = e.target != null ? e.target : e.srcElement;

		if ( o.sleep != true || !isdefined(o.sleep) ){

			target   = e.target != null ? e.target : e.srcElement;

			evt_type = e.type;

		}

		if( evt_type != "click" && (!isdefined( o.shownow ) || o.shownow == false) ) {

			if ( !s )	s = new Date();				

			var n  = new Date();

			d = n.getTime() - s.getTime();			

			if ( d <= 1000 ){				

				if( o.sleep != true || !isdefined(o.sleep) ) $(target).bind('mouseout',function() { if( t ) clearTimeout( t ); s = d = t = 0; } );

				o.sleep = true;

				t = setTimeout( function(){ popup(o) }, 1 );

			}

			else {

				o.shownow = true;

				popup( o );

				s = d = t = 0;

			}				

		}else{		

			var pos = browser.getPosition( target );		

			var left = pos.left;

			var top = pos.top;

			var width = o.width || 400;

			var url = o.url || '';

			var data = o.data || '';

			var onclose = ','+o.onclose || 'void(0)';

			var content = o.content || '<span class="loading" >Loading...</span>';

			if( target.offsetWidth ) left += Math.floor(target.offsetWidth / 2);

			if( target.offsetHeight ) top += Math.floor(target.offsetHeight / 2);

			if( isdefined( o.offsettop ) != undefined && parseInt( o.offsettop ) > 0 ) top += o.offsettop;

			if( isdefined( o.offsetleft ) != undefined && parseInt( o.offsetleft ) > 0 ) left += o.offsetleft;

			if( !isdefined( o.loginwindow ) ) o.loginwindow = false;

			

			 pop = ce('div');

			pop.forcedisplay = false;

			nopopup();

			//pop.onresize = function() { popupresize() };

			pop.className = 'popup';

			pop.style.width = width;			

			if ( isdefined(o.height) && o.height > 0 ) customstyle = 'style="height:'+o.height+'px"';

			else	customstyle = '';

			//pop.style.left = left;

			//pop.style.top = top;

			pop.postop = top;

			pop.posofftop = 25;

			pop.posoffleft = 43;

			pop.posleft = left;

			pop.poshoriz = top - document.body.scrollTop > document.body.scrollTop + document.body.clientHeight - top ? 'd' : 'u';

			pop.posvert = left > width+50 ? 'r' : 'l';

			

			if( o.loginwindow ) {

				pop.posofftop = 13;

				pop.posoffleft = 64;

				

				arrow_style = "margin-top:0px";

				if( pop.poshoriz == 'u') arrow_style = "margin-top:0px";

			

				pop.innerHTML = '<div id="popuparrow" class=arrow >' + png( g_template_img + 'login_c_arrow_' + pop.poshoriz + pop.posvert + '.png', 24, 13, arrow_style ) 

							  + '</div><div class="back-login" id="popupbackground" >'

							  +'<table width="100%" border="0" cellspacing="0" cellpadding="0">'

							  +'<tr><td><table width="100%" border="0" cellspacing="0" cellpadding="0"><tr>'

							  +'<td align="left" valign="top"><img src="'+g_template_img+'img_popuplefttop.gif" width="6" height="6" /></td>'

							  +'<td width="99%" align="left" valign="top" background="'+g_template_img+'img_popuptop.gif"><img src="'+g_template_img+'spacer.gif" ></td>'

							  +'<td align="left" valign="top" ><img src="'+g_template_img+'img_popuprighttop.gif" width="6" height="6" /></td>'

							  +'</tr></table></td></tr>'

							  +'<tr><td height="196" align="left" valign="top" class="bg_login">'

								

							  +'<table width="100%" border="0" cellspacing="0" cellpadding="0">'

							  +'<tr><td><img id="popupclose" src="'+g_template_img+'img_loginclose.gif" class="deletebutton-login" onclick="nopopup()'+ onclose +'" title="click to close" /></td></tr>'

							  +'<tr><td valign="top" id=popupcanvas class="popupcanvas-login" >'+content+'</td></tr></table>'

								

							  +'</td></tr>'

							  +'<tr><td><table width="100%" border="0" cellspacing="0" cellpadding="0"><tr>'

							  +'<td align="left" valign="top"><img src="'+g_template_img+'img_popupleftbottom.gif" width="6" height="6" /></td>'

							  +'<td width="99%" align="left" valign="bottom" background="'+g_template_img+'img_popupbottom.gif"><img src="'+g_template_img+'spacer.gif" ></td>'

							  +'<td align="left" valign="top"><img src="'+g_template_img+'img_popuprightbottom.gif" width="6" height="6" /></td>'

							  +'</tr></table></td></tr></table></div>';

			} else { 

				arrow_style = "margin-top:-4px";

				if( pop.poshoriz == 'u') arrow_style = "margin-top:7px";

				pop.innerHTML = '<div id="popuparrow" class=arrow >' + png( g_template_img + 'c_arrow_' + pop.poshoriz + pop.posvert + '.png', 43, 25, arrow_style ) 

							  + '</div><div class="back" id="popupbackground" '+customstyle+'>'

							  + '<div id="popupclose" class="deletebutton" onclick="nopopup()'+ onclose +'" title="click to close">X</div>'

							  + '<div id=popupcanvas >'+content+'</div></div>';

			}

			

			popupwin = pop;

			document.body.appendChild( pop );

			ge('popupcanvas').onchange = function() { popupresize(); }			

			if( url ) {

				$.ajax( {type : "POST",url : url, data : data, success: function( html ){

								$('#popupcanvas').html( html ); popupresize();

								if( isdefined( o.callback ) ) eval( o.callback );

								if( isdefined( o.autoclose ) && o.autoclose == true ) {

									var dely = ( isdefined( o.closedelay ) ) ? o.closedelay : 2000;

									setTimeout('nopopup()', dely);

								}

							}

						});			

			}

			popupresize();

			return pop;

		}

	}

	function popupresize() {

		var pop = popupwin;

		var vtop;

		if( pop == null ) return;



		ge('popupbackground').style.height = pop.clientHeight + 'px'; 

		ge('popupbackground').style.width = pop.clientWidth + 'px';

		

		if( pop.poshoriz == 'd' )	{

			pop.style.top = vtop = pop.postop - (pop.clientHeight + pop.posofftop) + 'px';

			ge('popuparrow').style.top = browser.isIE ? pop.clientHeight -1 + 'px': pop.clientHeight + 1 + 'px';

		}

		else if( pop.poshoriz == 'u' ) {

			pop.style.top = vtop = pop.postop + (pop.posofftop) + 'px';

			ge('popuparrow').style.top = browser.isIE ? -pop.posofftop + 1 + 'px' : -pop.posofftop + 1 + 'px';

		}

		if( vtop < 0 && !pop.forcedisplay )	{

			pop.forcedisplay = true;

			pop.poshoriz = 'u';

			popupresize();

			ge('popuparrow').innerHTML = png( g_template_img + 'c_arrow_' + pop.poshoriz + pop.posvert + '.png', 43, 25 );

			return;

		}

		pop.style.left = pop.posvert == 'l' ? pop.posleft + 'px' : pop.posleft - pop.clientWidth + 'px';

		

		ge('popuparrow').style.left = pop.posvert == 'l' ? 0 + 'px' : pop.clientWidth - pop.posoffleft + 'px';

		//ge('popupclose').style.left = pop.clientWidth - 28 + 'px';

	

		// scroll window to fit popup

		if( vtop < document.body.scrollTop )	{

			window.scrollBy( 0, vtop-(document.body.scrollTop+4) );

		}

		if( (vtop + pop.clientHeight) > (document.body.scrollTop + document.body.clientHeight) )	{

			window.scrollBy( 0, (vtop + pop.clientHeight)-(document.body.scrollTop + document.body.clientHeight - 4) );

		}

	}

	function reloadpopup(o){

		var url = o.url || '';

		var data = o.data || '';		

		var content = o.content || '<span class="loading" >Loading...</span>';

//		alert(o.height);

		$(popupwin).css('width',o.width);	

		if(o.height)	ge('popupbackground').style.height = o.height


	//	$('#$popupbackground').css('height',o.height);

		$('#popupcanvas').html( content );popupresize();

		if( url ) {			

			$.ajax( {type : "POST",url : url, data : data, success: function( html ){

							$('#popupcanvas').html( html ); popupresize();

							if( isdefined( o.callback ) ) eval( o.callback );

							if( isdefined( o.autoclose ) && o.autoclose == true ) {

								var dely = ( isdefined( o.closedelay ) ) ? o.closedelay : 2000;

								setTimeout('nopopup()', dely);

							}

						}

					});			

		}

	}

	function nopopup() {

		if( popupwin != null ) document.body.removeChild( popupwin );

		popupwin = null;

	}

	//	Popup creater.

	function dialog(o) {

		

			o = o || {};

			var page = browser.getPageSize();

			var title = o.title || ''; 

			var width = o.width || 400;

			var height = o.height || 300;

			var ismodel = o.ismodel || false;

			var isdrag = o.isdrag || false;

			var onclose = o.onclose || 'dialog.close()';

			var content = o.content || '<span class="warnning" >Loading...</span>';

			var left = o.left || ((page.windowWidth/2) - (width/2));

			var top = o.top || ((page.windowHeight/2) - (height/2));

			var popupBgClass = !isdefined( o.bgclass ) ? 'modal' : o.bgclass;

			this.loaded = false;

			

			left = left + page.scrollLeft;

			top  = top + page.scrollTop;

			

			bgid = 'dialog_bg_'+Math.random();	

			pop  = 'dialog_pop_'+Math.random();

			popcontainer  = 'dialog_container_'+Math.random();	

			if( ismodel == true ) {	//	Gray background
				
				this.bg = ce('div');		

				this.bg.id = bgid;							
				if($.browser.msie)
					this.bg.style.width = page.pageWidth+'px'; 
				else
					this.bg.style.width = page.pageWidth-17+'px'; 

				this.bg.style.height = page.pageHeight+'px'; 
				
				this.bg.className = (browser.IsSafari) ? "popupBackgroundSafari" : "popupBackground";

				hideAllElement('select', this.bg, 1)

				document.body.appendChild(this.bg); 

			}

			//	window

			/*this.table = ce("TABLE");

			this.tbody = ce("TBODY");

			this.tr = ce("TR");

			this.td = ce("TD");*/

			

			this.win = ce('div');

			this.win.id = pop;

			this.win.className = 'dialog';

			this.win.style.width = width+'px';		

			//this.win.style.height = height+'px';

			this.win.style.left = left +'px';		

			this.win.style.top = top +'px';

			this.win.style.position = "absolute";		

			//	title bar

			this.tit = ce('div');

			/*this.tit.style.width = width+'px';

			this.tit.id = 'titlebar';			

			var header = '<div class="close"><a href="javascript:'+onclose+';" >Close</a></div>';	

			this.tit.innerHTML = header;*/

			this.win.appendChild(this.tit);

			

			//Inner Window	

			this.innerwin = ce('div');

			this.win.appendChild(this.innerwin);	

			//this.innerwin.className = 'content-area';

			this.innerwin.className = popupBgClass;

			

			//	title

			/*this.titlediv = ce('div');

			this.titlediv.className = 'title';

			this.innerwin.appendChild(this.titlediv);	

			this.titlediv.innerHTML = title;

			this.br = ce('br');

			this.br.className = 'clear';

			this.innerwin.appendChild(this.br);	*/

			//	Content area		

			this.con = ce('div');

			this.con.id = popcontainer;

			this.con.style.width = width+'px';

			this.con.style.height = height+'px';

			this.innerwin.appendChild(this.con);	

			//this.con.className = 'content-area';

				

			this.con.innerHTML = content;

			

			this.show = function() {

			//	hideAllElement('select', this.win)

				document.body.appendChild(this.win);

				//$('#popup_close_button').attr('src', g_template_img+'popup_close.gif');

				if( isdrag ) {

					this.tit.style.cursor = 'move';

					this.drag = new drag( {src:this.tit, draggable:this.win, identy:this.tit.id} )

				}

			}

			this.sethtml = function(html) {

				$(this.con).css('height','auto');

				$(this.con).html(html);

				this.loaded = true;

				this.resizeDialog();

				//this.resetHeight( parseInt($(this.con).offsetHeight) );

			}

			this.setHeight = function(height){

				if(!browser.isIE){

					height = this.con.offsetHeight + parseInt(height);					

					$(this.con).css('height', height + 'px'); 

				}

			}

			this.resizeDialog = function(){				

				$(this.con).css('height','auto');

				//alert($(this.con).attr('offsetHeight'));

				this.resetHeight( parseInt($(this.con).attr('offsetHeight')) );

			}

			this.resetHeight = function(height){

				height = parseInt(height);					

				$(this.con).css('height', height + 'px'); 

			}

			this.resize = function() {

				if(!browser.isIE){	

					if( this.con.clientHeight < this.win.clientHeight )

						$(this.con).css('height', this.win.clientHeight);

				}

			}

			this.gethtml = function() {

				return this.con.innerHTML;

			}		

			this.close = function() {

				if( this.loaded ) {

					showAllElement('select');

					if( this.bg ) this.bg.parentNode.removeChild(this.bg);

					if( this.win ) this.win.parentNode.removeChild(this.win);

				}

			}

		

	}	

	/*

     * hides give objects (for IE only)

     */

	function hideAllElement( elmID, overDiv , greypopup) {

		if( document.all )	{

			for( i = 0; i < document.all.tags( elmID ).length; i++ )	{

				thispopup = 0;

				obj = document.all.tags( elmID )[i];

				

				if( !obj || !obj.offsetParent ) continue;

				// Find the element's offsetTop and offsetLeft relative to the BODY tag.

				objLeft   = obj.offsetLeft;

				objTop    = obj.offsetTop;

				objParent = obj.offsetParent;

				while( objParent.tagName.toUpperCase() != "BODY" )	{

					objLeft  += objParent.offsetLeft;

					objTop   += objParent.offsetTop;

					objParent = objParent.offsetParent;

					//alert(objParent.className);

			

					if(objParent.id == overDiv.id){

						thispopup = 1;	

						break;

					}

				}

				

				if(thispopup == 1)	continue;

				if(greypopup){obj.style.visibility = "hidden";}

				else{

					objHeight = obj.offsetHeight;

					objWidth = obj.offsetWidth;

					if(( overDiv.offsetLeft + overDiv.offsetWidth ) <= objLeft );

					else if(( overDiv.offsetTop + overDiv.offsetHeight ) <= objTop );

					else if( overDiv.offsetTop >= ( objTop + objHeight ));

					else if( overDiv.offsetLeft >= ( objLeft + objWidth ));

					else	obj.style.visibility = "hidden";

				}

			}

		}

	}

     

    /*

     * unhides give objects (for IE only)

     */

	function showAllElement( elmID )	{

		if( document.all )	{

			for( i = 0; i < document.all.tags( elmID ).length; i++ ) {

				obj = document.all.tags( elmID )[i];				

				if( !obj || !obj.offsetParent )

					continue;

				obj.style.visibility = "";

			}

		}

	}

	

	function showHint( obj, text ) {

		if( !obj ) return;

		if( obj.value == '' ) obj.value = text;

	}

	function removeHint( obj, text ) {

		if( !obj ) return;

		if( obj.value == text ) obj.value = '';

	}

	function showTinyHint( obj,  text ) {

		if( !obj ) return;

		//if( tinyMCE.getContent() == '' ) tinyMCE.setContent(text) ;

	}

	function removeTinyHint( obj, text ) {

		if( !obj ) return;

		if( tinyMCE.getContent() == text )  tinyMCE.setContent('');

	}


