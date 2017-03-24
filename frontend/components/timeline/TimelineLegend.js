import React, {PropTypes} from 'react';
import {Popover} from 'react-bootstrap';

const TimelineLegend = ({year, positive, negative, neutral, msgPositive, msgNegative, msgNeutral}) => (
    <Popover id="timeline-legend" title={<strong>{year}</strong>}>
        <dl>
            <div>
                {positive && <dt className="positive">{msgPositive}</dt>}
                {positive && <dd> {positive}</dd>}
            </div>
            <div>
                {negative && <dt className="negative">{msgNegative}</dt>}
                {negative && <dd>{negative}</dd>}
            </div>
            <div>
                {neutral && <dt className="neutral">{msgNeutral}</dt>}
                {neutral && <dd>{neutral}</dd>}
            </div>
        </dl>
    </Popover>
);

TimelineLegend.propTypes = {
    year: PropTypes.number.isRequired,
    positive: PropTypes.number,
    negative: PropTypes.number,
    neutral: PropTypes.number,
    msgPositive: PropTypes.string.isRequired,
    msgNegative: PropTypes.string.isRequired,
    msgNeutral: PropTypes.string.isRequired,
};

TimelineLegend.defaultProps = {
    positive: null,
    negative: null,
    neutral: null,
};

export default TimelineLegend;
