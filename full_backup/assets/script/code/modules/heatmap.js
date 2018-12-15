/*
 Highcharts JS v5.0.14 (2017-07-28)

 (c) 2009-2017 Torstein Honsi

 License: www.highcharts.com/license
*/
(function(p){"object"===typeof module&&module.exports?module.exports=p:p(Highcharts)})(function(p){(function(b){var h=b.Axis,r=b.Chart,k=b.color,n,d=b.each,w=b.extend,x=b.isNumber,l=b.Legend,g=b.LegendSymbolMixin,q=b.noop,u=b.merge,v=b.pick,t=b.wrap;n=b.ColorAxis=function(){this.init.apply(this,arguments)};w(n.prototype,h.prototype);w(n.prototype,{defaultColorAxisOptions:{lineWidth:0,minPadding:0,maxPadding:0,gridLineWidth:1,tickPixelInterval:72,startOnTick:!0,endOnTick:!0,offset:0,marker:{animation:{duration:50},
width:.01,color:"#999999"},labels:{overflow:"justify",rotation:0},minColor:"#e6ebf5",maxColor:"#003399",tickLength:5,showInLegend:!0},keepProps:["legendGroup","legendItemHeight","legendItemWidth","legendItem","legendSymbol"].concat(h.prototype.keepProps),init:function(a,c){var e="vertical"!==a.options.legend.layout,f;this.coll="colorAxis";f=u(this.defaultColorAxisOptions,{side:e?2:1,reversed:!e},c,{opposite:!e,showEmpty:!1,title:null});h.prototype.init.call(this,a,f);c.dataClasses&&this.initDataClasses(c);
this.initStops();this.horiz=e;this.zoomEnabled=!1;this.defaultLegendLength=200},initDataClasses:function(a){var c=this.chart,e,f=0,m=c.options.chart.colorCount,b=this.options,y=a.dataClasses.length;this.dataClasses=e=[];this.legendItems=[];d(a.dataClasses,function(a,d){a=u(a);e.push(a);a.color||("category"===b.dataClassColor?(d=c.options.colors,m=d.length,a.color=d[f],a.colorIndex=f,f++,f===m&&(f=0)):a.color=k(b.minColor).tweenTo(k(b.maxColor),2>y?.5:d/(y-1)))})},setTickPositions:function(){if(!this.dataClasses)return h.prototype.setTickPositions.call(this)},
initStops:function(){this.stops=this.options.stops||[[0,this.options.minColor],[1,this.options.maxColor]];d(this.stops,function(a){a.color=k(a[1])})},setOptions:function(a){h.prototype.setOptions.call(this,a);this.options.crosshair=this.options.marker},setAxisSize:function(){var a=this.legendSymbol,c=this.chart,e=c.options.legend||{},f,m;a?(this.left=e=a.attr("x"),this.top=f=a.attr("y"),this.width=m=a.attr("width"),this.height=a=a.attr("height"),this.right=c.chartWidth-e-m,this.bottom=c.chartHeight-
f-a,this.len=this.horiz?m:a,this.pos=this.horiz?e:f):this.len=(this.horiz?e.symbolWidth:e.symbolHeight)||this.defaultLegendLength},normalizedValue:function(a){this.isLog&&(a=this.val2lin(a));return 1-(this.max-a)/(this.max-this.min||1)},toColor:function(a,c){var e=this.stops,f,m,b=this.dataClasses,d,g;if(b)for(g=b.length;g--;){if(d=b[g],f=d.from,e=d.to,(void 0===f||a>=f)&&(void 0===e||a<=e)){m=d.color;c&&(c.dataClass=g,c.colorIndex=d.colorIndex);break}}else{a=this.normalizedValue(a);for(g=e.length;g--&&
!(a>e[g][0]););f=e[g]||e[g+1];e=e[g+1]||f;a=1-(e[0]-a)/(e[0]-f[0]||1);m=f.color.tweenTo(e.color,a)}return m},getOffset:function(){var a=this.legendGroup,c=this.chart.axisOffset[this.side];a&&(this.axisParent=a,h.prototype.getOffset.call(this),this.added||(this.added=!0,this.labelLeft=0,this.labelRight=this.width),this.chart.axisOffset[this.side]=c)},setLegendColor:function(){var a,c=this.reversed;a=c?1:0;c=c?0:1;a=this.horiz?[a,0,c,0]:[0,c,0,a];this.legendColor={linearGradient:{x1:a[0],y1:a[1],x2:a[2],
y2:a[3]},stops:this.stops}},drawLegendSymbol:function(a,c){var e=a.padding,f=a.options,b=this.horiz,d=v(f.symbolWidth,b?this.defaultLegendLength:12),g=v(f.symbolHeight,b?12:this.defaultLegendLength),q=v(f.labelPadding,b?16:30),f=v(f.itemDistance,10);this.setLegendColor();c.legendSymbol=this.chart.renderer.rect(0,a.baseline-11,d,g).attr({zIndex:1}).add(c.legendGroup);this.legendItemWidth=d+e+(b?f:q);this.legendItemHeight=g+e+(b?q:0)},setState:q,visible:!0,setVisible:q,getSeriesExtremes:function(){var a=
this.series,c=a.length;this.dataMin=Infinity;for(this.dataMax=-Infinity;c--;)void 0!==a[c].valueMin&&(this.dataMin=Math.min(this.dataMin,a[c].valueMin),this.dataMax=Math.max(this.dataMax,a[c].valueMax))},drawCrosshair:function(a,c){var e=c&&c.plotX,b=c&&c.plotY,d,g=this.pos,q=this.len;c&&(d=this.toPixels(c[c.series.colorKey]),d<g?d=g-2:d>g+q&&(d=g+q+2),c.plotX=d,c.plotY=this.len-d,h.prototype.drawCrosshair.call(this,a,c),c.plotX=e,c.plotY=b,this.cross&&(this.cross.addClass("highcharts-coloraxis-marker").add(this.legendGroup),
this.cross.attr({fill:this.crosshair.color})))},getPlotLinePath:function(a,c,e,b,d){return x(d)?this.horiz?["M",d-4,this.top-6,"L",d+4,this.top-6,d,this.top,"Z"]:["M",this.left,d,"L",this.left-6,d+6,this.left-6,d-6,"Z"]:h.prototype.getPlotLinePath.call(this,a,c,e,b)},update:function(a,c){var e=this.chart,b=e.legend;d(this.series,function(a){a.isDirtyData=!0});a.dataClasses&&b.allItems&&(d(b.allItems,function(a){a.isDataClass&&a.legendGroup&&a.legendGroup.destroy()}),e.isDirtyLegend=!0);e.options[this.coll]=
u(this.userOptions,a);h.prototype.update.call(this,a,c);this.legendItem&&(this.setLegendColor(),b.colorizeItem(this,!0))},remove:function(){this.legendItem&&this.chart.legend.destroyItem(this);h.prototype.remove.call(this)},getDataClassLegendSymbols:function(){var a=this,c=this.chart,e=this.legendItems,f=c.options.legend,t=f.valueDecimals,h=f.valueSuffix||"",l;e.length||d(this.dataClasses,function(f,u){var m=!0,k=f.from,n=f.to;l="";void 0===k?l="\x3c ":void 0===n&&(l="\x3e ");void 0!==k&&(l+=b.numberFormat(k,
t)+h);void 0!==k&&void 0!==n&&(l+=" - ");void 0!==n&&(l+=b.numberFormat(n,t)+h);e.push(w({chart:c,name:l,options:{},drawLegendSymbol:g.drawRectangle,visible:!0,setState:q,isDataClass:!0,setVisible:function(){m=this.visible=!m;d(a.series,function(a){d(a.points,function(a){a.dataClass===u&&a.setVisible(m)})});c.legend.colorizeItem(this,m)}},f))});return e},name:""});d(["fill","stroke"],function(a){b.Fx.prototype[a+"Setter"]=function(){this.elem.attr(a,k(this.start).tweenTo(k(this.end),this.pos),null,
!0)}});t(r.prototype,"getAxes",function(a){var c=this.options.colorAxis;a.call(this);this.colorAxis=[];c&&new n(this,c)});t(l.prototype,"getAllItems",function(a){var c=[],b=this.chart.colorAxis[0];b&&b.options&&(b.options.showInLegend&&(b.options.dataClasses?c=c.concat(b.getDataClassLegendSymbols()):c.push(b)),d(b.series,function(a){a.options.showInLegend=!1}));return c.concat(a.call(this))});t(l.prototype,"colorizeItem",function(a,c,b){a.call(this,c,b);b&&c.legendColor&&c.legendSymbol.attr({fill:c.legendColor})});
t(l.prototype,"update",function(a){a.apply(this,[].slice.call(arguments,1));this.chart.colorAxis[0]&&this.chart.colorAxis[0].update({},arguments[2])})})(p);(function(b){var h=b.defined,r=b.each,k=b.noop,n=b.seriesTypes;b.colorPointMixin={isValid:function(){return null!==this.value},setVisible:function(b){var d=this,h=b?"show":"hide";r(["graphic","dataLabel"],function(b){if(d[b])d[b][h]()})},setState:function(d){b.Point.prototype.setState.call(this,d);this.graphic&&this.graphic.attr({zIndex:"hover"===
d?1:0})}};b.colorSeriesMixin={pointArrayMap:["value"],axisTypes:["xAxis","yAxis","colorAxis"],optionalAxis:"colorAxis",trackerGroups:["group","markerGroup","dataLabelsGroup"],getSymbol:k,parallelArrays:["x","y","value"],colorKey:"value",pointAttribs:n.column.prototype.pointAttribs,translateColors:function(){var b=this,h=this.options.nullColor,k=this.colorAxis,l=this.colorKey;r(this.data,function(d){var g=d[l];if(g=d.options.color||(d.isNull?h:k&&void 0!==g?k.toColor(g,d):d.color||b.color))d.color=
g})},colorAttribs:function(b){var d={};h(b.color)&&(d[this.colorProp||"fill"]=b.color);return d}}})(p);(function(b){var h=b.colorPointMixin,r=b.each,k=b.merge,n=b.noop,d=b.pick,p=b.Series,x=b.seriesType,l=b.seriesTypes;x("heatmap","scatter",{animation:!1,borderWidth:0,nullColor:"#f7f7f7",dataLabels:{formatter:function(){return this.point.value},inside:!0,verticalAlign:"middle",crop:!1,overflow:!1,padding:0},marker:null,pointRange:null,tooltip:{pointFormat:"{point.x}, {point.y}: {point.value}\x3cbr/\x3e"},
states:{normal:{animation:!0},hover:{halo:!1,brightness:.2}}},k(b.colorSeriesMixin,{pointArrayMap:["y","value"],hasPointSpecificOptions:!0,getExtremesFromAll:!0,directTouch:!0,init:function(){var b;l.scatter.prototype.init.apply(this,arguments);b=this.options;b.pointRange=d(b.pointRange,b.colsize||1);this.yAxis.axisPointRange=b.rowsize||1},translate:function(){var b=this.options,d=this.xAxis,h=this.yAxis,k=function(b,a,c){return Math.min(Math.max(a,b),c)};this.generatePoints();r(this.points,function(g){var a=
(b.colsize||1)/2,c=(b.rowsize||1)/2,e=k(Math.round(d.len-d.translate(g.x-a,0,1,0,1)),-d.len,2*d.len),a=k(Math.round(d.len-d.translate(g.x+a,0,1,0,1)),-d.len,2*d.len),f=k(Math.round(h.translate(g.y-c,0,1,0,1)),-h.len,2*h.len),c=k(Math.round(h.translate(g.y+c,0,1,0,1)),-h.len,2*h.len);g.plotX=g.clientX=(e+a)/2;g.plotY=(f+c)/2;g.shapeType="rect";g.shapeArgs={x:Math.min(e,a),y:Math.min(f,c),width:Math.abs(a-e),height:Math.abs(c-f)}});this.translateColors()},drawPoints:function(){l.column.prototype.drawPoints.call(this);
r(this.points,function(b){b.graphic.attr(this.colorAttribs(b))},this)},animate:n,getBox:n,drawLegendSymbol:b.LegendSymbolMixin.drawRectangle,alignDataLabel:l.column.prototype.alignDataLabel,getExtremes:function(){p.prototype.getExtremes.call(this,this.valueData);this.valueMin=this.dataMin;this.valueMax=this.dataMax;p.prototype.getExtremes.call(this)}}),h)})(p)});
