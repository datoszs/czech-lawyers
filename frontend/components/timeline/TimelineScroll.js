import React from 'react';
import PropTypes from 'prop-types';

const TimelineScroll = ({children}) => (
    <div className="timeline-scroll hidden-xs">
        {children}
    </div>
);

TimelineScroll.propTypes = {
    children: PropTypes.node.isRequired,
};

export default TimelineScroll;
