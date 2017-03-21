import React, {PropTypes} from 'react';
import classnames from 'classnames';

const Bar = ({size, className, selected, onClick}) => (
    <div
        className={classnames({
            [className]: true,
            'timeline-bar': true,
            selected,
        })}
        style={{height: `${size}ex`}}
        onClick={onClick}
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
