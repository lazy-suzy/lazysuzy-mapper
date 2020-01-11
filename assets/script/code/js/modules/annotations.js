/*
 Highcharts JS v5.0.14 (2017-07-28)

 (c) 2009-2017 Torstein Honsi

 License: www.highcharts.com/license
*/
(function(n){"object"===typeof module&&module.exports?module.exports=n:n(Highcharts)})(function(n){(function(e){function n(a,c,b,f,e){for(var d=a.length,g=0;g<d;)k(a[g])&&k(a[g+1])?(a[g]=c.toPixels(a[g])-f,a[g+1]=b.toPixels(a[g+1])-e,g+=2):g+=1;return a}var p=e.defined,k=e.isNumber,t=e.inArray,A=e.isArray,B=e.merge,C=e.Chart,u=e.extend,D=e.each,q,E;E=["path","rect","circle"];q={top:0,left:0,center:.5,middle:.5,bottom:1,right:1};var F=function(){this.init.apply(this,arguments)};F.prototype={init:function(a,
c){var b=c.shape&&c.shape.type;this.chart=a;var f;f={xAxis:0,yAxis:0,title:{style:{},text:"",x:0,y:0},shape:{params:{stroke:"#000000",fill:"transparent",strokeWidth:2}}};a={circle:{params:{x:0,y:0}}};a[b]&&(f.shape=B(f.shape,a[b]));this.options=B({},f,c)},render:function(a){var c=this.chart,b=this.chart.renderer,f=this.group,e=this.title,d=this.shape,g=this.options,k=g.title,m=g.shape;f||(f=this.group=b.g());!d&&m&&-1!==t(m.type,E)&&(d=this.shape=b[g.shape.type](m.params),d.add(f));!e&&k&&(e=this.title=
b.label(k),e.add(f));f.add(c.annotations.group);this.linkObjects();!1!==a&&this.redraw()},redraw:function(){var a=this.options,c=this.chart,b=this.group,f=this.title,z=this.shape,d=this.linkedObject,g=c.xAxis[a.xAxis],v=c.yAxis[a.yAxis],c=a.width,m=a.height,w=q[a.anchorY],x=q[a.anchorX],h,y,l,r;d&&(y=d instanceof e.Point?"point":d instanceof e.Series?"series":null,"point"===y?(a.xValue=d.x,a.yValue=d.y,l=d.series):"series"===y&&(l=d),b.visibility!==l.group.visibility&&b.attr({visibility:l.group.visibility}));
d=p(a.xValue)?g.toPixels(a.xValue+g.minPointOffset)-g.minPixelPadding:a.x;l=p(a.yValue)?v.toPixels(a.yValue):a.y;k(d)&&k(l)&&(f&&(f.attr(a.title),f.css(a.title.style)),z&&(h=u({},a.shape.params),"values"===a.units&&(e.objectEach(h,function(a,b){-1<t(b,["width","x"])?h[b]=g.translate(h[b]):-1<t(b,["height","y"])&&(h[b]=v.translate(h[b]))}),h.width&&(h.width-=g.toPixels(0)-g.left),h.x&&(h.x+=g.minPixelPadding),"path"===a.shape.type&&n(h.d,g,v,d,l)),"circle"===a.shape.type&&(h.x+=h.r,h.y+=h.r),z.attr(h)),
b.bBox=null,k(c)||(r=b.getBBox(),c=r.width),k(m)||(r||(r=b.getBBox()),m=r.height),k(x)||(x=q.center),k(w)||(w=q.center),d-=c*x,l-=m*w,p(b.translateX)&&p(b.translateY)?b.animate({translateX:d,translateY:l}):b.translate(d,l))},destroy:function(){var a=this,c=this.chart.annotations.allItems,b=c.indexOf(a);-1<b&&c.splice(b,1);D(["title","shape","group"],function(b){a[b]&&(a[b].destroy(),a[b]=null)});a.group=a.title=a.shape=a.chart=a.options=null},update:function(a,c){u(this.options,a);this.linkObjects();
this.render(c)},linkObjects:function(){var a=this.chart,c=this.linkedObject,b=c&&(c.id||c.options.id),f=this.options.linkedTo;p(f)?p(c)&&f===b||(this.linkedObject=a.get(f)):this.linkedObject=null}};u(C.prototype,{annotations:{add:function(a,c){var b=this.allItems,f=this.chart,e,d;A(a)||(a=[a]);for(d=a.length;d--;)e=new F(f,a[d]),b.push(e),e.render(c)},redraw:function(){D(this.allItems,function(a){a.redraw()})}}});C.prototype.callbacks.push(function(a){var c=a.options.annotations,b;b=a.renderer.g("annotations");
b.attr({zIndex:7});b.add();a.annotations.allItems=[];a.annotations.chart=a;a.annotations.group=b;A(c)&&0<c.length&&a.annotations.add(a.options.annotations);e.addEvent(a,"redraw",function(){a.annotations.redraw()})})})(n)});
