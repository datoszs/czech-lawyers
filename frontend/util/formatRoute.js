import {formatPattern} from 'react-router';
import querystring from 'query-string';

export default (route, params, query, anchor) => {
    let result = formatPattern(route, params);
    if (query) {
        result += `?${querystring.stringify(query)}`;
    }
    if (anchor) {
        result += `#${anchor}`;
    }
    return result;
};
