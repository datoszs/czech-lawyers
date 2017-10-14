import React from 'react';
import PropTypes from 'prop-types';
import styles from './Result.less';

import positive from './positive.svg';
import negative from './negative.svg';
import neutral from './neutral.svg';

const resultMap = {
    positive,
    negative,
    neutral,
};

const Result = ({result}) => <img className={styles.image} src={resultMap[result]} alt="result" />;

Result.propTypes = {
    result: PropTypes.oneOf(Object.keys(resultMap)).isRequired,
};

export default Result;

