<!DOCTYPE html>
<html lang="{{_LANG}}">
  <head>
  <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
  
    <meta property="og:image" content="{{_OG_IMAGE}}"/>
	<meta property="og:title" content="Hemicycle chart"/>
	<meta property="og:url" content="{{_OG_URL}}"/>
	<meta property="og:site_name" content="Hemicycle chart"/>
	<meta property="og:type" content="website"/>
	
  <script src="http://d3js.org/d3.v3.min.js"></script>

    <script src="d3.hemicycle.js"></script>
    <script src="d3.tip.js"></script>
    <script src="d3.legend.js"></script>
    <script src="d3.orloj.js"></script>

    <style>
    text.shadow {
        stroke: gray;
        stroke-width: 1px;
        opacity: 0.9;
    }
    /* D3 tips */  
    .d3-tip {
      line-height: 1;
      font-weight: bold;
      padding: 12px;
      background: rgba(0, 0, 0, 0.8);
      color: #fff;
      border-radius: 2px;
    }
    /*.d3-tip.n:after {
      margin: -1px 0 0 0;
      top: 100%;
      left: 0;
    }*/
    /* Southward tooltips */
    .d3-tip.s:after {
        margin: 0 0 1px 0;
        top: -8px;
        left: 0;
    }
    /* Eastward tooltips */
    .d3-tip.e:after {
        margin: -4px 0 0 0;
        top: 50%;
        left: -8px;
    }
    .stronger {
      color: yellow;
    }
    </style>
  </head>
  <body>   
    <div id="chart"></div>

<script type="text/javascript">
  data = {{_DATA}};
  dat = {{_DAT}};
        
        //width and height
        var w = {{_WIDTH}},
            rowsOrloj = {{_ROWS_ORLOJ}},
            iconHeightOrloj = w/22, // w/22 for 2 rowsOrloj
            h = w*(1/2+1/8+rowsOrloj*1/16);

        //append svg
        var svg=d3.select("#chart")
            .append("svg")
            .attr("width",w)
            .attr("height",h)
            .attr("id","chart-svg");
        
        var hemicycleData = [{'widthIcon': dat.w, 'gap': dat.g, 'n': dat.n}];
        hemicycleData[0]['width'] = w;
        hemicycleData[0]['people'] = data;
        

       // Initialize tooltip
        tip = d3.tip().direction('e').attr("class", "d3-tip").html(function(d) {
            if (typeof(d["description"]) === 'undefined')
                chunk = '';
            else
                chunk = "<br>" + d["description"];
            return "<span class=\'stronger\'>" + d["name"] + "</span><br>" + d["party"] + chunk;
        });
        
        // function for chart
        var myChart = d3.hemicycle()
                .n(function(d) {return d.n;})
                .gap(function(d) {return d.gap;})
                .widthIcon(function(d) {return d.widthIcon;})
                .width(function(d) {return d.width;})
                .people(function(d) {return d.people;})
                .arcs({{_ARCS}})
                ;
       
       //create hemicycle         
       var hc = svg.selectAll(".hc")
            .data(hemicycleData)
           .enter()
            .append("svg:g")
            .attr("width",w)
            .attr("height",w/2)
              .call(myChart);

	    // Invoke the tip in the context of your visualization
        svg.call(tip);
        
	    // Add tooltip div
        var div = d3.select("body").append("div")
        .attr("class", "tooltip")
        .style("opacity", 1e-6);   
        
        //legend data
        var legendData = [{{_LEGEND}}];   
        
        var myLegend = d3.legend()
                .label(function(d) {return d.label;})
                .width(w)
                .icons(function(d) {return d.icons;});
       
        svg.selectAll(".legend")
              .data(legendData)
           .enter()
            .append("svg:g")
            .attr("width",w)
            .attr("height",w/8)
            .attr("transform", "translate(0," + w/2 + ")")
            .call(myLegend);
            
         var orlojData = [{
                //'label' : ['Legenda:'],
                'rows': rowsOrloj,
                'iconHeight': iconHeightOrloj,
                'icons' : {{_ORLOJ_PARTIES}}
            }];
        
        var myOrloj = d3.orloj()
                //.label(function(d) {return d.label;})
                .rows(function(d) {return d.rows;})
                .iconHeight(function(d) {return d.iconHeight;})
                .icons(function(d) {return d.icons;})
                .width(w);  
        
        movey = w/2 + w/8;        
        svg.selectAll(".orloj")
              .data(orlojData)
           .enter()
            .append("svg:g")
            .attr("width",w)
            .attr("height",w/16*orlojData[0].rows)
            .attr("transform", "translate(0," + movey + ")")
            .call(myOrloj);     


</script>
<!-- creates svg and png pictures (using create_png.php) and redirects to it when it is ready -->
<script src="https://code.jquery.com/jquery-1.11.1.js"></script>
<script src="http://crypto-js.googlecode.com/svn/tags/3.0.2/build/rollups/md5.js"></script>
<script>
    $(document).ready(function () {
        postdata = {'url':CryptoJS.MD5(window.location.href).toString(), 'svg':$('#chart').html().replace(/<strong>/g,'').replace(/<\/strong>/g,'').replace(/<br>/g,''), 'nocache': getParameterByName('nocache')};
        $.post('create_png.php',postdata);
        nothing = 0;
        //redirects to it when svg and png are ready
        if (($.inArray(getParameterByName('format'),['png','svg'])) > -1)
            get_picture();
    });
    var i = 0;
    function get_picture() {
        $.ajax('cache/{{_FORMAT}}/' + CryptoJS.MD5(window.location.href).toString() + '.{_FORMAT}', {
            statusCode: {
              200: function (response) {
                 location.href = 'cache/{{_FORMAT}}/' + CryptoJS.MD5(window.location.href).toString() + '.{{_FORMAT}}';
              },
              404: function(response) {
                i++;
                if (i < 60) {
                  setTimeout(get_picture, 1000)
                } else {
                    alert('Something wrong, giving up...');
                }
              }
            }
        });
    }
    function getParameterByName(name) {
        name = name.replace(/[\[]/, "\\[").replace(/[\]]/, "\\]");
        var regex = new RegExp("[\\?&]" + name + "=([^&#]*)"),
            results = regex.exec(location.search);
        return results === null ? "" : decodeURIComponent(results[1].replace(/\+/g, " "));
    }
</script>
  </body>
</html>
