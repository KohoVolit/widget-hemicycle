<!DOCTYPE html>
<html lang="en">
  <head>
    <meta charset="utf-8">
    <title>Generate Hemicycle Chart for a single vote event</title>
	<meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="Generator of Hemicycle Charts">
    <meta name="author" content="Michal Škop">
    
    <link rel="stylesheet" href="//maxcdn.bootstrapcdn.com/bootswatch/3.2.0/simplex/bootstrap.min.css" />
  </head>
  <body>
     <form action="../../widget.php">
        
        <!-- lang -->
        <h3>Language <small>lang</small></h3>
        <p>Language <a href="http://en.wikipedia.org/wiki/List_of_ISO_639-1_codes">ISO 639-1 code</a>
        <div class="radio">
          <label>
            <input type="radio" name="lang" id="lang1" value="en" checked>
            English <code>en</en>
          </label>
        </div>
        <div class="radio">
          <label>
            <input type="radio" name="lang" id="lang1" value="cs" checked>
            Czech <code>cs</code>
          </label>
        </div>
     </form>
  </body>
</head>

widget.php?  
resource=http://localhost/michal/project/widget-hemicycle/examples/csv2json.php%3Furl%3Dhttp%3A%2F%2Flocalhost%2Fmichal%2Fdev%2Fhemicycle%2Foptims%2Fsingle_vote_event2.csv  
&lang=cs  
&width=600  
&orloj_rows=2  
&party_set=cz
