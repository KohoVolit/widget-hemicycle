# Widget: Hemicycle
Widget for creation of a hemicycle visualization for a single vote event.

![picture](https://raw.githubusercontent.com/KohoVolit/widget-hemicycle/master/examples/example.png "Example")

The visualization may be created for any number of representatives in `[5, 250]` interval or if the number of representatives equals one of `{257, 266, 270, 275, 290, 292, 300, 301, 308, 315, 325, 326, 336, 348, 349, 350, 357, 360, 375, 386, 400, 435, 440, 450, 460, 462, 480, 498, 500, 508, 513, 547, 550, 552, 560, 577, 601, 614, 620, 630, 650, 687, 754, 765}` ([World parliaments](http://en.wikipedia.org/wiki/List_of_legislatures_by_country))

Technically, the visualization consists of 3 parts ([D3 reusable charts](http://bost.ocks.org/mike/chart/)): *hemicycle* (upper part), *legend* (middle part) and *orloj* (lower part). These parts may be used separately within another visualization.

Export to `png`(`svg`) requires Inkscape to be installed.

## Parameters
### resource (required)
Resource needs to be a `json` list, where the items have following *required* attributes: `name`, `party`, `option_meaning` (may be either `for`, `against` or `neutral`).  
Some other attributes are *semi-optional* (they are required unless `party_set` is set): `color`, `position`.  
Other attributes are optional, namely `description` (used in tooltips).

Example of resource:
```json
[{"name":"Michelle","party":"Fairies","option_meaning":"for","id":"1"},
 {"name":"Nico","party":"Pirates","option_meaning":"against","id":"2"}]
```

### lang (optional)
Language [ISO 639-1 code](http://en.wikipedia.org/wiki/List_of_ISO_639-1_codes). The language file (e.g.,`cs.json`) needs to exist in `lapi/` directory.

Default: `en`

Example of lang: `cs`

### orloj_rows (optional)
Number of rows in `Orloj`

Default: `1`

Example of orloj_rows: `2`

### party_set (optional)
Name of set of parties (file with parties' information). The party set file (e.g., `cz.json`) needs to exist in `/papi` directory.

Default: `data`

Example of party_set: `cz`

Note: either `party_set` is required, or the resource must contain `color` and `position` for each person.

### width (optional)
Width of the visualization in pixels.

Default: `400`

Note: The total **height** of the visualization is: `width * (1/2 + 1/8 + orloj_rows*1/16)`.

### format (optional)
Format of the visualization. If set to `png` or `svg` that it creates the files and redirects to them.

Default: html

Example of format: `png`

Note: Inscape MUST be installed for this export to `png`(`svg`).

## Examples
### Example files
There are example `csv` files (in `/examples` directory) and their convertor `csv2json.php` into `json` objects.

### Example calling
```url
widget.php?  
resource=http://localhost/michal/project/widget-hemicycle/examples/csv2json.php%3Furl%3Dhttp%3A%2F%2Flocalhost%2Fmichal%2Fdev%2Fhemicycle%2Foptims%2Fsingle_vote_event2.csv  
&lang=cs  
&width=600  
&orloj_rows=2  
&party_set=cz
&format=png
```


