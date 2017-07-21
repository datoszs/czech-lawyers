import React from 'react';
import PropTypes from 'prop-types';
import {result as resultType} from '../../model';

import positive from './positive.svg';
import negative from './negative.svg';
import neutral from './neutral.svg';

const resultMap = {
    [resultType.POSITIVE]: positive,
    [resultType.NEGATIVE]: negative,
    [resultType.NEUTRAL]: neutral,
};

const Result = ({result}) => <img src={resultMap[result]} alt="result" />;

Result.propTypes = {
    result: PropTypes.oneOf(Object.values(resultType)).isRequired,
};

export default Result;

