import React from 'react';
import PropTypes from 'prop-types';
import {Link} from 'react-router-dom';

const MarkdownLink = ({href, children}) => (
    href.match(/^https?:\/\//)
        ? <a href={href}>{children}</a>
        : <Link to={href} href={href}>{children}</Link>
);

MarkdownLink.propTypes = {
    href: PropTypes.string.isRequired,
    children: PropTypes.node.isRequired,
};

export default MarkdownLink;
