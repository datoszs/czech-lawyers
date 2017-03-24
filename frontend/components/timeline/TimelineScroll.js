import React, {PropTypes} from 'react';

const TimelineScroll = ({children}) => (
    <div className="timeline-scroll hidden-xs">
        {children}
    </div>
);

TimelineScroll.propTypes = {
    children: PropTypes.node.isRequired,
};

export default TimelineScroll;
