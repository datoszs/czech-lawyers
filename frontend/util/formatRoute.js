import {compile} from 'path-to-regexp';
import querystring from 'query-string';

export default (route, params, query, anchor) => {
    let result = compile(route)(params);
    if (query) {
        result += `?${querystring.stringify(query)}`;
    }
    if (anchor) {
        result += `#${anchor}`;
    }
    return result;
};
