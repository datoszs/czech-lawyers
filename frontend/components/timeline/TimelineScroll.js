import React from 'react';
import PropTypes from 'prop-types';
import classNames from 'classnames';
import styles from './TimelineScroll.css';

const TimelineScroll = ({children}) => (
    <div className={classNames(styles.main, 'hidden-xs')}>
        {children}
    </div>
);

TimelineScroll.propTypes = {
    children: PropTypes.node.isRequired,
};

export default TimelineScroll;
