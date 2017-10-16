import React from 'react';
import PropTypes from 'prop-types';
import {wrapLinkMouseEvent} from '../../util';

const Statement = ({children, onClick, href}) => (
    <a
        href={href}
        onClick={wrapLinkMouseEvent(onClick)}
        className="problem-statement well"
    >
        {children}
    </a>
);

Statement.propTypes = {
    children: PropTypes.node.isRequired,
    onClick: PropTypes.func.isRequired,
    href: PropTypes.string.isRequired,
};

export default Statement;
