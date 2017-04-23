import React, {PropTypes, createElement} from 'react';
import PositiveText from './PositiveText';
import NegativeText from './NegativeText';

const plusRegex = /\+(\\\+|[^+])*\+/;
const minusRegex = /-(\\-|[^-])*-/;

const concat = (result, literal) => (literal.length ? result.concat(literal) : result);

const first = ({plusMatch, minusMatch}) => {
    const plus = {match: plusMatch, Component: PositiveText};
    const minus = {match: minusMatch, Component: NegativeText};
    if (!plusMatch) {
        return minus;
    } else if (!minusMatch) {
        return plus;
    } else if (plusMatch.index < minusMatch.index) {
        return plus;
    } else {
        return minus;
    }
};

const transform = (result, literal) => {
    const plusMatch = literal.match(plusRegex);
    const minusMatch = literal.match(minusRegex);
    if (!plusMatch && !minusMatch) {
        return concat(result, literal);
    } else {
        const {match, Component} = first({plusMatch, minusMatch});
        const component = <Component text={match[0].substring(1, match[0].length - 1)} />;
        const newResult = concat(result, literal.substring(0, match.index)).concat(component);
        return transform(newResult, literal.substring(match.index + match[0].length));
    }
};

const TextNode = ({literal}) => createElement('span', null, transform([], literal));

TextNode.propTypes = {
    literal: PropTypes.string.isRequired,
};

export default TextNode;
