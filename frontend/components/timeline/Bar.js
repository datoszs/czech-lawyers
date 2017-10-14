import React from 'react';
import PropTypes from 'prop-types';
import classnames from 'classnames';
import {wrapEventStop} from '../../util';
import styles from './Bar.css';

const styleMap = {
    positive: styles.positive,
    negative: styles.negative,
    neutral: styles.neutral,
};

const Bar = ({size, className, selected, onClick}) => (
    <div
        className={classnames({
            [styleMap[className]]: true,
            [styles.selected]: selected,
        })}
        style={{height: `${size * 100}%`}}
        onClick={wrapEventStop(onClick)}
    />
);

Bar.defaultProps = {
    selected: false,
};

Bar.propTypes = {
    size: PropTypes.number.isRequired,
    className: PropTypes.string.isRequired,
    selected: PropTypes.bool,
    onClick: PropTypes.func.isRequired,
};

export default Bar;
