import React, {PropTypes} from 'react';
import {getCurrentYear, sequence} from '../../util';
import Year from './Year';

const Timeline = ({
    startYear,
    positiveSelector,
    negativeSelector,
    neutralSelector,
}) => (
    <div className="timeline">
        {
            sequence((getCurrentYear() - startYear) + 1)
                .map((year) => year + startYear)
                .map((year) => <Year
                    key={year}
                    year={year}
                    positiveSelector={positiveSelector}
                    negativeSelector={negativeSelector}
                    neutralSelector={neutralSelector}
                />)
        }
    </div>
);

Timeline.propTypes = {
    startYear: PropTypes.number.isRequired,
    positiveSelector: PropTypes.func.isRequired,
    negativeSelector: PropTypes.func.isRequired,
    neutralSelector: PropTypes.func.isRequired,
};

export default Timeline;
