### Full stops in keys
MongoDB doesn't allow full stops (.) in keys. Therefore, we replace all full stops with &46; when saving statements. This does mean, to display the full statement on the site and before sending via the API we need to replace &46; throughout with the full stop using a simple function:
```
//scan array and replace &46; with . (This is a result of . being reserved in Mongo)
//convert array to json as this is faster for multi-dimensional arrays (?) @todo check this out.
function replaceHtmlEntity( $array ){`
    return json_decode(str_replace('&46;','.', json_encode($array)));
}
```