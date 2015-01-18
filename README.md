# Widget: Hemicycle
Widget for creation of a hemicycle for a single vote event

## Parameters
### resource
Resource needs to be a `json` list, where the items have following *required* attributes: `name`, `party`, `option_meaning` (may be either `for`, `against` or `neutral`). Other attributes are optional.

Example of resource:
```json
[{"name":"Michelle","party":"Fairies","option_meaning":"for"}, {"name":"Nico","party":"Pirates","option_meaning":"against"}]
```




