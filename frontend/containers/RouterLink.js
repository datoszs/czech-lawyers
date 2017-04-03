import React, {PropTypes} from 'react';
import {Link} from 'react-router';

const RouterLink = ({href, children}) => (
    href.match(/^https?:\/\//)
        ? <a href={href}>{children}</a>
        : <Link to={href}>{children}</Link>
);

RouterLink.propTypes = {
    href: PropTypes.string.isRequired,
    children: PropTypes.node.isRequired,
};

export default RouterLink;
