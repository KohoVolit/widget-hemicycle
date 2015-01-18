# Widget: Hemicycle
Widget for creation of a hemicycle visualization for a single vote event.

The visualization may be created for any number of representatives in `[5, 250]` interval.

Technically, the visualization consists of 3 parts: *hemicycle* (upper part), *legend* (middle part) and *orloj* (lower part).

## Parameters
### resource (required)
Resource needs to be a `json` list, where the items have following *required* attributes: `name`, `party`, `option_meaning` (may be either `for`, `against` or `neutral`). Other attributes are optional.

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
Name of set of parties (file with parties' information). The party set file (e.g., `cz.json) needs to exist in `/papi` directory.

Default: `data`

Example of party_set: `cz`

### width (optional)
Width of the visualization in pixels.

Default: `400`

Note: The total **height** of the visualization is: `width * (1/2 + 1/8 + orloj_rows*1/16)`.

##Examples
###Example files
There are example `csv` files (in `/examples` directory) and their convertor `csv2json.php` into `json` objects).

###Example calling
`widget.php?
resource=http://localhost/michal/project/widget-hemicycle/examples/csv2json.php%3Furl%3Dhttp%3A%2F%2Flocalhost%2Fmichal%2Fdev%2Fhemicycle%2Foptims%2Fsingle_vote_event2.csv  
&lang=cs  
&width=600  
&orloj_rows=2  
&party_set=cz`


